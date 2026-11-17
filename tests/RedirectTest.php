<?php
declare(strict_types=1);

function redirect_test_add(PDO $db, string $from, string $to, string $type, int $code = 301): void
{
    $db->prepare('INSERT INTO redirects (from_path, to_path, match_type, code) VALUES (?, ?, ?, ?)')->execute([$from, $to, $type, $code]);
}

function test_redirect_exact_beats_prefix(): void
{
    $db = test_db();
    redirect_test_add($db, '/co-orporate/', '/corporate-training', 'prefix');
    redirect_test_add($db, '/co-orporate/x.html', '/corporate-training/x', 'exact', 302);
    eq(redirect_match($db, '/co-orporate/x.html'), ['to' => '/corporate-training/x', 'code' => 302]);
    eq(redirect_match($db, '/co-orporate/anything'), ['to' => '/corporate-training', 'code' => 301]);
    eq(redirect_match($db, '/elsewhere'), null);
}

function test_redirect_longest_prefix_wins(): void
{
    $db = test_db();
    redirect_test_add($db, '/a/', '/short', 'prefix');
    redirect_test_add($db, '/a/b/', '/long', 'prefix');
    eq(redirect_match($db, '/a/b/c')['to'], '/long');
    eq(redirect_match($db, '/a/z')['to'], '/short');
}

function test_redirect_prefix_row_covers_its_bare_folder(): void
{
    $db = test_db();
    redirect_test_add($db, '/landing-page/', '/ai-masterclass', 'prefix');
    redirect_test_add($db, '/landing-page', '/elsewhere', 'exact', 302);
    redirect_test_add($db, '/co-orporate/', '/corporate-ai-training', 'prefix');
    eq(redirect_match($db, '/co-orporate'), ['to' => '/corporate-ai-training', 'code' => 301]);
    eq(redirect_match($db, '/landing-page'), ['to' => '/elsewhere', 'code' => 302], 'an exact row still wins');
    eq(redirect_match($db, '/co-orporatex'), null, 'only the folder itself');
    eq(redirect_match($db, '/co-orporat'), null);
}

function test_redirect_prefix_treats_percent_literally(): void
{
    $db = test_db();
    redirect_test_add($db, '/50%/', '/x', 'prefix');
    eq(redirect_match($db, '/50abc/'), null);
}

function test_redirect_refuses_unsafe_targets(): void
{
    $db = test_db();
    redirect_test_add($db, '/js', 'javascript:alert(1)', 'exact');
    redirect_test_add($db, '/proto', '//evil.example', 'exact');
    redirect_test_add($db, '/abs', 'https://example.com/ok', 'exact');
    redirect_test_add($db, '/weird', '/ok', 'exact', 200);
    eq(redirect_match($db, '/js'), null);
    eq(redirect_match($db, '/proto'), null);
    eq(redirect_match($db, '/abs'), ['to' => 'https://example.com/ok', 'code' => 301]);
    eq(redirect_match($db, '/weird'), ['to' => '/ok', 'code' => 301]); // non-redirect codes fall back to 301
}

function test_redirect_keeps_query(): void
{
    eq(redirect_location('/new', 'gclid=abc&utm_source=g'), '/new?gclid=abc&utm_source=g');
    eq(redirect_location('/new', ''), '/new');
    eq(redirect_location('/new?x=1', 'gclid=abc'), '/new?x=1');
    eq(redirect_location('/new#top', 'a=1'), '/new?a=1#top');
    eq(redirect_location('https://example.com/p', 'a=1'), 'https://example.com/p?a=1');
}

function redirect_test_rejects(callable $f, string $needle): void
{
    try {
        $f();
    } catch (InvalidArgumentException $e) {
        ok(str_contains($e->getMessage(), $needle), "message should mention '$needle', got: " . $e->getMessage());
        return;
    }
    throw new RuntimeException("expected a rejection mentioning '$needle'");
}

function redirect_test_row(string $from, string $to, array $more = []): array
{
    return ['from_path' => $from, 'to_path' => $to] + $more;
}

