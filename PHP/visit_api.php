<?php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

set_error_handler(function ($no, $str) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => "PHP: $str"]);
    exit;
});
set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
});

function ok($d = null)
{
    echo json_encode(['ok' => true, 'data' => $d]);
    exit;
}
function fail($m = 'Error', $c = 400)
{
    http_response_code($c);
    echo json_encode(['ok' => false, 'error' => $m]);
    exit;
}
function clean($v)
{
    return trim((string)$v);
}

if ((int)($_SESSION['user_id'] ?? 0) <= 0) fail('Not authenticated', 401);

/* Ensure table exists — this version does NOT require created_by column */
$pdo->exec("CREATE TABLE IF NOT EXISTS visits (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  team VARCHAR(200) NOT NULL,
  visit_date DATE NOT NULL,
  status ENUM('planned','ongoing','completed','cancelled') NOT NULL DEFAULT 'planned',
  purpose VARCHAR(200) NOT NULL DEFAULT '',
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX(visit_date), INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

/* Check if created_by column exists; if yes, we'll write it, otherwise we won't */
$hasCreatedBy = false;
try {
    $col = $pdo->query("SHOW COLUMNS FROM visits LIKE 'created_by'")->fetch(PDO::FETCH_ASSOC);
    if ($col) {
        $hasCreatedBy = true;
    }
} catch (Throwable $e) { /* ignore */
}

$m = $_SERVER['REQUEST_METHOD'];
$a = $_GET['action'] ?? $_POST['action'] ?? 'list';

/* Export CSV */
if ($a === 'export' && $m === 'GET') {
    $q = clean($_GET['q'] ?? '');
    $st = strtolower(clean($_GET['status'] ?? 'all'));
    $f = clean($_GET['from'] ?? '');
    $t = clean($_GET['to'] ?? '');
    $w = [];
    $args = [];
    if ($q !== '') {
        $w[] = "(team LIKE :q1 OR purpose LIKE :q2 OR notes LIKE :q3)";
        $args[':q1'] = "%{$q}%";
        $args[':q2'] = "%{$q}%";
        $args[':q3'] = "%{$q}%";
    }
    if (in_array($st, ['planned', 'ongoing', 'completed', 'cancelled'], true)) {
        $w[] = "status=:st";
        $args[':st'] = $st;
    }
    if ($f !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) {
        $w[] = "visit_date>=:f";
        $args[':f'] = $f;
    }
    if ($t !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $t)) {
        $w[] = "visit_date<=:t";
        $args[':t'] = $t;
    }
    $sql = "SELECT team,visit_date,purpose,status,notes FROM visits";
    if ($w) $sql .= " WHERE " . implode(" AND ", $w);
    $sql .= " ORDER BY visit_date DESC,id DESC";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=visits_export_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['Team/Visitor', 'Date', 'Purpose', 'Status', 'Notes']);
    $stt = $pdo->prepare($sql);
    $stt->execute($args);
    while ($r = $stt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$r['team'], $r['visit_date'], $r['purpose'], ucfirst($r['status']), $r['notes']]);
    }
    fclose($out);
    exit;
}

/* List */
if ($m === 'GET' && $a === 'list') {
    $q = clean($_GET['q'] ?? '');
    $st = strtolower(clean($_GET['status'] ?? 'all'));
    $f = clean($_GET['from'] ?? '');
    $t = clean($_GET['to'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 10)));
    $off = ($page - 1) * $limit;

    $w = [];
    $args = [];
    if ($q !== '') {
        $w[] = "(team LIKE :q1 OR purpose LIKE :q2 OR notes LIKE :q3)";
        $args[':q1'] = "%{$q}%";
        $args[':q2'] = "%{$q}%";
        $args[':q3'] = "%{$q}%";
    }
    if (in_array($st, ['planned', 'ongoing', 'completed', 'cancelled'], true)) {
        $w[] = "status=:st";
        $args[':st'] = $st;
    }
    if ($f !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) {
        $w[] = "visit_date>=:f";
        $args[':f'] = $f;
    }
    if ($t !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $t)) {
        $w[] = "visit_date<=:t";
        $args[':t'] = $t;
    }
    $ws = $w ? (" WHERE " . implode(" AND ", $w)) : "";

    $cnt = $pdo->prepare("SELECT COUNT(*) c FROM visits" . $ws);
    $cnt->execute($args);
    $total = (int)($cnt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

    $sql = "SELECT id,team,visit_date,purpose,status,notes FROM visits" . $ws . " ORDER BY visit_date DESC,id DESC LIMIT :lim OFFSET :off";
    $stt = $pdo->prepare($sql);
    foreach ($args as $k => $v) {
        $stt->bindValue($k, $v);
    }
    $stt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stt->bindValue(':off', $off, PDO::PARAM_INT);
    $stt->execute();
    $rows = $stt->fetchAll(PDO::FETCH_ASSOC);

    ok(['items' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $limit)]);
}

/* Save (create/update) */
if ($m === 'POST' && $a === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $team = clean($_POST['team'] ?? '');
    $date = clean($_POST['date'] ?? '');
    $st = strtolower(clean($_POST['status'] ?? 'planned'));
    $pur = clean($_POST['purpose'] ?? '');
    $notes = clean($_POST['notes'] ?? '');

    if ($team === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) fail('Team and a valid date are required.');
    if (!in_array($st, ['planned', 'ongoing', 'completed', 'cancelled'], true)) $st = 'planned';

    if ($id > 0) {
        $u = $pdo->prepare("UPDATE visits SET team=:t,visit_date=:d,status=:s,purpose=:p,notes=:n WHERE id=:id");
        $u->execute([':t' => $team, ':d' => $date, ':s' => $st, ':p' => $pur, ':n' => $notes, ':id' => $id]);
        ok(['id' => $id]);
    } else {
        if ($hasCreatedBy) {
            $i = $pdo->prepare("INSERT INTO visits (team,visit_date,status,purpose,notes,created_by) VALUES (:t,:d,:s,:p,:n,:cb)");
            $i->execute([':t' => $team, ':d' => $date, ':s' => $st, ':p' => $pur, ':n' => $notes, ':cb' => (int)($_SESSION['user_id'] ?? 0)]);
        } else {
            $i = $pdo->prepare("INSERT INTO visits (team,visit_date,status,purpose,notes) VALUES (:t,:d,:s,:p,:n)");
            $i->execute([':t' => $team, ':d' => $date, ':s' => $st, ':p' => $pur, ':n' => $notes]);
        }
        ok(['id' => $pdo->lastInsertId()]);
    }
}

/* Delete */
if ($m === 'POST' && $a === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) fail('Invalid id');
    $d = $pdo->prepare("DELETE FROM visits WHERE id=:id");
    $d->execute([':id' => $id]);
    ok(true);
}

fail('Unsupported request', 404);
