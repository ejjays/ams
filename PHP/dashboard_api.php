<?php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

function respond($ok, $data = null, $error = null, $code = 200)
{
  http_response_code($code);
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
  if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
  }
  exit;
}
if ((int)($_SESSION['user_id'] ?? 0) <= 0) {
  respond(false, null, 'Not authenticated', 401);
}
function table_exists(PDO $pdo, string $table): bool
{
  try {
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
  } catch (Throwable $e) {
    return false;
  }
}
function safe_count(PDO $pdo, string $table): int
{
  try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
    return (int)$stmt->fetchColumn();
  } catch (Throwable $e) {
    return 0;
  }
}

$action = $_GET['action'] ?? '';
if ($action === 'summary') {
  $totals = [
    'programs'  => table_exists($pdo, 'programs')  ? safe_count($pdo, 'programs')  : 0,
    'visits'    => table_exists($pdo, 'visits')    ? safe_count($pdo, 'visits')    : 0,
    'users'     => table_exists($pdo, 'users')     ? safe_count($pdo, 'users')     : 0,
    'documents' => table_exists($pdo, 'documents') ? safe_count($pdo, 'documents') : 0,
  ];

  // Donut: Evidence documents uploaded per Program (always 5 labels for 5 programs)
  // We attribute documents via indicator links -> indicator_labels -> parameter_labels -> parameters -> sections -> level_programs -> programs.
  $donut = ['labels' => [], 'data' => [], 'center' => 0];
  try {
    $need = ['programs', 'level_programs', 'sections', 'parameters', 'parameter_labels', 'indicator_labels', 'indicator_document_links'];
    $ok = true;
    foreach ($need as $t) {
      if (!table_exists($pdo, $t)) {
        $ok = false;
        break;
      }
    }
    if ($ok) {
      $sql = "
        SELECT p.name AS label, COALESCE(c.n,0) AS n
        FROM programs p
        LEFT JOIN (
          SELECT lp.program_id, COUNT(*) AS n
          FROM indicator_document_links l
          JOIN documents d          ON d.id  = l.document_id
          JOIN indicator_labels il   ON il.id = l.indicator_id
          JOIN parameter_labels pl   ON pl.id = il.parameter_label_id
          JOIN parameters par        ON par.id = pl.parameter_id
          JOIN sections s            ON s.id  = par.section_id
          JOIN level_programs lp     ON lp.level_id = s.level_id
          GROUP BY lp.program_id
        ) c ON c.program_id = p.id
        ORDER BY p.id
      ";
      $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
      if ($rows) {
        $donut['labels'] = array_column($rows, 'label');
        $donut['data'] = array_map('intval', array_column($rows, 'n'));
        $donut['center'] = array_sum($donut['data']);
      }
    }
  } catch (Throwable $e) {
  }

  // Fallback when no program tables yet: just show nothing (five slices make no sense without programs)
  if (!$donut['labels']) {
    $donut = ['labels' => ['No programs'], 'data' => [0], 'center' => 0];
  }

  $attendance = ['labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 'present' => [], 'absent' => []];
  try {
    if (table_exists($pdo, 'visits')) {
      $stmt = $pdo->query("SELECT DATE(`visit_date`) as d, COUNT(*) as n FROM `visits` WHERE `visit_date` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(`visit_date`)");
      $byDay = [];
      foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $byDay[$r['d']] = (int)$r['n'];
      }
      $present = [];
      $absent = [];
      for ($i = 0; $i < 7; $i++) {
        $day = date('Y-m-d', strtotime('-' . (6 - $i) . ' days'));
        $n = $byDay[$day] ?? 0;
        $present[] = $n;
        $absent[] = (int)round($n * 0.3);
      }
      $attendance['present'] = $present;
      $attendance['absent'] = $absent;
    }
  } catch (Throwable $e) {
  }
  if (!$attendance['present']) {
    $attendance['present'] = [220, 80, 240, 60, 260, 280, 70];
    $attendance['absent'] = [20, 10, 40, 15, 30, 35, 12];
  }

  $notices = [];
  try {
    // 1) Upcoming visits (next 30 days)
    if (table_exists($pdo, 'visits')) {
      $stmt = $pdo->query("SELECT visit_date, COALESCE(NULLIF(TRIM(purpose),''), team, 'Visit') AS t, status
                           FROM visits
                           WHERE visit_date >= CURDATE() AND visit_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                           ORDER BY visit_date ASC LIMIT 5");
      foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notices[] = [
          'title' => 'Upcoming visit: ' . ($r['t'] ?: 'Visit'),
          'date' => date('d M, Y', strtotime($r['visit_date'])),
          'views' => 0
        ];
      }
      // Overdue visits (planned but past date)
      $stt = $pdo->query("SELECT visit_date, COALESCE(NULLIF(TRIM(purpose),''), team, 'Visit') AS t
                           FROM visits
                           WHERE visit_date < CURDATE() AND (status IS NULL OR status IN ('planned','ongoing'))
                           ORDER BY visit_date DESC LIMIT 3");
      foreach ($stt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notices[] = [
          'title' => 'Overdue visit: ' . ($r['t'] ?: 'Visit'),
          'date' => date('d M, Y', strtotime($r['visit_date'])),
          'views' => 0
        ];
      }
    }
    // 2) Tasks due soon / overdue
    if (table_exists($pdo, 'tasks')) {
      $dueSoon = $pdo->query("SELECT title, due_date, status FROM tasks
                              WHERE due_date IS NOT NULL AND due_date >= CURDATE() AND due_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
                                AND (status IS NULL OR status NOT IN ('Completed','completed','cancelled'))
                              ORDER BY due_date ASC LIMIT 5");
      foreach ($dueSoon->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notices[] = [
          'title' => 'Task due: ' . ($r['title'] ?: 'Task'),
          'date' => date('d M, Y', strtotime($r['due_date'])),
          'views' => 0
        ];
      }
      $over = $pdo->query("SELECT title, due_date FROM tasks
                           WHERE due_date IS NOT NULL AND due_date < CURDATE()
                             AND (status IS NULL OR status NOT IN ('Completed','completed','cancelled'))
                           ORDER BY due_date DESC LIMIT 3");
      foreach ($over->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notices[] = [
          'title' => 'Task overdue: ' . ($r['title'] ?: 'Task'),
          'date' => date('d M, Y', strtotime($r['due_date'])),
          'views' => 0
        ];
      }
    }
    // 3) Recently uploaded documents (last 7 days)
    if (table_exists($pdo, 'documents')) {
      $docs = $pdo->query("SELECT title, created_at FROM documents
                           WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                           ORDER BY created_at DESC LIMIT 5");
      foreach ($docs->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notices[] = [
          'title' => 'New document: ' . ($r['title'] ?: 'Untitled'),
          'date' => date('d M, Y', strtotime($r['created_at'])),
          'views' => 0
        ];
      }
    }
    // Sort by date desc (string dates are converted)
    usort($notices, function ($a, $b) {
      $da = strtotime($a['date'] ?? '1970-01-01');
      $db = strtotime($b['date'] ?? '1970-01-01');
      return $db <=> $da;
    });
    // Cap to 8 items
    $notices = array_slice($notices, 0, 8);
  } catch (Throwable $e) {
  }
  if (!$notices) {
    $notices = [
      ['title' => 'School annual sports day celebration 2023', 'date' => '20 July, 2023', 'views' => 20000],
      ['title' => 'Annual Function celebration 2023-24', 'date' => '05 July, 2023', 'views' => 15000],
      ['title' => 'Mid term examination routine published', 'date' => '15 June, 2023', 'views' => 22000],
      ['title' => 'Inter school annual painting competition', 'date' => '18 May, 2023', 'views' => 18000],
    ];
  }

  $events = [];
  try {
    if (table_exists($pdo, 'visits')) {
      $stmt = $pdo->query("SELECT DATE(`visit_date`) as d, COALESCE(NULLIF(TRIM(purpose),''), team, 'Visit') as t FROM visits ORDER BY `visit_date` DESC LIMIT 100");
      foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $events[] = ['date' => $r['d'], 'title' => $r['t'] ?: 'Visit'];
      }
    }
  } catch (Throwable $e) {
  }
  if (!$events) {
    $events = [['date' => date('Y-m-') . '05', 'title' => 'Planning'], ['date' => date('Y-m-') . '12', 'title' => 'Submission'], ['date' => date('Y-m-') . '20', 'title' => 'Review']];
  }

  respond(true, [
    'totals' => $totals,
    'donut' => $donut,
    'attendance' => $attendance,
    'notices' => $notices,
    'events' => $events,
    'ai_analytics' => get_ai_analytics($pdo)
  ]);
}

/**
 * AI-Driven Progress Analytics
 * Calculates compliance and generates a Gemini summary.
 */
function get_ai_analytics($pdo) {
    require_once __DIR__ . '/Gemini.php';
    
    try {
        // 1. Calculate Progress
        $sql = "
            SELECT 
                p.name as program,
                (SELECT COUNT(*) FROM indicator_labels) as total_required,
                COUNT(DISTINCT l.indicator_id) as uploaded_count
            FROM programs p
            LEFT JOIN level_programs lp ON lp.program_id = p.id
            LEFT JOIN sections s ON s.level_id = lp.level_id
            LEFT JOIN parameters par ON par.section_id = s.id
            LEFT JOIN parameter_labels pl ON pl.parameter_id = par.id
            LEFT JOIN indicator_labels il ON il.parameter_label_id = pl.id
            LEFT JOIN indicator_document_links l ON l.indicator_id = il.id
            GROUP BY p.id
        ";
        $stmt = $pdo->query($sql);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $prompt = "As an Accreditation Assistant, summarize this progress for the dashboard in 2-3 concise sentences. Mention specific percentages: ";
        foreach($stats as &$s) {
            $perc = $s['total_required'] > 0 ? round(($s['uploaded_count'] / $s['total_required']) * 100) : 0;
            $s['percentage'] = $perc;
            $prompt .= "{$s['program']}: {$perc}%, ";
        }
        unset($s); // break the reference

        // 2. Get AI Summary
        $summary = Gemini::ask($prompt) ?: "Progress data updated. Please check compliance details per program.";

        return [
            'stats' => $stats,
            'summary' => $summary
        ];
    } catch (Throwable $e) {
        return ['stats' => [], 'summary' => 'AI Analytics temporarily unavailable.'];
    }
}

$facilities = table_exists($pdo, 'facilities') ? safe_count($pdo, 'facilities') : 0;
$programs   = table_exists($pdo, 'programs')   ? safe_count($pdo, 'programs')   : 0;
$visits     = table_exists($pdo, 'visits')     ? safe_count($pdo, 'visits')     : 0;
$users      = table_exists($pdo, 'users')      ? safe_count($pdo, 'users')      : 0;
respond(true, ['facilities' => $facilities, 'programs' => $programs, 'visits' => $visits, 'users' => $users]);
