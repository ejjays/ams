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
    <title>Archives • Accreditation</title>
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
                            <span>ARCHIVES</span>
                            <span class="w-2 h-2 rounded-full bg-indigo-600 shadow-lg shadow-indigo-200"></span>
                        </h1>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Historical Data Repository</p>
                    </div>

                    <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl border border-slate-200/60">
                        <button id="tabDocs" onclick="switchTab('documents')" 
                            class="px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300 bg-white text-indigo-600 shadow-sm shadow-indigo-100 border border-indigo-100">
                            <i class="fa-solid fa-file mr-2"></i>Documents
                        </button>
                        <button id="tabProgs" onclick="switchTab('programs')" 
                            class="px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300 text-slate-500 hover:text-slate-700">
                            <i class="fa-solid fa-graduation-cap mr-2"></i>Programs
                        </button>
                    </div>
                </div>
            </header>

            <section class="px-10 py-10 max-w-6xl mx-auto pb-24">
                
                <!-- Documents View -->
                <div id="documentSection" class="space-y-6">
                    <div class="flex items-center px-10 py-5 bg-gradient-to-r from-indigo-900 via-purple-900 to-slate-900 rounded-3xl border border-indigo-500/30 text-[11px] font-black uppercase tracking-[0.25em] text-white shadow-xl shadow-indigo-200/20 relative overflow-hidden group">
                        <div class="absolute bottom-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-cyan-400 to-transparent opacity-50"></div>
                        <div class="flex-1 flex items-center gap-3 relative z-10">
                            <i class="fa-solid fa-file text-[10px] text-cyan-400"></i>
                            <span>Document Details</span>
                        </div>
                        <div class="w-48 text-left flex items-center gap-3 relative z-10">
                            <i class="fa-solid fa-calendar text-[10px] text-purple-400"></i>
                            <span>Date Archived</span>
                        </div>
                        <div class="w-24 text-center flex items-center justify-center gap-2 relative z-10">
                            <i class="fa-solid fa-rotate-left text-[10px] text-amber-400"></i>
                            <span>Restore</span>
                        </div>
                    </div>

                    <div id="docList" class="flex flex-col gap-4">
                        <!-- Modern Rows Injected Here -->
                    </div>
                </div>

                <!-- Programs View -->
                <div id="programSection" class="space-y-6 hidden">
                    <div class="flex items-center px-10 py-5 bg-gradient-to-r from-indigo-900 via-purple-900 to-slate-900 rounded-3xl border border-indigo-500/30 text-[11px] font-black uppercase tracking-[0.25em] text-white shadow-xl shadow-indigo-200/20 relative overflow-hidden group">
                        <div class="absolute bottom-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-cyan-400 to-transparent opacity-50"></div>
                        <div class="w-16 mr-8 text-center flex items-center justify-center gap-2 relative z-10">
                            <i class="fa-solid fa-qrcode text-[10px] text-cyan-400"></i>
                            <span>Code</span>
                        </div>
                        <div class="flex-1 flex items-center gap-3 relative z-10">
                            <i class="fa-solid fa-layer-group text-[10px] text-purple-400"></i>
                            <span>Program Details</span>
                        </div>
                        <div class="w-24 text-center flex items-center justify-center gap-2 relative z-10">
                            <i class="fa-solid fa-rotate-left text-[10px] text-amber-400"></i>
                            <span>Restore</span>
                        </div>
                    </div>

                    <div id="progList" class="flex flex-col gap-4">
                        <!-- Modern Rows Injected Here -->
                    </div>
                </div>

            </section>
        </main>
    </div>

    <!-- Restore Confirmation Modal -->
    <div id="restoreModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[440px] border-0 rounded-[1.5rem] shadow-2xl overflow-hidden p-0">
            <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center gap-3 text-white">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-box-open text-sm"></i>
                </div>
                <h3 class="text-lg font-bold tracking-tight">Restore Item?</h3>
            </div>

            <div class="p-8 text-center space-y-4">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-2 text-2xl border border-indigo-100 shadow-inner">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div class="space-y-1">
                    <p class="text-slate-800 font-bold text-lg">Bring it back?</p>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Restoring <span id="restoreItemName" class="font-black text-indigo-600"></span> will make it visible and active in the system again.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-center gap-3 p-6 border-t border-slate-50">
                <button type="button" data-close="true"
                    class="flex-1 py-3 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                    No, Keep Archived
                </button>
                <button type="button" id="restoreConfirmBtn"
                    class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    Yes, Restore
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-3 items-center pointer-events-none"></div>

    <script src="../app/js/archive.js?v=<?= filemtime(__DIR__.'/../app/js/archive.js') ?>"></script>
</body>

</html>