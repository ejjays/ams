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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../app/css/dashboard.css?v=2" />
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="px-10 py-6 border-b bg-white">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <h1 class="text-2xl font-semibold">PROGRAMS</h1>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input id="programSearch" type="text" placeholder="Search program"
                                class="pl-10 pr-3 py-2 w-64 rounded-lg border outline-none focus:ring-2 focus:ring-blue-600" />
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>

                        <button id="programCreateBtn"
                            class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
                            <i class="fa-solid fa-plus mr-1"></i> Create
                        </button>
                    </div>
                </div>
            </header>

            <section class="px-6 py-6">
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title"><i class="fa-solid fa-table"></i>Programs</div>
                    </div>
                    <div class="panel-body">
                        <table class="min-w-full text-base border-separate border-spacing-y-3">
                            <thead class="text-left text-slate-500">
                                <tr class="border-b">
                                    <th class="px-4 py-2">Code</th>
                                    <th class="px-4 py-2">Name</th>
                                    <th class="px-4 py-2">Description</th>
                                    <th class="px-4 py-2">Created</th>
                                    <th class="px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="programTbody" class="align-top"></tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Create/Edit Modal (CODE + NAME only) -->
    <div id="programCreateModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>
        <div class="modal-card">
            <h3 id="programModalTitle" class="text-lg font-semibold mb-3">Create Program</h3>
            <form id="programCreateForm" class="space-y-3">
                <input type="hidden" id="program_id" name="id" />
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="program_code">Code</label>
                    <input id="program_code" name="code" class="w-full border rounded-md px-3 py-2"
                        placeholder="e.g., BSIT" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="program_name">Name</label>
                    <input id="program_name" name="name" class="w-full border rounded-md px-3 py-2"
                        placeholder="e.g., Bachelor of Science in Information Technology" required />
                </div>
                <div class="flex justify-between pt-2">
                    <button type="button" id="programCreateCancel"
                        class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../app/js/dashboard.js"></script>
    <script src="../app/js/programs.js?v=9"></script>
</body>

</html>