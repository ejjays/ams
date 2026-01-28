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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../app/css/dashboard.css?v=4" />
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="flex-1 overflow-auto">
            <header class="px-10 py-6 border-b bg-white">
                <h1 class="text-2xl font-semibold"><i class="fa-solid fa-box-archive mr-2 text-gray-500"></i>Archives</h1>
            </header>

            <section class="px-10 pt-6">

                <div class="flex space-x-4 mb-4 border-b border-gray-300">
                    <button id="tabDocs" class="pb-2 px-4 font-medium border-b-2 border-blue-600 text-blue-600 transition" onclick="switchTab('documents')">
                        <i class="fa-solid fa-file mr-2"></i>Documents
                    </button>
                    <button id="tabProgs" class="pb-2 px-4 font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition" onclick="switchTab('programs')">
                        <i class="fa-solid fa-graduation-cap mr-2"></i>Programs
                    </button>
                </div>

                <div id="documentSection" class="bg-white rounded-lg shadow-md p-6">
                    <div class="mb-4 p-3 bg-blue-50 text-blue-800 rounded text-sm flex items-center">
                        <i class="fa-solid fa-clock-rotate-left mr-2"></i>
                        <span>Documents older than 5 years are automatically moved here.</span>
                    </div>
                    <table class="w-full">
                        <thead class="border-b bg-gray-50">
                            <tr>
                                <th class="p-3 text-left">Title</th>
                                <th class="p-3 text-left">Original Filename</th>
                                <th class="p-3 text-left">Owner</th>
                                <th class="p-3 text-left">Date Archived</th>
                                <th class="p-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="docList"></tbody>
                    </table>
                </div>

                <div id="programSection" class="bg-white rounded-lg shadow-md p-6 hidden">
                    <div class="mb-4 p-3 bg-amber-50 text-amber-800 rounded text-sm flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                        <span>These programs are hidden from the active list but preserved for history.</span>
                    </div>
                    <table class="w-full">
                        <thead class="border-b bg-gray-50">
                            <tr>
                                <th class="p-3 text-left">Code</th>
                                <th class="p-3 text-left">Program Name</th>
                                <th class="p-3 text-left">Description</th>
                                <th class="p-3 text-left">Date Created</th>
                                <th class="p-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="progList"></tbody>
                    </table>
                </div>

            </section>
        </main>
    </div>
    <script src="../app/js/archive.js?v=4"></script>
</body>

</html>