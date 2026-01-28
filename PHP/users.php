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
    <title>Users • Accreditation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../app/css/dashboard.css?v=3" />
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="flex-1 overflow-auto">
            <!-- Header WITH controls on the same row -->
            <header class="px-10 py-6 border-b bg-white">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-4">
                        <button class="sidebar-toggle md:hidden p-2 rounded bg-gray-200 hover:bg-gray-300">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <h1 class="text-2xl font-semibold">USERS</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input id="userSearch" type="text" placeholder="Search user"
                                class="pl-10 pr-3 py-2 w-64 rounded-lg border outline-none focus:ring-2 focus:ring-blue-600" />
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <button id="userCreateBtn"
                            class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
                            <i class="fa-solid fa-plus mr-1"></i> Create
                        </button>
                    </div>
                </div>
            </header>

            <!-- Users table -->
            <section class="px-6 py-6">
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="overflow-x-auto">

                        <div class="rounded-t-lg bg-slate-50">
                            <div class="grid grid-cols-12 gap-2 text-left text-slate-600 text-base md:text-lg px-5 py-3">
                                <div class="col-span-3 font-semibold md:text-lg">Name</div>
                                <div class="col-span-3 font-semibold md:text-lg">Email</div>
                                <div class="col-span-2 font-semibold md:text-lg">Username</div>
                                <div class="col-span-2 font-semibold md:text-lg">Role</div>
                                <div class="col-span-2 font-semibold md:text-lg text-right">Actions</div>
                            </div>
                        </div>
                        <div id="usersTableBody" class="mt-6"></div>

                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Create/Edit Modal -->
    <div id="userModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>
        <div class="modal-card w-[560px]">
            <h3 id="userModalTitle" class="text-lg font-semibold mb-3">Create User</h3>
            <form id="userForm" class="space-y-3">
                <input type="hidden" name="id" id="id" />
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">First name</label>
                        <input id="first_name" name="first_name" class="w-full border rounded-md px-3 py-2" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Last name</label>
                        <input id="last_name" name="last_name" class="w-full border rounded-md px-3 py-2" required />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input id="email" name="email" type="email" class="w-full border rounded-md px-3 py-2" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                        <input id="username" name="username" class="w-full border rounded-md px-3 py-2" required />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <select id="role" name="role" class="w-full border rounded-md px-3 py-2">
                            <option value="admin">Admin</option>
                            <option value="dean">Dean</option>
                            <option value="program_coordinator">Coordinator</option>
                            <option value="faculty" selected>Faculty</option>
                            <option value="staff">Staff</option>
                            <option value="external_accreditor">External Accreditor</option>
                        </select>
                    </div>
                    <!-- Password group: visible ONLY on Create -->
                    <div class="flex items-center gap-2" id="passwordGroup">
                        <div class="w-full">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Password <span class="text-slate-400">(required on create)</span>
                            </label>
                            <input id="password" name="password" type="password" class="w-full border rounded-md px-3 py-2" />
                        </div>
                    </div>
                </div>
                <div class="flex justify-between pt-2">
                    <button type="button" id="userCancelBtn" class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete confirm -->
    <div id="userDeleteModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>
        <div class="modal-card w-[420px]">
            <h3 class="text-lg font-semibold mb-3">Delete user</h3>
            <p class="text-sm text-slate-600">
                Are you sure you want to delete <span id="delName" class="font-semibold"></span>?
            </p>
            <input type="hidden" id="delId" />
            <div class="flex justify-end gap-2 mt-4">
                <button id="userDeleteCancel" class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                <button id="userDeleteConfirm" class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white">Delete</button>
            </div>
        </div>
    </div>

    <script src="../app/js/users.js?v=3"></script>
</body>

</html>