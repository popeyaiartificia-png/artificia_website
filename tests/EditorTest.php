<?php
declare(strict_types=1);

const EDITOR_TEST_PW = 'correct horse 1';

/** Logs in a new user with $role (and a known CSRF token); returns the user id. */
function editor_login(PDO $db, string $role = 'admin'): int
{
    $uid = user_create($db, "$role-" . bin2hex(random_bytes(3)) . '@example.com', EDITOR_TEST_PW, $role);
    $_SESSION = ['uid' => $uid, 'csrf' => 'tok'];
    return $uid;
}

function editor_post(PDO $db, string $path, array $post): HttpResponse
{
    $_POST = $post + ['_csrf' => 'tok'];
    try {
        return admin_handle($db, 'POST', $path);
    } finally {
        $_POST = [];
    }
}

function editor_get(PDO $db, string $path, array $get = []): HttpResponse
{
    $_GET = $get;
    try {
        return admin_handle($db, 'GET', $path);
    } finally {
        $_GET = [];
    }
}

/** A complete page (passes the publish rules) saved straight to the DB. */
function editor_page(PDO $db, array $over = []): int
{
    return page_save($db, $over + [
        'slug' => 'demo', 'title' => 'Demo page', 'h1' => 'Demo heading', 'meta_title' => 'Demo | Artificia',
        'meta_description' => 'A demo page.', 'status' => 'draft',
        'sections' => [['type' => 'rich_text', 'fields' => ['heading' => 'Hi', 'body' => '<p>Body words</p>']]],
    ], 1);
}

/** The editor form's fields for a saved page, as the browser posts them (sections left to the caller). */
function editor_form(PDO $db, int $id, array $over = []): array
{
    $p = page_find($db, $id);
    return $over + [
        'title' => $p['title'], 'slug' => $p['slug'], 'h1' => $p['h1'], 'meta_title' => $p['meta_title'],
        'meta_description' => $p['meta_description'], 'og_image_id' => (string) ($p['og_image_id'] ?? ''),
        'template_id' => '', 'action' => 'draft',
        'sections' => [['type' => 'rich_text', 'heading' => 'Hi', 'body' => '<p>Body words</p>']],
    ];
}

function test_editor_post_roundtrip(): void
{
    $db = test_db();
    editor_login($db);
    $id = editor_page($db);
    // Sparse indexes as admin.js leaves them: the FAQ section was moved above the text block, and item 5 was removed.
    $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, [
        'title' => ' Renamed ', 'published_at' => '2001-01-01 00:00:00', 'action' => 'publish',
        'sections' => [
            7 => ['type' => 'faqs', 'heading' => 'Questions', 'items' => [
                3 => ['question' => 'Q1', 'answer' => 'A1'],
                9 => ['question' => 'Q3', 'answer' => 'A3'],
            ]],
            2 => ['type' => 'rich_text', 'heading' => 'Intro', 'body' => '<p onclick="x">Hello</p>', 'bogus' => 'dropped'],
        ],
    ]));
    eq([$r->status, $r->location], [302, "/admin/pages/$id"]);
    $p = page_find($db, $id);
    eq($p['sections'], [
        ['type' => 'faqs', 'fields' => ['heading' => 'Questions', 'items' => [['question' => 'Q1', 'answer' => 'A1'], ['question' => 'Q3', 'answer' => 'A3']]]],
        ['type' => 'rich_text', 'fields' => ['heading' => 'Intro', 'body' => '<p>Hello</p>']],
    ], 'submitted order, re-indexed, cleaned');
    eq([$p['title'], $p['status']], ['Renamed', 'published']);
    ok($p['published_at'] !== '2001-01-01 00:00:00', 'a posted published_at is ignored');
    eq(page_save($db, ['id' => $id, 'published_at' => '2001-01-01 00:00:00'], 1), $id);
    ok(page_find($db, $id)['published_at'] !== '2001-01-01 00:00:00', 'page_save ignores a caller-supplied published_at');

    // The editor page shows the saved sections in that order with re-indexed names.
    $html = editor_get($db, "/admin/pages/$id")->body;
    ok(strpos($html, 'name="sections[0][type]" value="faqs"') !== false, 'faqs is section 0');
    ok(str_contains($html, 'name="sections[0][items][1][question]" value="Q3"'), 'list items re-indexed');
    ok(str_contains($html, 'name="sections[1][type]" value="rich_text"'));

    // Non-scalar form values are refused with a message, not a crash.
    $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['slug' => ['x'], 'title' => ['y']]));
    eq($r->status, 422);
    ok(str_contains($r->body, 'slug'), 'the slug message is shown');
    eq(page_find($db, $id)['title'], 'Renamed', 'nothing saved');

    // Publishing an incomplete draft saves it as a draft and lists the reasons next to the fields.
    $draft = editor_page($db, ['slug' => 'thin']);
    $r = editor_post($db, "/admin/pages/$draft", editor_form($db, $draft, ['meta_description' => '', 'action' => 'publish',
        'sections' => [['type' => 'text_image', 'heading' => 'T', 'body' => '<p>x</p>']]]));
    eq($r->status, 422);
    ok(str_contains($r->body, 'The meta description is required.'), 'page-level reason');
    ok(str_contains($r->body, 'Image is required.'), 'section reason');
    eq(page_find($db, $draft)['status'], 'draft', 'stays a draft');
    eq(page_find($db, $draft)['meta_description'], '', 'but the edits are saved');

    // A live page whose update fails the rules is not changed (and not taken offline).
    $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['h1' => '', 'title' => 'Half done', 'action' => 'publish']));
    eq($r->status, 422);
    ok(str_contains($r->body, 'value="Half done"'), 'the typed values are kept in the form');
    eq([page_find($db, $id)['title'], page_find($db, $id)['status']], ['Renamed', 'published']);
    $_SESSION = [];
}

