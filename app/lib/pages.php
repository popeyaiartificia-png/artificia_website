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

const SLUG_RESERVED = ['admin', 'blog', '_design', 'assets', 'uploads', 'lead', 'sitemap.xml', 'robots.txt', 'live', 'legacy'];
const PAGE_TEXT_LIMITS = ['title' => 255, 'h1' => 255, 'meta_title' => 255, 'meta_description' => 500];

/** Up to three lowercase segments; the first must not be one the router or server owns. `home` is just an ordinary slug. */
function slug_valid(string $slug): bool
{
    $ok = preg_match('#^[a-z0-9]+(-[a-z0-9]+)*(/[a-z0-9]+(-[a-z0-9]+)*){0,2}$#D', $slug) === 1;
    return $ok && !in_array(explode('/', $slug)[0], SLUG_RESERVED, true);
}

/** Any page or post by id (draft or published), with `sections` decoded. */
function page_find(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM pages WHERE id = ?');
    $stmt->execute([$id]);
    $page = $stmt->fetch();
    if (!$page) {
        return null;
    }
    $page['sections'] = json_decode((string) $page['sections'], true) ?: [];
    return $page;
}

function page_json(mixed $value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
}

/** Positive int from an int or digit string, else null. */
function positive_int(mixed $v): ?int
{
    $ok = (is_int($v) || (is_string($v) && ctype_digit($v))) && (int) $v > 0;
    return $ok ? (int) $v : null;
}

/** One field value cleaned to its registry type, or null when it should be dropped. */
function field_normalize(mixed $v, array $def): mixed
{
    if ($def['type'] === 'list') {
        return is_array($v) ? list_normalize($v, $def['fields']) : null;
    }
    if ($def['type'] === 'image') {
        return positive_int($v);
    }
    if (!is_scalar($v)) {
        return null;
    }
    $v = trim((string) $v);
    return match ($def['type']) {
        'richtext' => sanitize_html($v),
        'select' => in_array($v, $def['options'], true) ? $v : null,
        default => $v,
    };
}

/** Keeps only the registry's fields, each cleaned to its type. */
function fields_normalize(array $in, array $defs): array
{
    $out = [];
    foreach ($defs as $name => $def) {
        $v = array_key_exists($name, $in) ? field_normalize($in[$name], $def) : null;
        if ($v !== null) {
            $out[$name] = $v;
        }
    }
    return $out;
}

function list_normalize(array $items, array $defs): array
{
    $out = [];
    foreach ($items as $item) {
        $fields = is_array($item) ? fields_normalize($item, $defs) : [];
        if (array_filter($fields, fn($v) => $v !== '' && $v !== [])) {
            $out[] = $fields;
        }
    }
    return $out;
}

/** Unknown section types and fields are dropped; this is the trust boundary for editor input. */
function sections_normalize(array $sections): array
{
    $registry = section_registry();
    $out = [];
    foreach ($sections as $section) {
        $type = is_array($section) ? ($section['type'] ?? null) : null;
        if (!is_string($type) || !isset($registry[$type])) {
            continue;
        }
        $fields = is_array($section['fields'] ?? null) ? $section['fields'] : [];
        $out[] = ['type' => $type, 'fields' => fields_normalize($fields, $registry[$type]['fields'])];
    }
    return $out;
}

/**
 * Insert (no `id`) or update a page or post, and snapshot it into page_revisions.
 * Keys missing from $page keep their stored value on update. Drafts may be incomplete: publish rules live in page_publish_errors().
 * @throws InvalidArgumentException with an editor-readable message
 */