function test_redirect_save_inserts_and_updates(): void
{
    $db = test_db();
    $id = redirect_save($db, redirect_test_row('  old  ', '/new'));
    eq(redirect_match($db, '/old'), ['to' => '/new', 'code' => 301]); // trimmed, leading slash added, defaults applied
    eq(redirect_save($db, redirect_test_row('/old', '/newer', ['id' => $id, 'code' => 302])), $id);
    eq(redirect_match($db, '/old'), ['to' => '/newer', 'code' => 302]);
    eq((int) $db->query('SELECT COUNT(*) FROM redirects')->fetchColumn(), 1);
}

function test_redirect_to_self_rejected(): void
{
    $db = test_db();
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/a', '/a')), 'itself');
}

function test_redirect_loop_rejected(): void
{
    $db = test_db();
    redirect_save($db, redirect_test_row('/a', '/b'));
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/b', '/a')), 'loop');
    redirect_save($db, redirect_test_row('/b', '/c'));
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/c', '/a')), 'loop'); // longer chain
    eq(redirect_match($db, '/c'), null); // the rejected row was not kept
}

function test_redirect_loop_through_prefix_rejected(): void
{
    $db = test_db();
    redirect_save($db, redirect_test_row('/old/', '/new/x', ['match_type' => 'prefix']));
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/new/x', '/old/y')), 'loop');
}

function test_redirect_prefix_cannot_redirect_its_bare_folder_to_itself(): void
{
    $db = test_db();
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/old/', '/old', ['match_type' => 'prefix'])), 'loop');
    eq(redirect_match($db, '/old'), null, 'the rejected rule was rolled back');
}

function test_redirect_long_chains_allow_destinations_but_reject_cycles(): void
{
    $db = test_db();
    for ($i = 0; $i < 12; $i++) {
        redirect_save($db, redirect_test_row("/step-$i", '/step-' . ($i + 1)));
    }
    ok(redirect_save($db, redirect_test_row('/entry', '/step-0')) > 0, 'a long chain ending at a destination is valid');
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/step-12', '/step-0')), 'loop');
    eq(redirect_match($db, '/step-12'), null, 'closing the long cycle saved nothing');
}

function test_redirect_cannot_enter_an_existing_cycle(): void
{
    $db = test_db();
    redirect_test_add($db, '/cycle-a', '/cycle-b', 'exact');
    redirect_test_add($db, '/cycle-b', '/cycle-a', 'exact');
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/entry', '/cycle-a')), 'loop');
    eq(redirect_match($db, '/entry'), null);
}

function test_redirect_prefix_can_target_a_more_specific_exact_rule(): void
{
    $db = test_db();
    redirect_save($db, redirect_test_row('/old/exception', '/contact'));
    ok(redirect_save($db, redirect_test_row('/old/', '/old/exception', ['match_type' => 'prefix'])) > 0);
    eq(redirect_match($db, '/old/exception')['to'], '/contact', 'the exact rule wins and ends the chain');
}

function test_redirect_save_accepts_the_wordpress_page_lookup(): void
{
    $db = test_db();
    $lookup = fn(string $path): bool => $path === '/wordpress-page';
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row(' wordpress-page ', '/new'), $lookup), 'A page already lives at /wordpress-page');
    eq(redirect_match($db, '/wordpress-page'), null);
    ok(redirect_save($db, redirect_test_row('/old', '/new'), $lookup) > 0);
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/new', '/old'), $lookup), 'loop');
    eq(redirect_match($db, '/new'), null, 'WordPress rules use the same rollback on failure');
}

function test_redirect_update_checks_loop_against_the_edited_row(): void
{
    $db = test_db();
    $a = redirect_save($db, redirect_test_row('/a', '/b'));
    redirect_save($db, redirect_test_row('/b', '/c'));
    redirect_save($db, redirect_test_row('/a', '/z', ['id' => $a]));
    ok(redirect_save($db, redirect_test_row('/c', '/a')) > 0); // the old a->b edge is gone, so c->a is no loop now
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/z', '/b')), 'loop'); // z->b->c->a->z
}

