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

function seed_json(string $path): array
{
    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

/**
 * Imports content/seed: media, templates (by name), pages (by slug, published), redirects (by from_path)
 * and settings that are still empty. Refuses a site that already has pages unless $force.
 * @return array{pages:int, templates:int, redirects:int, media:int}
 */
function seed_import(PDO $db, string $dir, string $uploadsDir, bool $force = false): array
{
    if (!$force && (int) $db->query('SELECT COUNT(*) FROM pages')->fetchColumn() > 0) {
        throw new RuntimeException('The site already has pages, so the seed content was not imported. Use --force to import over them.');
    }
    $mediaDir = "$dir/media";
    $alts = is_file("$mediaDir/alt.json") ? seed_json("$mediaDir/alt.json") : [];
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
    foreach (seed_json("$dir/templates.json") as $t) {
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

    $pages = glob("$dir/pages/*.json") ?: [];
    foreach ($pages as $file) {
        $page = seed_json($file);
        if (isset($page['template'])) {
            $page['template_id'] = $templates[$page['template']] ?? throw new RuntimeException(basename($file) . ": no template named \"{$page['template']}\".");
        }
        $page['sections'] = $resolve($page['sections'] ?? []);
        $page['status'] = 'published';
        $page['id'] = $idOf('SELECT id FROM pages WHERE slug = ?', (string) ($page['slug'] ?? ''));
        page_save($db, $page, 0);
    }

    $redirects = seed_json("$dir/redirects.json");
    foreach ($redirects as $r) {
        redirect_save($db, ['id' => $idOf('SELECT id FROM redirects WHERE from_path = ?', $r['from_path'])] + $r);
    }

    $current = settings($db);
    foreach (is_file("$dir/settings.json") ? seed_json("$dir/settings.json") : [] as $key => $value) {
        if (trim($current[$key] ?? '') === '') {
            setting_set($db, $key, (string) $value);
        }
    }

    return ['pages' => count($pages), 'templates' => count($templates), 'redirects' => count($redirects), 'media' => count($images)];
}