function test_preview_requires_login_and_renders_draft(): void
{
    $db = test_db();
    $id = editor_page($db, ['sections' => [['type' => 'rich_text', 'fields' => ['heading' => 'Hi', 'body' => '<p>Unpublished words</p>']]]]);
    $_SESSION = [];
    $r = editor_get($db, "/admin/preview/$id");
    eq([$r->status, $r->location], [302, '/admin/login']);

    editor_login($db, 'seo');
    $r = editor_get($db, "/admin/preview/$id");
    eq($r->status, 200);
    ok(str_contains($r->body, 'Unpublished words'), 'the draft renders');
    eq(substr_count($r->body, '<h1'), 1);
    eq(editor_get($db, '/admin/preview/99999')->status, 404);
    eq(page_find_published($db, 'demo'), null, 'still not public');
    $_SESSION = [];
}

function test_slug_change_on_published_page_creates_redirect(): void
{
    $db = test_db();
    editor_login($db);
    $id = editor_page($db, ['slug' => 'old-name', 'status' => 'published']);
    $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['slug' => 'new-name', 'action' => 'publish']));
    eq($r->status, 302);
    eq(page_find($db, $id)['slug'], 'new-name');
    eq(redirect_match($db, '/old-name'), ['to' => '/new-name', 'code' => 301]);
    ok(str_contains($_SESSION['flash']['msg'], '/old-name'), 'the editor is told about the redirect');

    // Renaming back: the stale /old-name -> /new-name row (hidden by the page now) goes, and /new-name -> /old-name takes over.
    $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['slug' => 'old-name', 'action' => 'publish']));
    eq($r->status, 302);
    eq($_SESSION['flash']['kind'], 'ok', $_SESSION['flash']['msg']);
    eq(page_find($db, $id)['slug'], 'old-name');
    eq(redirect_match($db, '/new-name'), ['to' => '/old-name', 'code' => 301]);
    eq(redirect_match($db, '/old-name'), null, 'the stale row is gone');

    // When redirect_save refuses (here: a row from the old address already exists), the page still saves and the editor sees why.
    $db->exec("INSERT INTO redirects (from_path, to_path) VALUES ('/old-name', '/elsewhere')");
    $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['slug' => 'third-name', 'action' => 'publish']));
    eq($r->status, 302);
    eq(page_find($db, $id)['slug'], 'third-name');
    eq($_SESSION['flash']['kind'], 'error');
    ok(str_contains($_SESSION['flash']['msg'], 'could not be redirected') && str_contains($_SESSION['flash']['msg'], 'already a redirect'), $_SESSION['flash']['msg']);

    // Drafts and posts: no redirect for a draft; a post's redirect uses /blog/.
    $draft = editor_page($db, ['slug' => 'draft-one']);
    editor_post($db, "/admin/pages/$draft", editor_form($db, $draft, ['slug' => 'draft-two']));
    eq(redirect_match($db, '/draft-one'), null);
    $post = editor_page($db, ['slug' => 'first-post', 'kind' => 'post', 'status' => 'published']);
    $r = editor_post($db, "/admin/posts/$post", editor_form($db, $post, ['slug' => 'better-post', 'action' => 'publish', 'excerpt' => 'Short']));
    eq([$r->status, $r->location], [302, "/admin/posts/$post"]);
    eq(redirect_match($db, '/blog/first-post'), ['to' => '/blog/better-post', 'code' => 301]);
    eq(page_find($db, $post)['excerpt'], 'Short');
    $html = editor_get($db, "/admin/posts/$post")->body;
    ok(str_contains($html, 'name="excerpt"') && str_contains($html, 'Cover image'), 'a post edits its excerpt and cover');
    eq(editor_get($db, "/admin/pages/$post")->location, "/admin/posts/$post", 'a post opened as a page goes to the post editor');
    $_SESSION = [];
}

