<?php
require __DIR__ . '/auth_guard.php';
$current = 'audit_trail.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Audit Trail • Accreditation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../app/css/dashboard.css?v=<?= filemtime(__DIR__.'/../app/css/dashboard.css') ?>" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="px-10 py-8 border-b bg-white/80 backdrop-blur-md sticky top-0 z-30">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="space-y-1">
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3 text-uppercase">
                            <span>AUDIT TRAIL</span>
                            <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
                        </h1>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">System Activity Logs</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <input id="logSearch" type="text" placeholder="Search logs..."
                                class="pl-11 pr-4 py-3 w-80 rounded-2xl bg-slate-100 border-transparent outline-none focus:bg-white focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600/30 transition-all font-medium text-sm text-slate-700 placeholder:text-slate-400 shadow-inner" />
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                    </div>
                </div>
            </header>

            <section class="px-10 py-10 max-w-6xl mx-auto pb-24">
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                    <!-- Professional Table Header (Matches Visits) -->
                    <div class="grid grid-cols-12 gap-6 px-10 py-6 bg-indigo-50/50 border-b border-indigo-100/50 text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 relative overflow-hidden group">
                        <div class="col-span-2 text-center flex items-center justify-center gap-3">
                            <i class="fa-solid fa-clock text-sm text-indigo-400"></i>
                            <span>Time</span>
                        </div>
                        <div class="col-span-7 flex items-center gap-3">
                            <i class="fa-solid fa-bolt-lightning text-sm text-violet-400"></i>
                            <span>Activity Details</span>
                        </div>
                        <div class="col-span-3 text-right pr-4 flex items-center justify-end gap-3">
                            <i class="fa-solid fa-user-shield text-sm text-sky-400"></i>
                            <span>User</span>
                        </div>
                    </div>

                    <div id="logList" class="divide-y divide-slate-50">
                        <!-- Enhanced Rows Injected Here -->
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-3 items-center pointer-events-none"></div>

    <script src="../app/js/dashboard.js?v=<?= filemtime(__DIR__.'/../app/js/dashboard.js') ?>"></script>
    <script src="../app/js/audit_trail.js?v=<?= @filemtime(__DIR__.'/../app/js/audit_trail.js') ?: time() ?>"></script>
</body>
</html>