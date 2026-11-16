<?php
declare(strict_types=1);

const HTML_ALLOWED = ['p', 'h2', 'h3', 'ul', 'ol', 'li', 'strong', 'em', 'a', 'br'];
const HTML_DROPPED = ['script', 'style', 'iframe']; // removed together with their content
const HTML_TAG_MAP = ['b' => 'strong', 'i' => 'em', 'h1' => 'h2'];
const HTML_BLOCKS = ['p', 'h2', 'h3', 'ul', 'ol'];
// Unwrapped, these would run their text into the neighbours, so their inline content becomes its own <p>.
const HTML_BLOCKS_TO_P = ['div', 'section', 'article', 'header', 'footer', 'blockquote', 'td', 'th', 'tr', 'table', 'h4', 'h5', 'h6', 'pre'];

/** Reduce editor/Word-pasted HTML to a small safe subset. Output is rebuilt from the parsed tree, never echoed from input. */
function sanitize_html(string $html): string
{
    $html = mb_scrub($html, 'UTF-8');
    if (trim($html) === '') {
        return '';
    }
    // libxml quirks: it turns Word's <o:p> into a real <p>, and swallows a "<" that doesn't start a tag.
    $html = preg_replace('#</?[a-z][\w.-]*:[\w.-]+[^>]*>#i', '', $html) ?? '';
    $html = preg_replace('#<(?![a-z/!?])#i', '&lt;', $html) ?? '';
    $prev = libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    // The wrapper div keeps libxml from wrapping top-level text in <p>; it is just another unwrapped element.
    $doc->loadHTML('<meta charset="utf-8"><div>' . $html . '</div>', LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $body = $doc->getElementsByTagName('body')->item(0);
    return $body ? html_render_blocks($body, false) : '';
}

function html_render_children(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) {
        $out .= html_render_node($child);
    }
    return $out;
}

/** The tag an element is kept as: b/i become strong/em, h1 becomes h2; Google Docs' font-weight:normal <b> wrapper is '' (unwrapped). */
function html_tag(DOMElement $el): string
{
    $tag = strtolower($el->nodeName);
    if ($tag === 'b' && preg_match('/font-weight:\s*(normal|400)\b/i', $el->getAttribute('style'))) {
        return '';
    }
    return HTML_TAG_MAP[$tag] ?? $tag;
}

function html_render_node(DOMNode $node): string
{
    if ($node instanceof DOMText) { // DOMCdataSection extends DOMText
        return e($node->data);
    }
    if (!$node instanceof DOMElement) { // comments, processing instructions
        return '';
    }
    $tag = html_tag($node);
    if (in_array($tag, HTML_DROPPED, true)) {
        return '';
    }
    if (in_array($tag, HTML_BLOCKS_TO_P, true)) {
        return html_render_blocks($node);
    }
    $inner = html_render_children($node);
    if (!in_array($tag, HTML_ALLOWED, true)) {
        return $inner;
    }
    if ($tag === 'br') {
        return '<br>';
    }
    // <strong><b>x</b></strong> (Word, Docs) would otherwise nest twice.
    if (in_array($tag, ['strong', 'em'], true)) {
        for ($p = $node->parentNode; $p instanceof DOMElement; $p = $p->parentNode) {
            if (html_tag($p) === $tag) {
                return $inner;
            }
        }
    }
    $attrs = $tag === 'a' ? html_link_attrs($node->getAttribute('href')) : '';
    return "<$tag$attrs>$inner</$tag>";
}

/** Children as blocks: each run of inline content between block elements becomes a <p> ($wrap false leaves the run bare, for the top level). */
function html_render_blocks(DOMNode $node, bool $wrap = true): string
{
    $out = $run = '';
    html_collect($node, $wrap, $out, $run);
    return $out . html_flush_run($run, $wrap);
}

function html_collect(DOMNode $node, bool $wrap, string &$out, string &$run): void
{
    foreach ($node->childNodes as $child) {
        if (!$child instanceof DOMElement) {
            $run .= html_render_node($child);
            continue;
        }
        $tag = html_tag($child);
        if (in_array($tag, HTML_DROPPED, true)) {
            continue;
        }
        if (in_array($tag, HTML_BLOCKS_TO_P, true)) {
            $out .= html_flush_run($run, $wrap);
            // The first child of <body> is our own wrapper div: its content stays bare at the top level.
            $inner = !($node->nodeName === 'body' && $child->previousSibling === null);
            html_collect($child, $inner, $out, $run);
            $out .= html_flush_run($run, $inner);
        } elseif (in_array($tag, HTML_BLOCKS, true)) {
            $out .= html_flush_run($run, $wrap) . html_render_node($child);
        } elseif (in_array($tag, HTML_ALLOWED, true)) {
            $run .= html_render_node($child);
        } else { // span, font, Docs' wrapper <b>: transparent, so a block inside still ends the run
            html_collect($child, $wrap, $out, $run);
        }
    }
}

function html_flush_run(string &$run, bool $wrap): string
{
    $html = $run;
    $run = '';
    if (!$wrap) {
        return $html;
    }
    return preg_match('/\S/u', str_replace('<br>', '', $html)) === 1 ? '<p>' . trim($html) . '</p>' : '';
}

/** Contact routes and communication links keep the website open in its own tab. */
function contact_link_target(string $href): string
{
    $parts = parse_url($href);
    $host = strtolower($parts['host'] ?? '');
    $path = rtrim($parts['path'] ?? '', '/');
    $communication = preg_match('#^(?:mailto:|tel:)#i', $href) === 1
        || in_array($host, ['wa.me', 'whatsapp.com', 'www.whatsapp.com', 'api.whatsapp.com', 'web.whatsapp.com',
            'm.me', 'messenger.com', 'www.messenger.com'], true)
        || (in_array($host, ['facebook.com', 'www.facebook.com', 'm.facebook.com'], true)
            && preg_match('#^/messages(?:/|$)#i', $parts['path'] ?? '') === 1);
    return $communication || $path === '/contact' ? ' target="_blank" rel="noopener noreferrer"' : '';
}

function html_link_attrs(string $href): string
{
    // Browsers ignore tabs/newlines anywhere in a URL and trim control chars and spaces at the ends, so do the same before judging it.
    $href = trim(preg_replace('/[\t\r\n\x00]/', '', $href) ?? '', " \x00..\x20");
    $absolute = preg_match('#^https?://#i', $href) === 1;
    // Allowlist; a leading // or /\ is a protocol-relative external link, not a site path.
    if (!$absolute && preg_match('#^(mailto:|tel:)#i', $href) !== 1 && preg_match('#^/(?![/\\\\])#', $href) !== 1) {
        return '';
    }
    $target = contact_link_target($href);
    return ' href="' . e($href) . '"' . ($target !== '' ? $target : ($absolute ? ' rel="noopener"' : ''));
}