function test_noindex_page_has_robots_meta_and_is_not_in_sitemap(): void
{
    $db = test_db();
    editor_login($db);
    $hidden = editor_page($db, ['slug' => 'hidden-page', 'status' => 'published']);
    $shown = editor_page($db, ['slug' => 'shown-page', 'status' => 'published']);
    editor_post($db, "/admin/pages/$hidden", editor_form($db, $hidden, ['noindex' => '1', 'action' => 'publish']));
    eq((int) page_find($db, $hidden)['noindex'], 1);

    ok(str_contains(render_page(page_find_published($db, 'hidden-page'), []), '<meta name="robots" content="noindex">'));
    ok(!str_contains(render_page(page_find_published($db, 'shown-page'), []), 'name="robots"'));
    $xml = sitemap_xml($db, 'https://example.test');
    ok(!str_contains($xml, 'hidden-page') && str_contains($xml, 'shown-page'), $xml);

    editor_post($db, "/admin/pages/$hidden", editor_form($db, $hidden, ['action' => 'publish'])); // box unticked
    eq((int) page_find($db, $hidden)['noindex'], 0);
    eq(json_decode((string) file_get_contents(__DIR__ . '/../content/seed/pages/blog.json'), true)['noindex'] ?? null, true, 'the empty blog index ships noindex');
    $_SESSION = [];
}

function test_seo_role_can_edit_pages(): void
{
    $db = test_db();
    editor_login($db, 'seo');
    $id = editor_page($db, ['title' => 'Listed page']);
    foreach (['/admin/pages', '/admin/posts', '/admin/templates', '/admin/pages/new', '/admin/posts/new', "/admin/pages/$id"] as $path) {
        eq(editor_get($db, $path)->status, 200, $path);
    }
    $list = editor_get($db, '/admin/pages')->body;
    ok(str_contains($list, 'Listed page') && str_contains($list, "/admin/pages/$id"), 'the page is listed with an edit link');
    ok(str_contains($list, 'href="/admin/pages"') && str_contains($list, 'href="/admin/pages/new"'), 'nav link and + Page shortcut');
    ok(!str_contains(editor_get($db, '/admin/pages', ['q' => 'nothing like it'])->body, 'Listed page'), 'search filters');
    ok(str_contains(editor_get($db, '/admin/pages', ['status' => 'draft'])->body, 'Listed page'), 'status filter');

    $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['title' => 'Edited by SEO']));
    eq($r->status, 302);
    eq(page_find($db, $id)['title'], 'Edited by SEO');

    $form = editor_get($db, '/admin/section-form', ['type' => 'card_grid', 'i' => '12']);
    eq($form->status, 200);
    ok(str_contains($form->body, 'name="sections[12][type]" value="card_grid"') && str_contains($form->body, '__j__'), 'blank section form with an item template');
    ok(!str_contains($form->body, '<html'), 'a fragment, not a whole page');
    eq(editor_get($db, '/admin/section-form', ['type' => 'nope'])->status, 404);
    $_SESSION = [];
}

