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
