<?php
require __DIR__ . '/auth_guard.php';
$current = basename($_SERVER['PHP_SELF']);
function active($page, $current) { return $current === $page ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">

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
    <title>Accreditation • Accreditation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../app/css/dashboard.css?v=2" />

</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex min-h-screen">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-auto">
        <!-- Header with title -->
        <header class="px-10 py-6 border-b bg-white">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold"><i class="fa-solid fa-certificate mr-2"></i>Accreditation</h1>
            </div>
        </header>

        <!-- Content -->
        <section class="px-10 py-8">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <i class="fa-regular fa-circle-question"></i> Placeholder
                    </div>
                </div>
                <div class="panel-body">
                    <p class="text-slate-600">This page is a placeholder. Functionality coming soon.</p>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="../app/js/dashboard.js"></script>
<script src="../app/js/accreditation.js?v=1"></script>
</body>
</html>