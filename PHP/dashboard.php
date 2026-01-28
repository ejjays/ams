<?php
require __DIR__ . '/auth_guard.php';
$current = basename($_SERVER['PHP_SELF']);
function active($page, $current)
{
  return $current === $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Accreditation Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../app/css/dashboard.css?v=1" />
  <link rel="stylesheet" href="../app/css/dashboard.v2.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../app/css/dashboard.layout.css?v=1" />
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="flex h-screen">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main -->
    
<main class="content">
  <section class="kpi-row section">
    <div class="kpi-card kpi--orange">
      <div class="kpi-icon"><i class="fa-solid fa-clipboard-list"></i></div>
      <div class="kpi-body">
        <div class="kpi-title">Total Programs</div>
        <div class="kpi-value" id="kpiPrograms">—</div>
      </div>
    </div>
    <div class="kpi-card kpi--purple">
      <div class="kpi-icon"><i class="fa-solid fa-calendar-check"></i></div>
      <div class="kpi-body">
        <div class="kpi-title">Total Visits</div>
        <div class="kpi-value" id="kpiVisits">—</div>
      </div>
    </div>
    <div class="kpi-card kpi--cyan">
      <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
      <div class="kpi-body">
        <div class="kpi-title">Total Users</div>
        <div class="kpi-value" id="kpiUsers">—</div>
      </div>
    </div>
    <div class="kpi-card kpi--blue">
      <div class="kpi-icon"><i class="fa-solid fa-file"></i></div>
      <div class="kpi-body">
        <div class="kpi-title">Total Documents</div>
        <div class="kpi-value" id="kpiDocuments">—</div>
      </div>
    </div>
  </section>

  <!-- AI Driven Analytics Section -->
  <section class="section">
    <div class="card bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500">
      <div class="card-title text-blue-800"><i class="fa-solid fa-robot mr-2"></i> AI Compliance Insights</div>
      <div id="aiSummary" class="text-gray-700 italic py-2">Analyzing progress...</div>
      <div id="aiProgressBars" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4"></div>
    </div>
  </section>

  <section class="grid-2 section">
    <div class="card">
      <div class="card-title">Evidence by Program</div>
      <div class="donut-wrap">
        <div class="chart-wrap"><canvas id="chartDonut" width="300" height="300"></canvas></div>
        <div class="donut-center" id="donutCenter">0</div>
      </div>
      <div class="legend" id="donutLegend"></div>
    </div>
    <div class="card">
      <div class="card-title">
        Activity (Last 7 days)
        <div class="card-sub">Submissions vs Reviews</div>
      </div>
      <canvas id="chartBar" height="220"></canvas>
    </div>
  </section>

  <section class="grid-2 section">
    <div class="card">
      <div class="card-title">
        Notice Board
        <button id="addNoticeBtn" class="btn-primary"><i class="fa-solid fa-plus"></i></button>
      </div>
      <ul class="notice-list" id="noticeList"></ul>
    </div>
    <div class="card">
      <div class="card-title">Visit Calendar</div>
      <div id="calendar" class="calendar"></div>
    </div>
  </section>
</main>

  </div>

  <!-- Announcement Modal -->
  <div id="announcementModal" class="modal hidden">
    <div class="modal-backdrop" data-close="true"></div>
    <div class="modal-card">
      <h3 class="font-semibold text-lg mb-4">New Announcement</h3>
      <form id="announcementForm">
        <div class="mb-4">
          <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
          <input type="text" id="title" name="title" class="w-full px-3 py-2 border rounded-md text-gray-700" placeholder="Title">
        </div>
        <div class="mb-4">
          <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
          <textarea id="message" name="message" rows="4" class="w-full px-3 py-2 border rounded-md text-gray-700" placeholder="Enter your message here"></textarea>
        </div>
        <div class="flex justify-between items-center">
          <button type="button" id="announcementCloseBtn" class="btn-light">Cancel</button>
          <button type="submit" class="btn-primary">Create</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../app/js/dashboard.js?v=2"></script>
  <script src="../app/js/dashboard_counts.js?v=1"></script>
  <script src="../app/js/dashboard_v2.js"></script>
</body>

</html>