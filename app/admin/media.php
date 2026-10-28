<?php
declare(strict_types=1);

// Admin media library: the page (upload, grid, edit alt) and the JSON feed the image picker reads.
// No delete in v1: removing an image would break every page that uses it.

function media_uploads_dir(): string
{
    return getenv('ARTIFICIA_UPLOADS') ?: dirname(__DIR__, 2) . '/public/uploads';
}

/** Why an upload with a PHP error code failed, in words for the editor. */
function media_upload_error(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_NO_FILE => 'Choose an image to upload.',
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That image is too big - please upload a file under 15 MB.',
        UPLOAD_ERR_PARTIAL => 'The upload was interrupted - please try again.',
        default => 'The upload failed on the server - please try again.',
    };
}

/** Every image, newest first, with its display name and the size of its largest stored file. */
function media_list(PDO $db): array
{
    $rows = $db->query('SELECT id, base_name, alt, width, height, source FROM media ORDER BY id DESC')->fetchAll();
    $dir = media_uploads_dir();
    foreach ($rows as &$r) {
        $r['name'] = $r['source'] ?: $r['alt'];
        $widths = media_widths((int) $r['width']); // same choice as media_url(), without a query per row
        $thumbW = array_filter($widths, fn(int $w) => $w <= 480);
        $r['thumb'] = media_rendition_url($r, $thumbW ? max($thumbW) : min($widths));
        $r['url'] = media_rendition_url($r, max($widths));
        $file = $dir . '/' . substr((string) $r['url'], strlen('/uploads/'));
        $r['kb'] = is_file($file) ? (int) round(filesize($file) / 1024) : 0;
    }
    return $rows;
}

function admin_media_json(PDO $db, string $method): HttpResponse
{
    if (!auth_user($db)) {
        return new HttpResponse(401, '[]', null, 'application/json; charset=utf-8');
    }
    $list = array_map(fn(array $r) => [
        'id' => (int) $r['id'], 'alt' => $r['alt'], 'thumb' => $r['thumb'], 'url' => $r['url'],
        'width' => (int) $r['width'], 'height' => (int) $r['height'],
    ], media_list($db));
    return new HttpResponse(200, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE), null, 'application/json; charset=utf-8');
}

function admin_media(PDO $db, string $method): HttpResponse
{
    $user = auth_require('seo', $db);
    $uploadError = $altError = null;
    $altFor = ['id' => 0, 'alt' => ''];
    $altDraft = '';
    if ($method === 'POST') {
        try {
            if (($_POST['action'] ?? '') === 'alt') {
                $id = (int) ($_POST['id'] ?? 0);
                if (!media_row($db, $id)) {
                    return new HttpResponse(404, admin_view('notfound', ['title' => 'Not found'], $user));
                }
                $altFor = ['id' => $id, 'alt' => (string) ($_POST['alt'] ?? '')];
                $db->prepare('UPDATE media SET alt = ? WHERE id = ?')->execute([media_clean_alt($altFor['alt']), $id]);
                return admin_redirect('/admin/media', 'Description saved.');
            }
            $altDraft = (string) ($_POST['alt'] ?? '');
            $file = $_FILES['file'] ?? null;
            $code = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($code !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException(media_upload_error($code));
            }
            media_store($db, (string) $file['tmp_name'], (string) $file['name'], $altDraft, media_uploads_dir());
            return admin_redirect('/admin/media', 'Image uploaded.');
        } catch (InvalidArgumentException $e) {
            if ($altFor['id']) {
                $altError = $e->getMessage();
            } else {
                $uploadError = $e->getMessage();
            }
        } catch (RuntimeException) {
            $uploadError = 'The image could not be saved on the server - please try again.';
        }
    }
    $items = media_list($db);
    $vars = ['title' => 'Media', 'items' => $items, 'uploadError' => $uploadError, 'altError' => $altError, 'altFor' => $altFor, 'altDraft' => $altDraft];
    return new HttpResponse($uploadError || $altError ? 422 : 200, admin_view('media', $vars, $user));
}