function test_new_page_post_template_and_duplicate(): void
{
    $db = test_db();
    $uid = editor_login($db);
    $r = editor_post($db, '/admin/pages/new', ['title' => 'Fresh page', 'slug' => 'fresh-page', 'template_id' => '']);
    $id = (int) $db->query("SELECT id FROM pages WHERE slug = 'fresh-page'")->fetchColumn();
    eq([$r->status, $r->location], [302, "/admin/pages/$id"]);
    eq(page_find($db, $id)['status'], 'draft');
    eq(editor_post($db, '/admin/pages/new', ['title' => 'Again', 'slug' => 'fresh-page'])->status, 422, 'a taken slug is refused');
    eq(editor_post($db, '/admin/pages/new', ['title' => '', 'slug' => 'untitled'])->status, 422, 'a title is needed');

    $r = editor_post($db, '/admin/posts/new', ['title' => 'First post', 'slug' => 'first-post']);
    $post = page_find($db, (int) $db->query("SELECT id FROM pages WHERE slug = 'first-post'")->fetchColumn());
    eq([$post['kind'], $r->location], ['post', "/admin/posts/{$post['id']}"]);

    // Save as template (unique fields ticked), then start a page from it.
    $src = editor_page($db, ['slug' => 'service-a']);
    $r = editor_post($db, "/admin/pages/$src/template", ['name' => 'Service', 'unique' => ['0.heading', 'junk']]);
    eq($r->status, 302);
    $tpl = $db->query("SELECT id, unique_fields FROM templates WHERE name = 'Service'")->fetch();
    eq(json_decode($tpl['unique_fields'], true), ['0.heading'], 'only real field paths are kept');
    ok(str_contains(editor_get($db, '/admin/templates')->body, 'Service'), 'listed');
    editor_post($db, '/admin/pages/new', ['title' => 'Service B', 'slug' => 'service-b', 'template_id' => (string) $tpl['id']]);
    eq((int) page_find($db, (int) $db->query("SELECT id FROM pages WHERE slug = 'service-b'")->fetchColumn())['template_id'], (int) $tpl['id']);

    // Duplicate: a draft copy at a free address.
    $r = editor_post($db, "/admin/pages/$src/duplicate", []);
    $copy = (int) $db->query("SELECT id FROM pages WHERE slug = 'service-a-copy'")->fetchColumn();
    eq([$r->status, $r->location], [302, "/admin/pages/$copy"]);
    editor_post($db, "/admin/pages/$src/duplicate", []);
    ok((bool) $db->query("SELECT 1 FROM pages WHERE slug = 'service-a-copy-2'")->fetchColumn(), 'second copy gets -2');
    eq(page_find($db, $copy)['status'], 'draft');
    eq((int) page_find($db, $copy)['updated_by'], $uid);
    $_SESSION = [];
}

/** The fields a browser would submit for #page-form in $html (no <template> rows, no inputs owned by another form). */
function editor_submitted(string $html): array
{
    $doc = new DOMDocument();
    $prev = libxml_use_internal_errors(true); // HTML5 tags like <template> are unknown to libxml
    $doc->loadHTML($html, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $pairs = [];
    $xp = new DOMXPath($doc);
    foreach ($xp->query('//form[@id="page-form"]//*[(self::input or self::textarea or self::select) and @name and not(ancestor::template) and not(@form)]') as $el) {
        $name = $el->getAttribute('name');
        $type = $el->getAttribute('type');
        if ($el->nodeName === 'textarea') {
            $value = $el->textContent;
        } elseif ($el->nodeName === 'select') {
            $sel = $xp->query('.//option[@selected]', $el)->item(0) ?? $xp->query('.//option', $el)->item(0);
            $value = $sel ? $sel->getAttribute('value') : '';
        } elseif ($type === 'checkbox' && !$el->hasAttribute('checked')) {
            continue;
        } else {
            $value = $el->getAttribute('value');
        }
        $pairs[] = rawurlencode($name) . '=' . rawurlencode($value);
    }
    parse_str(implode('&', $pairs), $out);
    return $out;
}

function test_every_seed_page_roundtrips_through_the_editor(): void
{
    $db = test_db();
    seed_import($db, __DIR__ . '/../content/seed', temp_dir());
    editor_login($db);
    foreach ($db->query('SELECT id FROM pages ORDER BY id')->fetchAll(PDO::FETCH_COLUMN) as $id) {
        $page = page_find($db, (int) $id);
        $_POST = editor_submitted(editor_get($db, admin_edit_url($page))->body);
        $form = editor_page_from_post($page);
        $_POST = [];
        eq($form['sections'], $page['sections'], "{$page['slug']}: sections survive the form");
        foreach (['title', 'slug', 'h1', 'meta_title', 'meta_description'] as $k) {
            eq($form[$k], $page[$k], "{$page['slug']}: $k");
        }
        eq([positive_int($form['og_image_id']), positive_int($form['template_id']), (int) $form['noindex']],
            [positive_int($page['og_image_id']), positive_int($page['template_id']), (int) $page['noindex']], "{$page['slug']}: ids and noindex");
    }
    eq((int) page_find_published($db, 'blog')['noindex'], 1, 'the seed blog index imports as noindex');
    $_SESSION = [];
}

function test_dates_say_their_timezone_and_the_db_agrees(): void
{
    eq(admin_date('2026-10-28 15:16:00'), '28/10/26, 3:16 PM ' . date('T', strtotime('2026-10-28 15:16:00')));
    $prev = date_default_timezone_get();
    date_default_timezone_set('Asia/Kolkata');
    try {
        eq(admin_date('2026-10-28 15:16:00'), '28/10/26, 3:16 PM IST');
        $db = test_db(); // connects after the change: its session follows PHP's offset
        eq((int) $db->query('SELECT TIMESTAMPDIFF(MINUTE, UTC_TIMESTAMP(), NOW())')->fetchColumn(), 330);
    } finally {
        date_default_timezone_set($prev);
    }
    $db = test_db();
    eq((int) $db->query('SELECT TIMESTAMPDIFF(MINUTE, UTC_TIMESTAMP(), NOW())')->fetchColumn(), intdiv((int) date('Z'), 60), 'DB clock = PHP clock');
    $id = editor_page($db, ['status' => 'published']);
    $p = page_find($db, $id);
    ok(abs(strtotime($p['published_at']) - time()) < 120 && abs(strtotime($p['updated_at']) - time()) < 120, 'DB timestamps read back in PHP time');
}

function test_template_link_cannot_be_removed(): void
{
    $db = test_db();
    editor_login($db);
    $src = editor_page($db, ['slug' => 'source']);
    $tid = template_from_page($db, $src, 'Service', ['0.heading']);
    $id = page_from_template($db, $tid, 'from-template', 'From template', 1);
    $html = editor_get($db, "/admin/pages/$id")->body;
    ok(!str_contains($html, 'name="template_id"') && str_contains($html, 'Service'), 'shown read-only');
    ok(str_contains(editor_get($db, "/admin/pages/$src")->body, 'name="template_id"'), 'a page without one can still pick one');
    editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['template_id' => '']));
    eq((int) page_find($db, $id)['template_id'], $tid, 'a posted change is ignored');
    $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['template_id' => '', 'action' => 'publish',
        'sections' => [['type' => 'rich_text', 'heading' => 'Hi', 'body' => '<p>Body words</p>']]]));
    eq($r->status, 422);
    ok(str_contains($r->body, 'still the same as in the'), 'the unique-field rule still applies');
    $_SESSION = [];
}

