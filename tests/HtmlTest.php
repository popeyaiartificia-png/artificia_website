<?php
declare(strict_types=1);

function test_sanitize_strips_dangerous(): void
{
    eq(sanitize_html('<p onclick="x">a<script>b</script></p>'), '<p>a</p>');
    eq(sanitize_html('<a href="javascript:alert(1)">x</a>'), '<a>x</a>');
    eq(sanitize_html('<p>a</p><style>p{}</style><iframe src="x">inner</iframe>'), '<p>a</p>');
}

function test_sanitize_keeps_allowed(): void
{
    $in = '<h2>T</h2><h3>S</h3><p><strong>b</strong> <em>i</em><br>x</p><ul><li>a</li></ul><ol><li>b</li></ol>';
    eq(sanitize_html($in), $in);
    eq(sanitize_html('<a href="https://x.com/a?b=1&amp;c=2">x</a>'), '<a href="https://x.com/a?b=1&amp;c=2" rel="noopener">x</a>');
    eq(sanitize_html('<a href="http://x.com">x</a>'), '<a href="http://x.com" rel="noopener">x</a>');
    eq(sanitize_html('<a href="mailto:a@b.co">x</a>'), '<a href="mailto:a@b.co" target="_blank" rel="noopener noreferrer">x</a>');
    eq(sanitize_html('<a href="tel:+911234">x</a>'), '<a href="tel:+911234" target="_blank" rel="noopener noreferrer">x</a>');
    eq(sanitize_html('<a href="/contact">x</a>'), '<a href="/contact" target="_blank" rel="noopener noreferrer">x</a>');
}

function test_sanitize_word_paste(): void
{
    $in = '<p class="MsoNormal" style="margin:0"><span style="color:red">Hello</span><o:p></o:p> <font face="Arial">world</font></p>'
        . '<!--[if gte mso 9]><xml>junk</xml><![endif]-->';
    eq(sanitize_html($in), '<p>Hello world</p>');
}

function test_sanitize_unwraps_nested_disallowed(): void
{
    eq(sanitize_html('<div><span><section><p>a</p></section></span></div>'), '<p>a</p>');
    eq(sanitize_html('<div><script>x</script><p>a</p></div>'), '<p>a</p>');
    eq(sanitize_html('<strong onmouseover="x"><b>a</b></strong>'), '<strong>a</strong>');
}

function test_sanitize_obfuscated_schemes(): void
{
    foreach ([
        'JaVaScRiPt:alert(1)',
        ' javascript:alert(1)',
        "java\tscript:alert(1)",
        "java\nscript:alert(1)",
        '&#106;avascript:alert(1)',
        '&#x6A;&#x61;vascript&colon;alert(1)',
        'jav&#x09;ascript:alert(1)',
        'data:text/html;base64,PHNjcmlwdD4=',
        'DATA:text/html,x',
        'vbscript:x',
        '//evil.com/x',
        '/\\evil.com/x',
        'relative/path',
        '',
    ] as $href) {
        eq(sanitize_html('<a href="' . $href . '">x</a>'), '<a>x</a>', $href);
    }
    eq(sanitize_html('<a HREF="HTTPS://X.com" onClick="x">x</a>'), '<a href="HTTPS://X.com" rel="noopener">x</a>', 'mixed-case attrs');
    eq(sanitize_html('<a href="https://x.com" target="_blank" style="x">x</a>'), '<a href="https://x.com" rel="noopener">x</a>');
}

function test_sanitize_text_and_edges(): void
{
    eq(sanitize_html(''), '');
    eq(sanitize_html("  \n "), '');
    eq(sanitize_html('plain text'), 'plain text');
    eq(sanitize_html('hello <strong>x</strong> there'), 'hello <strong>x</strong> there');
    eq(sanitize_html('a < b & c'), 'a &lt; b &amp; c');
    eq(sanitize_html('<p>&lt;script&gt;x&lt;/script&gt;</p>'), '<p>&lt;script&gt;x&lt;/script&gt;</p>', 'escaped markup stays text');
    eq(sanitize_html('<p>Café – नमस्ते</p>'), '<p>Café – नमस्ते</p>', 'utf-8');
    eq(sanitize_html('</div><script>x</script><p>a</p>'), '<p>a</p>', 'breakout attempt');
}

function test_sanitize_invalid_utf8(): void
{
    $out = sanitize_html("<p>caf\xE9 ok</p>");
    ok(mb_check_encoding($out, 'UTF-8'), 'output is valid UTF-8');
    ok(str_starts_with($out, '<p>caf') && str_ends_with($out, ' ok</p>'), $out);
}

function test_sanitize_maps_bold_italic(): void
{
    eq(sanitize_html('<p><b>a</b> <i>b</i></p>'), '<p><strong>a</strong> <em>b</em></p>');
    eq(sanitize_html('<p><i><b>a</b></i></p>'), '<p><em><strong>a</strong></em></p>');
    eq(sanitize_html('<p><em><i>a</i></em></p>'), '<p><em>a</em></p>');
}

function test_sanitize_google_docs_wrapper(): void
{
    $in = '<b style="font-weight:normal;" id="docs-internal-guid-1"><p dir="ltr"><span style="font-weight:700">Hi</span> there</p><p><b>bold</b></p></b>';
    eq(sanitize_html($in), '<p>Hi there</p><p><strong>bold</strong></p>');
    eq(sanitize_html('<b style="font-weight: 400">x</b>'), 'x');
    eq(sanitize_html('<b style="font-weight:bold">x</b>'), '<strong>x</strong>');
}

function test_sanitize_blocks_become_paragraphs(): void
{
    eq(sanitize_html('<div>one</div><div>two <b>x</b></div>'), '<p>one</p><p>two <strong>x</strong></p>');
    eq(sanitize_html('<div>intro<p>para</p>outro</div>'), '<p>intro</p><p>para</p><p>outro</p>');
    eq(sanitize_html('<div><div>deep</div></div>'), '<p>deep</p>');
    eq(sanitize_html('<div> <br> </div><div>  </div><p>a</p>'), '<p>a</p>');
    eq(sanitize_html('<h1>T</h1><h4>S</h4><blockquote>q</blockquote><pre>c</pre>'), '<h2>T</h2><p>S</p><p>q</p><p>c</p>');
    eq(sanitize_html('<table><tr><td>a</td><td>b</td></tr></table>'), '<p>a</p><p>b</p>');
    eq(sanitize_html('<span><div>x</div></span>'), '<p>x</p>');
    eq(sanitize_html('<ul><li><div>a</div></li></ul>'), '<ul><li><p>a</p></li></ul>');
    eq(sanitize_html('top <div>x</div> tail'), 'top <p>x</p> tail');
}