function page_save(PDO $db, array $page, int $userId): int
{
    $id = positive_int($page['id'] ?? null);
    $current = $id ? page_find($db, $id) : null;
    if ($id && !$current) {
        throw new InvalidArgumentException('That page no longer exists.');
    }
    $row = array_replace($current ?? [], $page);

    $slug = trim((string) ($row['slug'] ?? ''));
    $kind = ($row['kind'] ?? '') === 'post' ? 'post' : 'page';
    // The blog index is the one page allowed to take the reserved word "blog"; posts live at /blog/{slug} with a single segment.
    if (!slug_valid($slug) && !($kind === 'page' && $slug === 'blog')) {
        throw new InvalidArgumentException("The slug \"$slug\" is not allowed. Use lowercase letters, numbers and hyphens, with at most two slashes, and not a reserved first word such as admin or blog.");
    }
    if ($kind === 'post' && str_contains($slug, '/')) {
        throw new InvalidArgumentException("A blog post's web address can't contain a slash.");
    }
    $taken = $db->prepare('SELECT 1 FROM pages WHERE slug = ? AND id <> ?');
    $taken->execute([$slug, $id ?? 0]);
    if ($taken->fetchColumn()) {
        throw new InvalidArgumentException("The slug \"$slug\" is already used by another page.");
    }

    $values = ['slug' => $slug, 'kind' => $kind];
    foreach (PAGE_TEXT_LIMITS as $col => $max) {
        $values[$col] = trim((string) ($row[$col] ?? ''));
        if (mb_strlen($values[$col]) > $max) {
            throw new InvalidArgumentException("The $col is too long (at most $max characters).");
        }
    }
    $excerpt = trim((string) ($row['excerpt'] ?? ''));
    $status = ($row['status'] ?? '') === 'published' ? 'published' : 'draft';
    $values += [
        'og_image_id' => positive_int($row['og_image_id'] ?? null),
        'excerpt' => $excerpt === '' ? null : $excerpt,
        'sections' => page_json(sections_normalize(is_array($row['sections'] ?? null) ? $row['sections'] : [])),
        'template_id' => positive_int($row['template_id'] ?? null),
        'status' => $status,
        'published_at' => $row['published_at'] ?? ($status === 'published' ? date('Y-m-d H:i:s') : null),
        'updated_by' => $userId > 0 ? $userId : null,
    ];

    $own = !$db->inTransaction();
    $own && $db->beginTransaction();
    try {
        if ($id) {
            $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($values)));
            $db->prepare("UPDATE pages SET $set WHERE id = :id")->execute($values + ['id' => $id]);
        } else {
            $cols = implode(', ', array_keys($values));
            $marks = implode(', ', array_map(fn($c) => ":$c", array_keys($values)));
            $db->prepare("INSERT INTO pages ($cols) VALUES ($marks)")->execute($values);
            $id = (int) $db->lastInsertId();
        }
        $db->prepare('INSERT INTO page_revisions (page_id, snapshot, user_id) VALUES (?, ?, ?)')
            ->execute([$id, page_json(page_find($db, $id)), $values['updated_by']]);
        $own && $db->commit();
    } catch (Throwable $t) {
        $own && $db->rollBack();
        throw $t;
    }
    return $id;
}

function field_empty(mixed $v): bool
{
    return is_string($v) ? trim(strip_tags($v)) === '' : (is_array($v) ? $v === [] : $v === null);
}

/** Plain-English reasons this page cannot be published yet; empty means it can. */
function page_publish_errors(PDO $db, array $page): array
{
    $errors = [];
    foreach (['h1' => 'The page heading (h1)', 'meta_title' => 'The meta title', 'meta_description' => 'The meta description'] as $col => $label) {
        if (trim((string) ($page[$col] ?? '')) === '') {
            $errors[] = "$label is required.";
        }
    }
    $registry = section_registry();
    $sections = $page['sections'] ?? [];
    foreach ($sections as $i => $section) {
        $def = $registry[$section['type'] ?? ''] ?? null;
        $n = $i + 1;
        if (!$def) {
            $errors[] = "Section $n has an unknown type and should be removed.";
            continue;
        }
        foreach (section_field_errors($section['fields'] ?? [], $def['fields']) as $msg) {
            $errors[] = "Section $n ({$def['label']}): $msg";
        }
    }
    return array_merge($errors, template_unique_errors($db, $page, $sections, $registry));
}

