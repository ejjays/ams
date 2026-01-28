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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../app/css/dashboard.css?v=3" />
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="flex-1 overflow-auto">
            <!-- Header: title + (Export CSV, Add Visit) only -->
            <header class="px-10 py-6 border-b bg-white">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <h1 class="text-2xl font-semibold">VISIT MANAGEMENT</h1>
                    <div class="flex items-center gap-2">
                        <button id="visitExportBtn" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-slate-800">
                            <i class="fa-regular fa-file-excel mr-2"></i>Export CSV
                        </button>
                        <button id="visitAddBtn" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
                            <i class="fa-solid fa-plus"></i>Add Visit
                        </button>
                    </div>
                </div>
            </header>

            <section class="p-10">
                <div class="bg-white rounded-xl border p-5">
                    <!-- Filters -->
                    <div class="grid grid-cols-12 gap-3 items-end">
                        <div class="col-span-12 md:col-span-4">
                            <label class="block text-sm font-medium text-slate-700">Search</label>
                            <input id="visitSearch" class="w-full border rounded-md px-3 py-2" placeholder="Team, purpose, notes..." />
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Status</label>
                            <select id="visitStatus" class="w-full border rounded-md px-3 py-2">
                                <option value="all">All</option>
                                <option value="planned">Planned</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">From</label>
                            <input id="visitFrom" type="date" class="w-full border rounded-md px-3 py-2" placeholder="mm/dd/yyyy" />
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">To</label>
                            <input id="visitTo" type="date" class="w-full border rounded-md px-3 py-2" placeholder="mm/dd/yyyy" />
                        </div>
                        <div class="col-span-6 md:col-span-2 flex gap-2">
                            <button id="visitApply" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 text-white w-full">
                                <i class="fa-solid fa-filter mr-2"></i>Apply
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-slate-500">
                                <tr class="border-b">
                                    <th class="px-4 py-2">Team / Visitor</th>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Purpose</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2">Notes</th>
                                    <th class="px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="visitTbody" class="align-top"></tbody>
                        </table>
                    </div>

                    <!-- Footer: result + pagination (Export/Add removed here) -->
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-slate-500" id="visitResultText">0 results</div>
                        <div class="flex items-center gap-2">
                            <button id="visitPrev" class="px-3 py-1.5 rounded-md bg-gray-100 text-slate-700 border disabled:opacity-50">Prev</button>
                            <span id="visitPageLabel" class="text-sm text-slate-600">1</span>
                            <button id="visitNext" class="px-3 py-1.5 rounded-md bg-gray-100 text-slate-700 border disabled:opacity-50">Next</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal -->
    <div id="visitModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>
        <div class="modal-card w-[520px]">
            <h3 id="visitModalTitle" class="text-lg font-semibold mb-3">Add Visit</h3>
            <form id="visitForm" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Team / Visitor</label>
                    <input name="team" class="w-full border rounded-md px-3 py-2" placeholder="e.g., CHED, PAASCU" required />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                        <input name="date" type="date" class="w-full border rounded-md px-3 py-2" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full border rounded-md px-3 py-2">
                            <option value="planned">Planned</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Purpose</label>
                    <input name="purpose" class="w-full border rounded-md px-3 py-2" placeholder="e.g., Visit" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="4" class="w-full border rounded-md px-3 py-2"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button id="visitCancel" class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../app/js/visit.js?v=1"></script>
</body>

</html>