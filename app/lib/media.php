<?php
declare(strict_types=1);

const MEDIA_WIDTHS = [480, 960, 1600];
const MEDIA_MAX_BYTES = 15 * 1024 * 1024;
const MEDIA_MAX_PIXELS = 40_000_000;
const MEDIA_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

/** Renditions stored for a source of this width: never upscaled; a tiny source keeps its own width. */
function media_widths(int $srcWidth): array
{
    return array_values(array_filter(MEDIA_WIDTHS, fn(int $w) => $w <= $srcWidth)) ?: [$srcWidth];
}

/** Trimmed alt text, or an InvalidArgumentException with a user-facing message. */
function media_clean_alt(string $alt): string
{
    $alt = trim($alt);
    if ($alt === '') {
        throw new InvalidArgumentException('Please add a short description of the image (alt text) - it helps Google and screen-reader users.');
    }
    if (mb_strlen($alt) > 500) {
        throw new InvalidArgumentException('The image description is too long - keep it under 500 characters.');
    }
    return $alt;
}

/** Validates, resizes to WebP renditions under $uploadsDir/YYYY/MM/ and returns the new media id. */
function media_store(PDO $db, string $tmpPath, string $originalName, string $alt, string $uploadsDir, ?string $source = null): int
{
    $alt = media_clean_alt($alt);
    if (!is_file($tmpPath) || filesize($tmpPath) > MEDIA_MAX_BYTES) {
        throw new InvalidArgumentException('That image is too big - please upload a file under 15 MB.');
    }
    if (!in_array((new finfo(FILEINFO_MIME_TYPE))->file($tmpPath), MEDIA_TYPES, true)) {
        throw new InvalidArgumentException('That file is not a JPEG, PNG or WebP image.');
    }
    $size = getimagesize($tmpPath);
    if ($size === false) {
        throw new InvalidArgumentException('That image could not be read - try saving it again as a JPEG or PNG.');
    }
    if ($size[0] * $size[1] > MEDIA_MAX_PIXELS) {
        throw new InvalidArgumentException('That image has too many pixels - please shrink it to under 40 megapixels (about 8000 px wide) first.');
    }

    // shortcut: sized for photos up to ~8000 px; upgrade to Imagick if larger uploads are needed.
    ini_set('memory_limit', '512M');
    $src = imagecreatefromstring((string) file_get_contents($tmpPath));
    if ($src === false) {
        throw new InvalidArgumentException('That image could not be read - try saving it again as a JPEG or PNG.');
    }
    imagepalettetotruecolor($src);
    $src = media_orient($src, $tmpPath);
    [$w, $h] = [imagesx($src), imagesy($src)];

    $written = [];
    $db->beginTransaction();
    try {
        $db->prepare('INSERT INTO media (base_name, alt, width, height, source) VALUES (?, ?, ?, ?, ?)')
            ->execute(['', $alt, $w, $h, $source]);
        $id = (int) $db->lastInsertId();
        $base = date('Y/m') . "/$id";
        if (!is_dir(dirname("$uploadsDir/$base")) && !@mkdir(dirname("$uploadsDir/$base"), 0755, true) && !is_dir(dirname("$uploadsDir/$base"))) {
            throw new RuntimeException('Could not create the uploads folder.');
        }
        foreach (media_widths($w) as $rw) {
            $path = "$uploadsDir/$base-$rw.webp";
            $written[] = $path;
            if (!@imagewebp(media_resize($src, $rw), $path, 80)) {
                throw new RuntimeException("Could not save the {$rw}px version of the image.");
            }
        }
        $db->prepare('UPDATE media SET base_name = ? WHERE id = ?')->execute([$base, $id]);
        $db->commit();
        return $id;
    } catch (Throwable $t) {
        $db->rollBack();
        array_map('unlink', array_filter($written, 'is_file'));
        throw $t;
    }
}

/** Rotates a phone photo upright according to its EXIF Orientation tag. */
function media_orient(GdImage $im, string $path): GdImage
{
    try {
        $orientation = @exif_read_data($path)['Orientation'] ?? 1;
    } catch (Throwable) {
        return $im; // PNG/WebP or no EXIF block
    }
    $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
    return $angle ? imagerotate($im, $angle, 0) : $im;
}

/** Copy of $im scaled to $w px wide, keeping PNG transparency. */
function media_resize(GdImage $im, int $w): GdImage
{
    $h = max(1, (int) round(imagesy($im) * $w / imagesx($im)));
    $out = imagecreatetruecolor($w, $h);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
    imagecopyresampled($out, $im, 0, 0, 0, 0, $w, $h, imagesx($im), imagesy($im));
    return $out;
}

/** Stores a bundled file once: re-running the seed importer returns the existing id. */
function media_from_file(PDO $db, string $path, string $alt, string $uploadsDir): int
{
    $stmt = $db->prepare('SELECT id FROM media WHERE source = ?');
    $stmt->execute([basename($path)]);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int) $id : media_store($db, $path, basename($path), $alt, $uploadsDir, basename($path));
}

function media_row(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT base_name, alt, width, height FROM media WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function media_rendition_url(array $row, int $w): string
{
    return "/uploads/{$row['base_name']}-$w.webp";
}

/** URL of the largest stored rendition not wider than $maxW (the smallest if none fits); null for an unknown id. */
function media_url(PDO $db, int $id, int $maxW = 1600): ?string
{
    $row = media_row($db, $id);
    if (!$row) {
        return null;
    }
    $widths = media_widths((int) $row['width']);
    $fit = array_filter($widths, fn(int $w) => $w <= $maxW);
    return media_rendition_url($row, $fit ? max($fit) : min($widths));
}

function img_tag(PDO $db, int $id, string $sizes, bool $eager = false): string
{
    $row = media_row($db, $id);
    if (!$row) {
        return '';
    }
    $widths = media_widths((int) $row['width']);
    $srcset = implode(', ', array_map(fn(int $w) => media_rendition_url($row, $w) . " {$w}w", $widths));
    $src = media_rendition_url($row, in_array(960, $widths, true) ? 960 : max($widths));
    $loading = $eager ? 'loading="eager" fetchpriority="high"' : 'loading="lazy" decoding="async"';
    return '<img src="' . e($src) . '" srcset="' . e($srcset) . '" sizes="' . e($sizes) . '" width="' . (int) $row['width']
        . '" height="' . (int) $row['height'] . '" alt="' . e($row['alt']) . '" ' . $loading . '>';
}
