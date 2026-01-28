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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../app/css/dashboard.css?v=3" />
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="flex-1 overflow-auto">
            <header class="px-10 py-6 border-b bg-white flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Audit Trail</h1>
                </div>

            </header>

            <section class="px-10 pt-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold border-b">
                            <tr>
                                <th class="p-4">Date & Time</th>
                                <th class="p-4">User</th>
                                <th class="p-4">Action</th>
                                <th class="p-4">Module</th>
                                <th class="p-4">Details</th>
                            </tr>
                        </thead>
                        <tbody id="logList" class="text-sm divide-y divide-gray-100">
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-400">Loading logs...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        async function loadLogs() {
            const tbody = document.getElementById('logList');
            try {
                const res = await fetch('audit_trail_api.php?action=list');
                const json = await res.json();

                if (!json.ok) throw new Error(json.error || 'Failed to load logs');

                tbody.innerHTML = '';
                if (json.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center">No activity recorded yet.</td></tr>';
                    return;
                }

                json.data.forEach(log => {
                    // DEFAULT COLOR
                    let color = 'text-gray-600';

                    // CONDITIONAL COLORS
                    // Changed to ORANGE to match your Program page
                    if (log.action === 'ARCHIVE') color = 'text-orange-600 font-bold';

                    if (log.action === 'RESTORE') color = 'text-green-600 font-bold';
                    if (log.action === 'CREATE') color = 'text-blue-600 font-bold';
                    if (log.action === 'UPDATE') color = 'text-indigo-600 font-bold'; // Added for edits
                    if (log.action === 'LOGIN') color = 'text-gray-800 font-medium';

                    const row = `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 text-gray-500">${new Date(log.created_at).toLocaleString()}</td>
                            <td class="p-4 font-medium text-gray-800">
                                ${log.user_name || 'Unknown'} <span class="text-xs text-gray-400 ml-1">(${log.role || 'N/A'})</span>
                            </td>
                            <td class="p-4 ${color}">${log.action}</td>
                            <td class="p-4 text-gray-500 text-xs uppercase tracking-wide">${log.module}</td>
                            <td class="p-4 text-gray-600">${log.description}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-red-500">Error: ${e.message}</td></tr>`;
            }
        }
        document.addEventListener('DOMContentLoaded', loadLogs);
    </script>
</body>

</html>