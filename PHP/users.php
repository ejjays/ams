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
                            <span>USERS</span>
                            <span class="w-2 h-2 rounded-full bg-cyan-500 shadow-lg shadow-cyan-200"></span>
                        </h1>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Account & Access Management</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <input id="userSearch" type="text" placeholder="Search users..."
                                class="pl-11 pr-4 py-3 w-80 rounded-2xl bg-slate-100 border-transparent outline-none focus:bg-white focus:ring-4 focus:ring-cyan-500/10 focus:border-cyan-500/30 transition-all font-medium text-sm text-slate-700 placeholder:text-slate-400 shadow-inner" />
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-cyan-500 transition-colors"></i>
                        </div>
                        <button id="userCreateBtn" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-cyan-200 transition-all active:scale-95">
                            <i class="fa-solid fa-user-plus text-lg"></i>
                            <span>Add User</span>
                        </button>
                    </div>
                </div>
            </header>

            <section class="px-10 py-10 max-w-6xl mx-auto pb-24">
                <div class="flex items-center px-10 py-5 mb-8 bg-gradient-to-r from-indigo-900 via-purple-900 to-slate-900 rounded-3xl border border-indigo-500/30 text-[11px] font-black uppercase tracking-[0.25em] text-white shadow-xl shadow-indigo-200/20 relative overflow-hidden group">
                    <div class="absolute bottom-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-cyan-400 to-transparent opacity-50"></div>
                    
                    <div class="w-16 mr-8 text-center flex items-center justify-center gap-2 relative z-10">
                        <i class="fa-solid fa-circle-user text-[10px] text-cyan-400"></i>
                        <span>Profile</span>
                    </div>
                    <div class="flex-1 flex items-center gap-3 relative z-10">
                        <i class="fa-solid fa-address-card text-[10px] text-purple-400"></i>
                        <span>User Information</span>
                    </div>
                    <div class="w-48 text-left flex items-center gap-3 relative z-10">
                        <i class="fa-solid fa-shield-halved text-[10px] text-cyan-400"></i>
                        <span>Role & Status</span>
                    </div>
                    <div class="w-24 text-center flex items-center justify-center gap-2 relative z-10">
                        <i class="fa-solid fa-sliders text-[10px] text-amber-400"></i>
                        <span>Actions</span>
                    </div>
                </div>

                <div id="userList" class="flex flex-col gap-4">
                    <!-- Modern Cards Injected Here -->
                </div>
            </section>
        </main>
    </div>

    <!-- User Modal (Create/Edit) -->
    <div id="userModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[550px] border-0 rounded-[1.5rem] shadow-2xl overflow-hidden p-0">
            <div class="bg-gradient-to-r from-cyan-600 to-blue-700 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-user-gear text-sm"></i>
                    </div>
                    <h3 id="userModalTitle" class="text-lg font-bold tracking-tight">Add New User</h3>
                </div>
            </div>

            <form id="userForm" class="p-8 space-y-6">
                <input type="hidden" id="u_id" name="id" />
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">First Name</label>
                        <input id="first_name" name="first_name" placeholder="John"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all font-bold" required />
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Last Name</label>
                        <input id="last_name" name="last_name" placeholder="Doe"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all font-bold" required />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Email Address</label>
                        <input id="email" name="email" type="email" placeholder="john@example.com"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all font-bold" required />
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Username</label>
                        <input id="username" name="username" placeholder="johndoe"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all font-bold" required />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">System Role</label>
                    <select id="role" name="role" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all font-bold appearance-none cursor-pointer">
                        <option value="admin">Administrator</option>
                        <option value="dean">Dean</option>
                        <option value="program_coordinator">Program Coordinator</option>
                        <option value="faculty" selected>Faculty</option>
                        <option value="staff">Staff</option>
                        <option value="external_accreditor">External Accreditor</option>
                    </select>
                </div>

                <div id="passwordArea" class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Password</label>
                    <input id="password" name="password" type="password" placeholder="••••••••"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all font-bold" />
                    <p id="passHint" class="text-[9px] text-slate-400 italic mt-1 hidden">Leave blank to keep current password.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="button" data-close="true"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                        Cancel
                    </button>
                    <button type="submit" id="userSubmitBtn"
                        class="px-8 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-cyan-200 transition-all active:scale-95">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[440px] border-0 rounded-[1.5rem] shadow-2xl overflow-hidden p-0">
            <div class="bg-gradient-to-r from-rose-500 to-red-700 px-6 py-4 flex items-center gap-3 text-white">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-user-minus text-sm"></i>
                </div>
                <h3 class="text-lg font-bold tracking-tight">Delete User?</h3>
            </div>

            <div class="p-8 text-center space-y-4">
                <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-2 text-2xl border border-rose-100 shadow-inner">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div class="space-y-1">
                    <p class="text-slate-800 font-bold text-lg">Are you sure?</p>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Deleting <span id="deleteUserName" class="font-black text-rose-600"></span> is permanent and cannot be undone. All access will be revoked immediately.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-center gap-3 p-6 border-t border-slate-50">
                <button type="button" data-close="true"
                    class="flex-1 py-3 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                    No, Cancel
                </button>
                <button type="button" id="deleteConfirmBtn"
                    class="flex-1 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-rose-200 transition-all active:scale-95">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-3 items-center pointer-events-none"></div>

    <script src="../app/js/dashboard.js?v=<?= filemtime(__DIR__.'/../app/js/dashboard.js') ?>"></script>
    <script src="../app/js/users.js?v=<?= filemtime(__DIR__.'/../app/js/users.js') ?>"></script>
</body>
</html>