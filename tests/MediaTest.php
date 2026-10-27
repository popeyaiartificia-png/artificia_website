<?php
declare(strict_types=1);

/** Fresh temp uploads dir; callers remove it with media_test_cleanup(). */
function media_test_dir(): string
{
    $dir = sys_get_temp_dir() . '/artificia-media-' . bin2hex(random_bytes(4));
    mkdir($dir);
    return $dir;
}

function media_test_cleanup(string $dir): void
{
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}

/** Writes a GD-made image fixture and returns its path. $exifOrientation injects an EXIF Orientation tag into JPEGs. */
function media_fixture(string $dir, int $w, int $h, string $type = 'jpeg', ?int $exifOrientation = null): string
{
    $im = imagecreatetruecolor($w, $h);
    if ($type === 'png') {
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 255, 0, 0, 127)); // fully transparent
    } else {
        imagefill($im, 0, 0, imagecolorallocate($im, 200, 100, 50));
    }
    $path = "$dir/fixture-$w-$h.$type";
    match ($type) {
        'jpeg' => imagejpeg($im, $path, 70),
        'png' => imagepng($im, $path),
        'webp' => imagewebp($im, $path),
    };
    if ($exifOrientation !== null) {
        // SOI + APP1("Exif", big-endian TIFF, one IFD0 entry: Orientation) + rest of the JPEG
        $tiff = "MM\x00\x2a\x00\x00\x00\x08" . "\x00\x01" . "\x01\x12\x00\x03\x00\x00\x00\x01" . pack('n', $exifOrientation) . "\x00\x00" . "\x00\x00\x00\x00";
        $app1 = "Exif\x00\x00" . $tiff;
        $jpeg = file_get_contents($path);
        file_put_contents($path, "\xFF\xD8\xFF\xE1" . pack('n', strlen($app1) + 2) . $app1 . substr($jpeg, 2));
    }
    return $path;
}

function test_media_resizes_large(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $id = media_store($db, media_fixture($dir, 6000, 4000), 'big.jpg', 'A big photo', $dir);
        $base = $db->query("SELECT base_name FROM media WHERE id = $id")->fetchColumn();
        ok((bool) preg_match('#^\d{4}/\d{2}/' . $id . '$#', $base), $base);
        foreach ([480, 960, 1600] as $w) {
            $file = "$dir/$base-$w.webp";
            ok(is_file($file), "$w missing");
            eq((new finfo(FILEINFO_MIME_TYPE))->file($file), 'image/webp');
            eq(getimagesize($file)[0], $w);
            eq(getimagesize($file)[1], (int) round($w * 2 / 3));
        }
        $row = $db->query("SELECT width, height FROM media WHERE id = $id")->fetch();
        eq([(int) $row['width'], (int) $row['height']], [6000, 4000]);
    } finally {
        media_test_cleanup($dir);
    }
}

function test_media_rejects_fake_image(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        file_put_contents("$dir/x.jpg", '<?php echo 1;');
        try {
            media_store($db, "$dir/x.jpg", 'x.jpg', 'Alt', $dir);
            ok(false, 'should have thrown');
        } catch (InvalidArgumentException $e) {
            ok(str_contains($e->getMessage(), 'JPEG, PNG or WebP'), $e->getMessage());
        }
        eq((int) $db->query('SELECT COUNT(*) FROM media')->fetchColumn(), 0);
    } finally {
        media_test_cleanup($dir);
    }
}

function test_media_requires_alt(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $f = media_fixture($dir, 600, 400);
        foreach (['', '   '] as $alt) {
            try {
                media_store($db, $f, 'a.jpg', $alt, $dir);
                ok(false, 'should have thrown');
            } catch (InvalidArgumentException $e) {
                ok(str_contains($e->getMessage(), 'description'), $e->getMessage());
            }
        }
    } finally {
        media_test_cleanup($dir);
    }
}

function test_media_rejects_oversize_file(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $f = media_fixture($dir, 600, 400);
        file_put_contents($f, str_repeat("\0", 15 * 1024 * 1024), FILE_APPEND);
        try {
            media_store($db, $f, 'a.jpg', 'Alt', $dir);
            ok(false, 'should have thrown');
        } catch (InvalidArgumentException $e) {
            ok(str_contains($e->getMessage(), '15 MB'), $e->getMessage());
        }
    } finally {
        media_test_cleanup($dir);
    }
}

function test_small_image_not_upscaled(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $id = media_store($db, media_fixture($dir, 300, 200), 's.jpg', 'Small', $dir);
        $base = $db->query("SELECT base_name FROM media WHERE id = $id")->fetchColumn();
        $files = glob("$dir/$base-*.webp");
        eq(count($files), 1);
        eq(getimagesize($files[0])[0], 300);
        eq(media_widths(300), [300]);
        eq(media_widths(1000), [480, 960]);
        eq(media_widths(480), [480]);
    } finally {
        media_test_cleanup($dir);
    }
}

