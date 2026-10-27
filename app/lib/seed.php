<?php
declare(strict_types=1);

/** Stores a bundled image once (by filename) with its alt.json text, or one made from the filename; returns its media id. */
function seed_media(PDO $db, string $path, string $uploadsDir, array $alts): int
{
    $name = basename($path);
    $alt = trim((string) ($alts[$name] ?? '')) ?: ucwords(str_replace('-', ' ', pathinfo($name, PATHINFO_FILENAME)));
    return media_from_file($db, $path, $alt, $uploadsDir);
}

/** Image fields that name a file in $mediaDir become that file's media id; anything else is left for page_save to drop. */
function seed_resolve_images(PDO $db, array $sections, string $mediaDir, string $uploadsDir, array $alts = []): array
{
    $registry = section_registry();
    $swap = function (array $fields, array $defs) use (&$swap, $db, $mediaDir, $uploadsDir, $alts): array {
        foreach ($defs as $key => $def) {
            if (!isset($fields[$key])) {
                continue;
            }
            if ($def['type'] === 'image' && is_string($fields[$key]) && is_file($mediaDir . '/' . basename($fields[$key]))) {
                $fields[$key] = seed_media($db, $mediaDir . '/' . basename($fields[$key]), $uploadsDir, $alts);
            } elseif ($def['type'] === 'list' && is_array($fields[$key])) {
                $fields[$key] = array_map(fn($item) => is_array($item) ? $swap($item, $def['fields']) : $item, $fields[$key]);
            }
        }
        return $fields;
    };
    foreach ($sections as &$section) {
        $section['fields'] = $swap($section['fields'] ?? [], $registry[$section['type'] ?? '']['fields'] ?? []);
    }
    return $sections;
}

/** A decoded JSON object or list; a missing, unreadable or malformed file throws a RuntimeException naming it. */
function seed_json(string $path): array
{
    $raw = is_file($path) ? @file_get_contents($path) : false;
    if ($raw === false) {
        throw new RuntimeException(basename($path) . ': the file is missing or could not be read.');
    }
    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        throw new RuntimeException(basename($path) . ': ' . $e->getMessage() . '.');
    }
    return is_array($data) ? $data : throw new RuntimeException(basename($path) . ': expected a JSON object or list.');
}

/**
 * Imports content/seed: media, templates (by name), pages (by slug, published), redirects (by from_path)
 * and settings that are still empty. Refuses a site that already has pages unless $force.
 * Every file is read and checked before anything is written. A page that fails its publish rules is saved
 * as a draft, and the import then throws naming it.
 * @return array{pages:int, templates:int, redirects:int, media:int}
 */
function seed_import(PDO $db, string $dir, string $uploadsDir, bool $force = false): array
{
    if (!$force && (int) $db->query('SELECT COUNT(*) FROM pages')->fetchColumn() > 0) {
        throw new RuntimeException('The site already has pages, so the seed content was not imported. Use --force to import over them.');
    }
    $mediaDir = "$dir/media";
    $alts = is_file("$mediaDir/alt.json") ? seed_json("$mediaDir/alt.json") : [];
    $templateRows = seed_json("$dir/templates.json");
    foreach ($templateRows as $i => $t) {
        if (!is_string($t['name'] ?? null) || !is_array($t['sections'] ?? null) || !is_array($t['unique_fields'] ?? null)) {
            throw new RuntimeException('templates.json: template ' . ($i + 1) . ' needs a name, sections and unique_fields.');
        }
    }
    $pageRows = [];
    foreach (glob("$dir/pages/*.json") ?: [] as $file) {
        $page = seed_json($file);
        if (!is_string($page['slug'] ?? null) || !is_array($page['sections'] ?? [])) {
            throw new RuntimeException(basename($file) . ': a page needs a slug and a list of sections.');
        }
        if (isset($page['template']) && !in_array($page['template'], array_column($templateRows, 'name'), true)) {
            throw new RuntimeException(basename($file) . ": no template named \"{$page['template']}\".");
        }
        $pageRows[] = $page;
    }
    $redirectRows = seed_json("$dir/redirects.json");
    foreach ($redirectRows as $i => $r) {
        if (!is_string($r['from_path'] ?? null) || !is_string($r['to_path'] ?? null)) {
            throw new RuntimeException('redirects.json: redirect ' . ($i + 1) . ' needs a from_path and a to_path.');
        }
    }
    $settingRows = is_file("$dir/settings.json") ? seed_json("$dir/settings.json") : [];

    $images = array_filter(glob("$mediaDir/*") ?: [], fn($f) => preg_match('/\.(jpe?g|png|webp)$/i', $f) === 1);
    foreach ($images as $file) {
        seed_media($db, $file, $uploadsDir, $alts); // the whole set goes into the library, used or not
    }
    $resolve = fn(array $sections) => seed_resolve_images($db, $sections, $mediaDir, $uploadsDir, $alts);
    $idOf = function (string $sql, string $key) use ($db): ?int {
        $stmt = $db->prepare($sql);
        $stmt->execute([$key]);
        return positive_int($stmt->fetchColumn());
    };

    $templates = [];
    foreach ($templateRows as $t) {
        $args = [page_json(sections_normalize($resolve($t['sections']))), page_json(array_values($t['unique_fields'])), $t['name']];
        $id = $idOf('SELECT id FROM templates WHERE name = ?', $t['name']);
        if ($id) {
            $db->prepare('UPDATE templates SET sections = ?, unique_fields = ? WHERE name = ?')->execute($args);
        } else {
            $db->prepare('INSERT INTO templates (sections, unique_fields, name) VALUES (?, ?, ?)')->execute($args);
            $id = (int) $db->lastInsertId();
        }
        $templates[$t['name']] = $id;
    }

    $drafts = [];
    foreach ($pageRows as $page) {
        if (isset($page['template'])) {
            $page['template_id'] = $templates[$page['template']];
        }
        $page['sections'] = sections_normalize($resolve($page['sections'] ?? []));
        $errors = page_publish_errors($db, $page);
        $page['status'] = $errors ? 'draft' : 'published';
        if ($errors) {
            $drafts[] = "{$page['slug']} ({$errors[0]})";
        }
        $page['id'] = $idOf('SELECT id FROM pages WHERE slug = ?', $page['slug']);
        page_save($db, $page, 0);
    }

    foreach ($redirectRows as $r) {
        $from = '/' . ltrim(trim($r['from_path']), '/'); // as redirect_save stores it, so a re-run finds the row
        redirect_save($db, ['id' => $idOf('SELECT id FROM redirects WHERE from_path = ?', $from)] + $r);
    }

    $current = settings($db);
    foreach ($settingRows as $key => $value) {
        if (trim($current[$key] ?? '') === '') {
            setting_set($db, (string) $key, (string) $value);
        }
    }

    if ($drafts) {
        throw new RuntimeException('Imported, but these pages fail their publish rules and were saved as drafts: ' . implode('; ', $drafts));
    }
    return ['pages' => count($pageRows), 'templates' => count($templates), 'redirects' => count($redirectRows), 'media' => count($images)];
}
