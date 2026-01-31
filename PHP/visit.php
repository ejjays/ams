<?php
require __DIR__ . '/auth_guard.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Visit Management • Accreditation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
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
                            <span>VISITS</span>
                            <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
                        </h1>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Accreditation Visit Management</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <button id="visitExportBtn" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white border border-slate-200 text-slate-600 text-sm font-bold uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95 shadow-sm">
                            <i class="fa-regular fa-file-excel text-green-600"></i>
                            <span>Export</span>
                        </button>
                        <button id="visitAddBtn" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">
                            <i class="fa-solid fa-plus text-lg"></i>
                            <span>Add Visit</span>
                        </button>
                    </div>
                </div>
            </header>

            <section class="px-10 py-8">
                <!-- Advanced Filter Bar -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                        <div class="md:col-span-4 space-y-2">
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Search Records</label>
                            <div class="relative group">
                                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                                <input id="visitSearch" class="w-full pl-11 pr-4 py-3 bg-slate-100 border-transparent rounded-2xl text-sm font-bold text-slate-700 outline-none focus:bg-white focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600/30 transition-all shadow-inner" placeholder="Team, purpose, notes..." />
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Status</label>
                            <select id="visitStatus" class="w-full px-4 py-3 bg-slate-100 border-transparent rounded-2xl text-sm font-bold text-slate-700 outline-none focus:bg-white focus:ring-4 focus:ring-blue-600/10 transition-all shadow-inner appearance-none cursor-pointer">
                                <option value="all">All Status</option>
                                <option value="planned">Planned</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">From Date</label>
                            <input id="visitFrom" type="date" class="w-full px-4 py-3 bg-slate-100 border-transparent rounded-2xl text-sm font-bold text-slate-700 outline-none focus:bg-white focus:ring-4 focus:ring-blue-600/10 transition-all shadow-inner" />
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">To Date</label>
                            <input id="visitTo" type="date" class="w-full px-4 py-3 bg-slate-100 border-transparent rounded-2xl text-sm font-bold text-slate-700 outline-none focus:bg-white focus:ring-4 focus:ring-blue-600/10 transition-all shadow-inner" />
                        </div>
                        <div class="md:col-span-2">
                            <button id="visitApply" class="w-full py-3 bg-slate-900 hover:bg-black text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-filter"></i> Apply
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Team / Visitor</th>
                                    <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Visit Date</th>
                                    <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Purpose</th>
                                    <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Status</th>
                                    <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Notes</th>
                                    <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="visitTbody" class="divide-y divide-slate-50">
                                <!-- JS Content -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span class="text-xs font-black uppercase tracking-widest text-slate-500" id="visitResultText">0 Results Found</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button id="visitPrev" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all disabled:opacity-30 active:scale-90 shadow-sm">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                            <div class="px-4 py-2 rounded-xl bg-white border border-slate-200 shadow-sm">
                                <span id="visitPageLabel" class="text-xs font-black text-blue-700">1</span>
                            </div>
                            <button id="visitNext" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all disabled:opacity-30 active:scale-90 shadow-sm">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal -->
    <div id="visitModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[560px] border-0 rounded-[2rem] shadow-2xl overflow-hidden p-0">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                        <i class="fa-solid fa-calendar-check text-lg"></i>
                    </div>
                    <div>
                        <h3 id="visitModalTitle" class="text-xl font-black tracking-tight uppercase">Add Visit</h3>
                        <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">Schedule accreditation activity</p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors" data-close="true">
                    <i class="fa-solid fa-xmark" data-close="true"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="visitForm" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Team / Visitor</label>
                    <input name="team" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" placeholder="e.g., CHED, PAASCU Team" required />
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Date</label>
                        <input name="date" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" required />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Status</label>
                        <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all appearance-none cursor-pointer">
                            <option value="planned">Planned</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Purpose</label>
                    <input name="purpose" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" placeholder="e.g., Level III Visit" />
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Notes</label>
                    <textarea name="notes" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all resize-none" placeholder="Additional details..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" id="visitCancel" data-close="true" class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
                    <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">Save Record</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../app/js/visit.js?v=<?= filemtime(__DIR__.'/../app/js/visit.js') ?>"></script>
</body>

</html>