function test_redirect_cannot_shadow_page(): void
{
    $db = test_db();
    $db->exec("INSERT INTO pages (kind, slug, title, status, sections) VALUES ('page', 'about', 'About', 'published', '[]'), ('page', 'draft-one', 'D', 'draft', '[]'), ('page', 'home', 'Home', 'published', '[]'), ('post', 'hello', 'Hello', 'published', '[]')");
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/about', '/x')), 'A page already lives at /about');
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/', '/x')), 'A page already lives at /');
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/blog/hello', '/x')), 'A page already lives at /blog/hello');
    ok(redirect_save($db, redirect_test_row('/draft-one', '/x')) > 0); // a draft hides nothing
}

function test_redirect_duplicate_from_rejected(): void
{
    $db = test_db();
    $id = redirect_save($db, redirect_test_row('/a', '/b'));
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/a', '/c')), 'already a redirect from /a');
    ok(redirect_save($db, redirect_test_row('/a', '/c', ['id' => $id])) === $id); // editing the same row is fine
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/q', '/c', ['id' => 999])), 'no longer exists');
}

function test_redirect_field_rules(): void
{
    $db = test_db();
    foreach (['/a?x=1', '/a#top', 'https://evil.example/a', '//evil.example/a', '/a b', ''] as $from) {
        redirect_test_rejects(fn() => redirect_save($db, redirect_test_row($from, '/b')), 'old address');
    }
    foreach (['b', '//evil.example', 'javascript:alert(1)', '/\\evil.example', '/a b', ''] as $to) {
        redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/a', $to)), 'new address');
    }
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/a', '/b', ['match_type' => 'regex'])), 'exact or prefix');
    redirect_test_rejects(fn() => redirect_save($db, redirect_test_row('/a', '/b', ['code' => 200])), '301');
    ok(redirect_save($db, redirect_test_row('/ok', 'https://example.com/p?x=1#y')) > 0);
}

function test_seed_map_complete(): void
{
    $db = test_db();
    foreach (json_decode(file_get_contents(__DIR__ . '/../content/seed/redirects.json'), true, 512, JSON_THROW_ON_ERROR) as $r) {
        redirect_save($db, $r);
    }
    $expected = [ // spec section 7, old path => new path
        '/index.html' => '/', '/about.html' => '/about', '/team.html' => '/about',
        '/contact.html' => '/contact', '/registration.html' => '/contact',
        '/trainingcourses.html' => '/ai-masterclass', '/landing-page/' => '/ai-masterclass',
        '/landing-page/index.html' => '/ai-masterclass', '/landing-page/thanks/x.html' => '/ai-masterclass',
        '/co-orporate/' => '/corporate-ai-training', '/co-orporate/index.html' => '/corporate-ai-training',
        '/landing-page' => '/ai-masterclass', '/co-orporate' => '/corporate-ai-training', // bare: the prefix rows cover them too
        '/projects.html' => '/case-studies/orkay-tiles', '/faqs.html' => '/ai-masterclass#faq',
        '/hiring.html' => '/', '/services.html' => '/services',
        '/privacypolicy.html' => '/privacy', '/Termsandcondition.html' => '/terms',
    ];
    foreach ($expected as $old => $new) {
        eq(redirect_match($db, $old), ['to' => $new, 'code' => 301], $old);
    }
    $types = $db->query("SELECT from_path FROM redirects WHERE match_type = 'prefix' ORDER BY from_path")->fetchAll(PDO::FETCH_COLUMN);
    eq($types, ['/co-orporate/', '/landing-page/']);
}

// ---------- Admin redirects page ----------

function redirect_admin_login(PDO $db): void
{
    $_SESSION = ['uid' => user_create($db, 'seo@example.com', 'correct horse 1', 'seo'), 'csrf' => 'tok'];
    $_POST = [];
}

function redirect_admin_post(PDO $db, array $post): HttpResponse
{
    $_POST = ['_csrf' => 'tok'] + $post;
    return admin_handle($db, 'POST', '/admin/redirects');
}

function redirect_admin_count(PDO $db): int
{
    return (int) $db->query('SELECT COUNT(*) FROM redirects')->fetchColumn();
}