function test_media_applies_exif_orientation(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $id = media_store($db, media_fixture($dir, 400, 200, 'jpeg', 6), 'p.jpg', 'Portrait', $dir);
        $row = $db->query("SELECT base_name, width, height FROM media WHERE id = $id")->fetch();
        eq([(int) $row['width'], (int) $row['height']], [200, 400], 'dimensions after rotation');
        $size = getimagesize("$dir/{$row['base_name']}-200.webp");
        eq([$size[0], $size[1]], [200, 400]);
    } finally {
        media_test_cleanup($dir);
    }
}

function test_media_keeps_png_transparency(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $id = media_store($db, media_fixture($dir, 600, 300, 'png'), 'l.png', 'Logo', $dir);
        $base = $db->query("SELECT base_name FROM media WHERE id = $id")->fetchColumn();
        $im = imagecreatefromwebp("$dir/$base-480.webp");
        ok(((imagecolorat($im, 10, 10) >> 24) & 0x7F) > 100, 'corner should still be transparent');
    } finally {
        media_test_cleanup($dir);
    }
}

function test_media_failed_write_leaves_nothing(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $f = media_fixture($dir, 1000, 500);
        // The first row will be id 1; a directory squatting on its 960 file makes the second write fail.
        mkdir($dir . '/' . date('Y/m') . '/1-960.webp', 0755, true);
        try {
            media_store($db, $f, 'a.jpg', 'Alt', $dir);
            ok(false, 'should have thrown');
        } catch (Exception) { // RuntimeException in production; the test runner turns the GD warning into an ErrorException
        }
        eq((int) $db->query('SELECT COUNT(*) FROM media')->fetchColumn(), 0);
        eq(glob($dir . '/' . date('Y/m') . '/1-*.webp'), [$dir . '/' . date('Y/m') . '/1-960.webp']);
    } finally {
        media_test_cleanup($dir);
    }
}

function test_img_tag_has_dimensions_and_lazy(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $id = media_store($db, media_fixture($dir, 2000, 1000), 'a.jpg', 'A "quoted" <b> alt', $dir);
        $base = $db->query("SELECT base_name FROM media WHERE id = $id")->fetchColumn();
        $tag = img_tag($db, $id, '(max-width: 600px) 100vw, 50vw');
        ok(str_contains($tag, 'src="/uploads/' . $base . '-960.webp"'), $tag);
        ok(str_contains($tag, 'srcset="/uploads/' . $base . '-480.webp 480w, /uploads/' . $base . '-960.webp 960w, /uploads/' . $base . '-1600.webp 1600w"'), $tag);
        ok(str_contains($tag, 'sizes="(max-width: 600px) 100vw, 50vw"'), $tag);
        ok(str_contains($tag, 'width="2000" height="1000"'), $tag);
        ok(str_contains($tag, 'alt="A &quot;quoted&quot; &lt;b&gt; alt"'), $tag);
        ok(str_contains($tag, 'loading="lazy" decoding="async"'), $tag);
        $eager = img_tag($db, $id, '100vw', true);
        ok(str_contains($eager, 'loading="eager" fetchpriority="high"'), $eager);
        ok(!str_contains($eager, 'lazy'), $eager);
        eq(img_tag($db, 9999, '100vw'), '');
    } finally {
        media_test_cleanup($dir);
    }
}

function test_img_tag_small_image_uses_only_stored_width(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $id = media_store($db, media_fixture($dir, 300, 200), 's.jpg', 'Small', $dir);
        $base = $db->query("SELECT base_name FROM media WHERE id = $id")->fetchColumn();
        $tag = img_tag($db, $id, '100vw');
        ok(str_contains($tag, 'src="/uploads/' . $base . '-300.webp"'), $tag);
        ok(str_contains($tag, 'srcset="/uploads/' . $base . '-300.webp 300w"'), $tag);
    } finally {
        media_test_cleanup($dir);
    }
}

function test_media_url_picks_largest_within_max(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $id = media_store($db, media_fixture($dir, 2000, 1000), 'a.jpg', 'Alt', $dir);
        $base = $db->query("SELECT base_name FROM media WHERE id = $id")->fetchColumn();
        eq(media_url($db, $id), "/uploads/$base-1600.webp");
        eq(media_url($db, $id, 1200), "/uploads/$base-960.webp");
        eq(media_url($db, $id, 100), "/uploads/$base-480.webp", 'smallest when none fits');
        eq(media_url($db, 9999), null);
    } finally {
        media_test_cleanup($dir);
    }
}

function test_media_from_file_is_idempotent(): void
{
    $db = test_db();
    $dir = media_test_dir();
    try {
        $f = media_fixture($dir, 600, 400);
        $a = media_from_file($db, $f, 'Seed photo', $dir);
        $b = media_from_file($db, $f, 'Seed photo', $dir);
        eq($a, $b);
        eq((int) $db->query('SELECT COUNT(*) FROM media')->fetchColumn(), 1);
        eq($db->query("SELECT source FROM media WHERE id = $a")->fetchColumn(), basename($f));
        eq(count(glob("$dir/*/*/*.webp")), 1, 'renditions written once');
    } finally {
        media_test_cleanup($dir);
    }
}
