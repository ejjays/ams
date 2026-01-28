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
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
      <div class="grid grid-cols-1 lg:grid-cols-12">
        <!-- AI Narrative Column -->
        <div class="lg:col-span-5 bg-slate-50 p-8 lg:p-10 border-r border-gray-100">
          <div class="flex items-center gap-3 mb-8">
            <div class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></div>
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">AI Intelligence Report</span>
          </div>
          
          <div class="mb-8">
            <h4 class="text-xs font-bold text-indigo-600 uppercase mb-2">Executive Summary</h4>
            <div id="aiSummary" class="text-slate-700 font-medium leading-relaxed space-y-3">
              <div class="skeleton-text w-full"></div>
              <div class="skeleton-text w-full"></div>
              <div class="skeleton-text w-full"></div>
              <div class="skeleton-text w-4/5"></div>
            </div>
          </div>

          <div>
            <h4 class="text-xs font-bold text-indigo-600 uppercase mb-2">Strategic Action</h4>
            <div id="aiAction" class="text-slate-600 text-sm italic border-l-2 border-indigo-200 pl-4 space-y-2">
              <div class="skeleton-text w-full"></div>
              <div class="skeleton-text w-2/3"></div>
            </div>
          </div>
        </div>

        <!-- Program Progress Column -->
        <div class="lg:col-span-7 p-8 lg:p-10">
          <div class="flex items-center justify-between mb-8">
            <h3 class="text-lg font-bold text-slate-900">Program Status</h3>
            <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full">Real-time Compliance</span>
          </div>
          <div id="aiProgressBars" class="grid grid-cols-1 gap-10">
            <!-- Skeleton Items -->
            <div class="space-y-4">
              <div class="flex justify-between"><div class="skeleton-text w-32"></div><div class="skeleton-text w-10"></div></div>
              <div class="skeleton-text skeleton-bar w-full"></div>
            </div>
            <div class="space-y-4">
              <div class="flex justify-between"><div class="skeleton-text w-40"></div><div class="skeleton-text w-10"></div></div>
              <div class="skeleton-text skeleton-bar w-full"></div>
            </div>
            <div class="space-y-4">
              <div class="flex justify-between"><div class="skeleton-text w-36"></div><div class="skeleton-text w-10"></div></div>
              <div class="skeleton-text skeleton-bar w-full"></div>
            </div>
            <div class="space-y-4">
              <div class="flex justify-between"><div class="skeleton-text w-44"></div><div class="skeleton-text w-10"></div></div>
              <div class="skeleton-text skeleton-bar w-full"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="grid-2 section">
    <div class="card">
      <div class="card-title text-slate-800 font-bold">Evidence by Program</div>
      <div class="donut-wrap mb-8">
        <div class="chart-wrap"><canvas id="chartDonut" width="300" height="300"></canvas></div>
        <div class="donut-center font-black text-slate-900" id="donutCenter">0</div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="donutLegend"></div>
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