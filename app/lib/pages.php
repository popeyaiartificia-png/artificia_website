<?php
declare(strict_types=1);

/** A published page or post by slug, with `sections` decoded. Home has the slug `home`. */
function page_find_published(PDO $db, string $slug): ?array
{
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
    if (!$page) {
        return null;
    }
    $page['sections'] = json_decode((string) $page['sections'], true) ?: [];
    return $page;
}

/** Public path of a page: `/` for home, `/blog/{slug}` for posts. */
function page_path(array $page): string
{
    return match (true) {
        $page['slug'] === 'home' => '/',
        $page['kind'] === 'post' => '/blog/' . $page['slug'],
        default => '/' . $page['slug'],
    };
}

/** Newest published posts first. */
function posts_recent(PDO $db, int $limit): array
{
    $stmt = $db->prepare("SELECT slug, title, excerpt, published_at FROM pages
        WHERE kind = 'post' AND status = 'published' ORDER BY published_at DESC, id DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
