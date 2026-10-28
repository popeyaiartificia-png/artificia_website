<?php
declare(strict_types=1);

// Admin redirects page: one table, one form per row (Save, Delete) and an Add row on top. Rules live in redirect_save().

function admin_redirects(PDO $db, string $method): HttpResponse
{
    $user = auth_require('seo', $db);
    $errors = []; // row key ('new' or the id) => message
    $draft = []; // row key => submitted values, so a failed row keeps what was typed
    if ($method === 'POST') {
        $id = (int) ($_POST['id'] ?? 0);
        if (($_POST['action'] ?? '') === 'delete') {
            $db->prepare('DELETE FROM redirects WHERE id = ?')->execute([$id]);
            return admin_redirect('/admin/redirects', 'Redirect deleted. The old address now shows a 404 unless a page lives there.');
        }
        $key = $id > 0 ? (string) $id : 'new';
        $draft[$key] = array_map(fn($v) => is_string($v) ? $v : '', array_intersect_key($_POST, array_flip(['from_path', 'to_path', 'match_type', 'code'])));
        try {
            redirect_save($db, $draft[$key] + ($id > 0 ? ['id' => $id] : []));
            return admin_redirect('/admin/redirects', $id > 0 ? 'Redirect updated. The change is live now.' : 'Redirect added. It is live now.');
        } catch (InvalidArgumentException $e) {
            $errors[$key] = $e->getMessage();
            $_SESSION['flash'] = ['msg' => $e->getMessage(), 'kind' => 'error'];
        }
    }
    $rows = $db->query('SELECT id, from_path, to_path, match_type, code FROM redirects ORDER BY from_path')->fetchAll();
    $known = array_flip(array_map(fn($r) => (string) $r['id'], $rows));
    $vars = ['title' => 'Redirects', 'rows' => $rows, 'errors' => $errors, 'draft' => $draft, 'orphan' => array_diff_key($errors, $known + ['new' => 1])];
    return new HttpResponse($errors ? 422 : 200, admin_view('redirects', $vars, $user));
}