function test_admin_redirects_lists_adds_edits_and_deletes(): void
{
    $db = test_db();
    redirect_admin_login($db); // an SEO user: the SEO editor fixes redirects
    $_POST = [];
    redirect_save($db, redirect_test_row('/old', '/new'));
    $page = admin_handle($db, 'GET', '/admin/redirects');
    eq($page->status, 200);
    ok(str_contains($page->body, 'value="/old"') && str_contains($page->body, 'value="/new"'), 'row shown');
    ok(str_contains($page->body, 'href="/admin/redirects"'), 'nav links to it now');

    $r = redirect_admin_post($db, ['action' => 'save', 'from_path' => 'gone', 'to_path' => '/home', 'match_type' => 'exact', 'code' => '302']);
    eq([$r->status, $r->location], [302, '/admin/redirects']);
    eq(redirect_match($db, '/gone'), ['to' => '/home', 'code' => 302]);

    $id = (int) $db->query("SELECT id FROM redirects WHERE from_path = '/old'")->fetchColumn();
    redirect_admin_post($db, ['action' => 'save', 'id' => (string) $id, 'from_path' => '/old', 'to_path' => '/newer', 'match_type' => 'prefix', 'code' => '301']);
    eq($db->query("SELECT to_path, match_type FROM redirects WHERE id = $id")->fetch(PDO::FETCH_NUM), ['/newer', 'prefix']);
    eq(redirect_admin_count($db), 2, 'edited in place, not duplicated');

    $r = redirect_admin_post($db, ['action' => 'delete', 'id' => (string) $id]);
    eq([$r->status, $r->location], [302, '/admin/redirects']);
    eq(redirect_admin_count($db), 1);
    eq(redirect_admin_post($db, ['action' => 'delete', 'id' => (string) $id])->status, 302, 'deleting it twice is harmless');
    $_SESSION = $_POST = [];
}

function test_admin_redirect_loop_shows_error_and_saves_nothing(): void
{
    $db = test_db();
    redirect_admin_login($db);
    redirect_save($db, redirect_test_row('/a', '/b'));
    $db->exec("INSERT INTO pages (kind, slug, title, status, sections) VALUES ('page', 'about', 'About', 'published', '[]')");

    $r = redirect_admin_post($db, ['action' => 'save', 'from_path' => '/b', 'to_path' => '/a', 'match_type' => 'exact', 'code' => '301']);
    eq($r->status, 422);
    ok(str_contains($r->body, 'redirect loop'), 'the library message is shown');
    ok(str_contains($r->body, 'value="/b"') && str_contains($r->body, 'role="alert"'), 'the typed values stay in the add row');
    ok(str_contains($r->body, 'toast--error'), 'and an error toast');
    eq(redirect_admin_count($db), 1, 'nothing saved');

    $id = (int) $db->query("SELECT id FROM redirects WHERE from_path = '/a'")->fetchColumn();
    $r = redirect_admin_post($db, ['action' => 'save', 'id' => (string) $id, 'from_path' => '/about', 'to_path' => '/x', 'match_type' => 'exact', 'code' => '301']);
    eq($r->status, 422);
    ok(str_contains($r->body, 'A page already lives at /about'), 'shown on the row being edited');
    eq($db->query("SELECT from_path FROM redirects WHERE id = $id")->fetchColumn(), '/a', 'the row is unchanged');

    $r = redirect_admin_post($db, ['action' => 'save', 'from_path' => '/z', 'to_path' => '/z', 'match_type' => 'exact', 'code' => '301']);
    ok($r->status === 422 && str_contains($r->body, 'to itself'));
    $_SESSION = $_POST = [];
}

function test_admin_redirect_delete_requires_csrf(): void
{
    $db = test_db();
    redirect_admin_login($db);
    $id = redirect_save($db, redirect_test_row('/a', '/b'));
    $_POST = ['action' => 'delete', 'id' => (string) $id]; // no token
    eq(admin_handle($db, 'POST', '/admin/redirects')->status, 403);
    $_POST = ['_csrf' => 'wrong', 'action' => 'delete', 'id' => (string) $id];
    eq(admin_handle($db, 'POST', '/admin/redirects')->status, 403);
    eq(redirect_admin_count($db), 1, 'still there');
    $_SESSION = $_POST = [];
}

function test_admin_redirects_requires_login(): void
{
    $db = test_db();
    $_SESSION = $_POST = [];
    $r = admin_handle($db, 'GET', '/admin/redirects');
    eq([$r->status, $r->location], [302, '/admin/login']);
}