function test_preview_loads_no_tracking(): void
{
    $db = test_db();
    editor_login($db);
    setting_set($db, 'ga4_id', 'G-TEST123');
    setting_set($db, 'meta_pixel_id', '1234567');
    $id = editor_page($db);
    ok(str_contains(render_page(page_find($db, $id), settings($db)), 'gtag'), 'the public page would load GA4');
    $html = editor_get($db, "/admin/preview/$id")->body;
    ok(!str_contains($html, 'gtag') && !str_contains($html, 'fbq') && !str_contains($html, 'G-TEST123'), 'no GA4 or Meta Pixel in preview');
    $_SESSION = [];
}

function test_home_and_blog_cannot_be_unpublished(): void
{
    $db = test_db();
    editor_login($db);
    foreach (['home', 'blog'] as $slug) {
        $id = editor_page($db, ['slug' => $slug, 'status' => 'published']);
        ok(!str_contains(editor_get($db, "/admin/pages/$id")->body, 'value="draft"'), "$slug: no Unpublish button");
        $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['action' => 'draft']));
        eq($r->status, 422, $slug);
        ok(str_contains($r->body, "can&#039;t be unpublished"), $slug);
        eq(page_find($db, $id)['status'], 'published', "$slug stays live");
    }
    $other = editor_page($db, ['slug' => 'other', 'status' => 'published']);
    ok(str_contains(editor_get($db, "/admin/pages/$other")->body, 'value="draft"'), 'other live pages keep Unpublish');
    $_SESSION = [];
}

function test_publish_needs_a_title(): void
{
    $db = test_db();
    editor_login($db);
    $id = editor_page($db);
    $r = editor_post($db, "/admin/pages/$id", editor_form($db, $id, ['title' => '', 'action' => 'publish']));
    eq($r->status, 422);
    ok(str_contains($r->body, 'id="e-title"') && str_contains($r->body, 'The title is required.'), 'shown under the title field');
    eq(page_find($db, $id)['status'], 'draft');
    $_SESSION = [];
}

function test_media_picker_feed_survives_bad_alt(): void
{
    try {
        media_clean_alt("Kiln \xff photo");
        ok(false, 'invalid UTF-8 accepted');
    } catch (InvalidArgumentException $e) {
        ok(str_contains($e->getMessage(), 'description'), $e->getMessage());
    }
}
