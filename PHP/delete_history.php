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
    <title>Delete History • Accreditation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../app/css/dashboard.css?v=3" />
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="flex-1 overflow-auto">
            <header class="px-10 py-6 border-b bg-white">
                <h1 class="text-2xl font-semibold">Document Delete History</h1>
            </header>

            <section class="px-10 pt-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr>
                                <th class="p-3 text-left">Title</th>
                                <th class="p-3 text-left">Original Filename</th>
                                <th class="p-3 text-left">Deleted By</th>
                                <th class="p-3 text-left">Date Deleted</th>
                                <th class="p-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="historyList">
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="../app/js/delete_history.js"></script>
</body>

</html>