/** Required-field messages for one section's fields (or, with $item set, one list item's). */
function section_field_errors(array $fields, array $defs, ?int $item = null): array
{
    $errors = [];
    foreach ($defs as $name => $def) {
        $v = $fields[$name] ?? null;
        $empty = $def['type'] === 'image' ? positive_int($v) === null : field_empty($v);
        if (!empty($def['required']) && $empty) {
            $errors[] = $def['type'] === 'list' ? "{$def['label']} needs at least one item."
                : "{$def['label']} is required" . ($item ? " in item $item." : '.');
        }
        if ($def['type'] === 'list' && is_array($v)) {
            foreach (array_values($v) as $j => $row) {
                $errors = array_merge($errors, section_field_errors($row, $def['fields'], $j + 1));
            }
        }
    }
    return $errors;
}

/** Fields the template marks as page-specific must be filled in and differ from the template's copy. */
function template_unique_errors(PDO $db, array $page, array $sections, array $registry): array
{
    $tid = positive_int($page['template_id'] ?? null);
    $stmt = $db->prepare('SELECT name, sections, unique_fields FROM templates WHERE id = ?');
    $stmt->execute([$tid ?? 0]);
    $tpl = $stmt->fetch();
    if (!$tpl) {
        return [];
    }
    $tplSections = json_decode($tpl['sections'], true) ?: [];
    $errors = [];
    foreach (json_decode($tpl['unique_fields'], true) ?: [] as $path) {
        [$i, $name] = explode('.', $path, 2) + [1 => ''];
        $section = $sections[(int) $i] ?? null;
        $n = (int) $i + 1;
        if (!$section) {
            $errors[] = "Section $n is missing; the \"{$tpl['name']}\" template expects it.";
            continue;
        }
        $def = $registry[$section['type']] ?? ['label' => $section['type'], 'fields' => []];
        $label = $def['fields'][$name]['label'] ?? $name;
        $mine = $section['fields'][$name] ?? null;
        $theirs = $tplSections[(int) $i]['fields'][$name] ?? null;
        if (field_empty($mine)) {
            $errors[] = "Section $n ({$def['label']}): $label must be written for this page.";
        } elseif ($mine === $theirs) { // strict: PHP's == treats "1" and "01" as equal
            $errors[] = "Section $n ({$def['label']}): $label is still the same as in the \"{$tpl['name']}\" template; write something specific to this page.";
        }
    }
    return $errors;
}

/** A new draft with the same content and meta as page $id. */
function page_duplicate(PDO $db, int $id, string $newSlug, int $userId): int
{
    $page = page_find($db, $id) ?? throw new InvalidArgumentException('That page was not found.');
    $copy = array_intersect_key($page, array_flip(['kind', 'title', 'h1', 'meta_title', 'meta_description', 'og_image_id', 'excerpt', 'sections', 'template_id']));
    return page_save($db, $copy + ['slug' => $newSlug, 'status' => 'draft'], $userId);
}

/** Saves a page's sections as a template. $uniqueFields are "<sectionIndex>.<field>" paths each page built from it must rewrite. */
function template_from_page(PDO $db, int $pageId, string $name, array $uniqueFields): int
{
    $page = page_find($db, $pageId) ?? throw new InvalidArgumentException('That page was not found.');
    $name = trim($name);
    $exists = $db->prepare('SELECT 1 FROM templates WHERE name = ?');
    $exists->execute([$name]);
    if ($name === '' || $exists->fetchColumn()) {
        throw new InvalidArgumentException($name === '' ? 'Give the template a name.' : "A template named \"$name\" already exists.");
    }
    $db->prepare('INSERT INTO templates (name, sections, unique_fields) VALUES (?, ?, ?)')
        ->execute([$name, page_json($page['sections']), page_json(array_values(array_filter($uniqueFields, 'is_string')))]);
    return (int) $db->lastInsertId();
}

/** A new draft page with the template's sections. */
function page_from_template(PDO $db, int $templateId, string $slug, string $title, int $userId): int
{
    $stmt = $db->prepare('SELECT sections FROM templates WHERE id = ?');
    $stmt->execute([$templateId]);
    $sections = $stmt->fetchColumn();
    if ($sections === false) {
        throw new InvalidArgumentException('That template was not found.');
    }
    return page_save($db, [
        'slug' => $slug, 'title' => $title, 'status' => 'draft', 'template_id' => $templateId,
        'sections' => json_decode($sections, true) ?: [],
    ], $userId);
}
