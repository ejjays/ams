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
    <title>Programs • Accreditation</title>
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
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                            <span>PROGRAMS</span>
                            <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
                        </h1>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Academic Degree Management</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <input id="programSearch" type="text" placeholder="Search programs..."
                                class="pl-11 pr-4 py-3 w-80 rounded-2xl bg-slate-100 border-transparent outline-none focus:bg-white focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600/30 transition-all font-medium text-sm text-slate-700 placeholder:text-slate-400 shadow-inner" />
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <button id="programCreateBtn" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">
                            <i class="fa-solid fa-plus text-lg"></i>
                            <span>New Program</span>
                        </button>
                    </div>
                </div>
            </header>

            <section class="px-10 py-10 max-w-6xl mx-auto">
                <div class="flex items-center px-10 py-5 mb-8 bg-gradient-to-r from-indigo-900 via-purple-900 to-slate-900 rounded-3xl border border-indigo-500/30 text-[11px] font-black uppercase tracking-[0.25em] text-white shadow-xl shadow-indigo-200/20 relative overflow-hidden group">
                    <!-- Subtle Cyberpunk Accent Line -->
                    <div class="absolute bottom-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-cyan-400 to-transparent opacity-50"></div>
                    
                    <div class="w-16 mr-8 text-center flex items-center justify-center gap-2 relative z-10">
                        <i class="fa-solid fa-qrcode text-[10px] text-cyan-400 drop-shadow-[0_0_5px_rgba(34,211,238,0.5)]"></i>
                        <span class="drop-shadow-sm">Code</span>
                    </div>
                    <div class="flex-1 flex items-center gap-3 relative z-10">
                        <i class="fa-solid fa-layer-group text-[10px] text-purple-400 drop-shadow-[0_0_5px_rgba(192,38,211,0.5)]"></i>
                        <span class="drop-shadow-sm">Program Details</span>
                    </div>
                    <div class="w-24 text-center flex items-center justify-center gap-2 relative z-10">
                        <i class="fa-solid fa-sliders text-[10px] text-amber-400 drop-shadow-[0_0_5px_rgba(251,191,36,0.5)]"></i>
                        <span class="drop-shadow-sm">Actions</span>
                    </div>
                </div>
                <div id="programList" class="flex flex-col gap-4">
                    <!-- Modern Cards Injected Here -->
                </div>
            </section>
        </main>
    </div>

    <!-- Create/Edit Modal -->
    <div id="programCreateModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[500px] border-0 rounded-[1.5rem] shadow-2xl overflow-hidden p-0">
            <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-graduation-cap text-sm"></i>
                    </div>
                    <h3 id="programModalTitle" class="text-lg font-bold tracking-tight">New Program</h3>
                </div>
            </div>

            <form id="programCreateForm" class="p-6 space-y-5">
                <input type="hidden" id="program_id" name="id" />
                
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Program Code</label>
                    <input id="program_code" name="code" placeholder="e.g., BSIT"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all font-bold" required />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Full Program Name</label>
                    <input id="program_name" name="name" placeholder="e.g., Bachelor of Science in Information Technology"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all font-medium" required />
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" id="programCreateCancel" data-close="true"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                        Cancel
                    </button>
                    <button type="submit" id="programSubmitBtn"
                        class="px-8 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-blue-200 transition-all active:scale-95">
                        Save Program
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div id="archiveModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[440px] border-0 rounded-[1.5rem] shadow-2xl overflow-hidden p-0">
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4 flex items-center gap-3 text-white">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-box-archive text-sm"></i>
                </div>
                <h3 class="text-lg font-bold tracking-tight">Archive Program?</h3>
            </div>

            <div class="p-8 text-center space-y-4">
                <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-2 text-2xl border border-amber-100 shadow-inner">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="space-y-1">
                    <p class="text-slate-800 font-bold text-lg">Are you sure?</p>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Archiving <span id="archiveProgramName" class="font-black text-amber-600"></span> will hide it from the active dashboard, but the data will be preserved in history.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-center gap-3 p-6 border-t border-slate-50">
                <button type="button" id="archiveCancel" data-close="true"
                    class="flex-1 py-3 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                    No, Cancel
                </button>
                <button type="button" id="archiveConfirmBtn"
                    class="flex-1 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-amber-200 transition-all active:scale-95">
                    Yes, Archive
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-3 items-center pointer-events-none"></div>

    <script src="../app/js/dashboard.js"></script>
    <script src="../app/js/programs.js?v=<?= filemtime(__DIR__.'/../app/js/programs.js') ?>"></script>
</body>

</html>