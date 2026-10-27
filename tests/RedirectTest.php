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
