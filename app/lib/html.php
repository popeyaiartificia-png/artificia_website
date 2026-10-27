<?php
declare(strict_types=1);

const HTML_ALLOWED = ['p', 'h2', 'h3', 'ul', 'ol', 'li', 'strong', 'em', 'a', 'br'];
const HTML_DROPPED = ['script', 'style', 'iframe']; // removed together with their content

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
    return $body ? html_render_children($body) : '';
}

function html_render_children(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) {
        $out .= html_render_node($child);
    }
    return $out;
}

function html_render_node(DOMNode $node): string
{
    if ($node instanceof DOMText) { // DOMCdataSection extends DOMText
        return e($node->data);
    }
    if (!$node instanceof DOMElement) { // comments, processing instructions
        return '';
    }
    $tag = strtolower($node->nodeName);
    if (in_array($tag, HTML_DROPPED, true)) {
        return '';
    }
    $inner = html_render_children($node);
    if (!in_array($tag, HTML_ALLOWED, true)) {
        return $inner;
    }
    if ($tag === 'br') {
        return '<br>';
    }
    $attrs = $tag === 'a' ? html_link_attrs($node->getAttribute('href')) : '';
    return "<$tag$attrs>$inner</$tag>";
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
    return ' href="' . e($href) . '"' . ($absolute ? ' rel="noopener"' : '');
}
