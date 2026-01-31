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
    <title>Documents • Accreditation</title>
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
            <header class="px-10 py-8 border-b bg-white/80 backdrop-blur-md sticky top-0 z-30">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="space-y-1">
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                            <span>DOCUMENTS</span>
                            <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
                        </h1>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Accreditation Evidence Repository</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <input id="docSearch" type="text" placeholder="Search evidence repository..."
                                class="pl-11 pr-4 py-3 w-80 rounded-2xl bg-slate-100 border-transparent outline-none focus:bg-white focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600/30 transition-all font-medium text-sm text-slate-700 placeholder:text-slate-400 shadow-inner" />
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <button id="docUploadOpen" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">
                            <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                            <span>Upload Document</span>
                        </button>
                    </div>
                </div>
            </header>

            <div class="px-10 mt-6">
                <div class="inline-flex p-1 bg-slate-200/50 rounded-2xl">
                    <button id="tabMine" class="px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 bg-white text-blue-700 shadow-sm">
                        Your uploads
                    </button>
                    <button id="tabShared" class="px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 text-slate-500 hover:text-slate-700">
                        Shared
                    </button>
                </div>
            </div>

            <section class="px-10 pt-4 pb-24">
                <div id="docList" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                </div>
            </section>
        </main>
    </div>

    <div id="docModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[560px] border-0 rounded-[1.5rem] shadow-2xl overflow-hidden p-0">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-file-arrow-up text-sm"></i>
                    </div>
                    <h3 id="docModalTitle" class="text-lg font-bold tracking-tight">Upload document</h3>
                </div>
            </div>

            <!-- Modal Body -->
            <form id="docForm" class="p-6 space-y-5" enctype="multipart/form-data">
                <input type="hidden" name="id" id="doc_id" />
                
                <div class="space-y-1.5">
                    <div class="flex justify-between items-end">
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Document Title</label>
                        <span id="titleCount" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">0 / 60</span>
                    </div>
                    <div class="relative">
                        <input id="title" name="title" placeholder="e.g., Annual Research Report" maxlength="60"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all font-medium" required />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Comment / Description</label>
                    <textarea id="comment" name="comment" rows="2" placeholder="Optional notes about this file..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all font-medium resize-none"></textarea>
                </div>

                <div id="fileRow" class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Select File</label>
                    <div class="group relative">
                        <input id="file" name="file" type="file" 
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:tracking-widest file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer bg-slate-50 border border-dashed border-slate-300 rounded-xl p-2 group-hover:border-blue-400" />
                    </div>
                    <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-2 ml-1">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>PDF, DOCX, XLSX, TXT, JPG, PNG (Max 10MB)</span>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" id="docCancel" data-close="true"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                        Cancel
                    </button>
                    <button type="submit" id="docSubmitBtn"
                        class="px-8 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-blue-200 transition-all active:scale-95">
                        Save Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="shareModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[480px] border-0 rounded-[1.5rem] shadow-2xl overflow-hidden p-0">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-share-nodes text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold tracking-tight">Share Document</h3>
                </div>
            </div>

            <!-- Modal Body -->
            <form id="shareForm" class="p-6 space-y-5">
                <input type="hidden" id="share_doc_id" />
                
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Target User ID</label>
                    <div class="relative">
                        <input id="share_user_id" type="number" min="1" placeholder="Enter recipient's ID"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 transition-all font-bold" required />
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 ml-1">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Sharing allows other users to view and download this document.
                    </p>
                </div>

                <!-- Action Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" id="shareCancel" data-close="true"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-8 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-indigo-200 transition-all active:scale-95">
                        Confirm Share
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="reviewModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[900px] max-w-[95vw] border-0 rounded-[1.5rem] shadow-2xl overflow-hidden p-0 flex flex-col" style="max-height: 90vh;">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4 flex items-center justify-between text-white flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-star text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold tracking-tight">Review Document</h3>
                </div>
            </div>

            <div class="p-6 flex-grow overflow-y-auto space-y-6">
                <!-- Preview Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Document Preview</label>
                        <button id="reviewTogglePreview" type="button"
                            class="text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 transition-all">
                            <i class="fa-regular fa-eye"></i> <span>Toggle View</span>
                        </button>
                    </div>
                    <div id="reviewPreviewWrap" class="hidden border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
                        <iframe id="reviewPreviewFrame" class="w-full" style="height:50vh;" src=""></iframe>
                    </div>
                </div>

                <!-- Feedback Form -->
                <form id="reviewForm" class="grid grid-cols-1 md:grid-cols-12 gap-6 pt-4 border-t border-slate-100">
                    <input type="hidden" id="review_doc_id" />
                    <input type="hidden" id="review_stored_name" />
                    <input type="hidden" id="review_file_ext" />

                    <div class="md:col-span-4 space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Overall Rating</label>
                        <div class="relative">
                            <input id="review_rating" type="number" min="1" max="5" required placeholder="1-5"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all font-bold text-center text-lg" />
                            <div class="flex justify-center gap-1.5 mt-3">
                                <i class="fa-solid fa-star text-base text-amber-400"></i>
                                <i class="fa-solid fa-star text-base text-amber-400"></i>
                                <i class="fa-solid fa-star text-base text-amber-400"></i>
                                <i class="fa-solid fa-star text-base text-amber-400"></i>
                                <i class="fa-solid fa-star text-base text-amber-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-8 space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Assessment Comment</label>
                        <textarea id="review_comment" rows="4" placeholder="Provide your professional feedback..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all font-medium resize-none"></textarea>
                    </div>
                </form>
            </div>

            <!-- Action Footer -->
            <div class="flex items-center justify-end gap-3 p-6 border-t border-slate-100 flex-shrink-0">
                <button type="button" id="reviewCancel" data-close="true"
                    class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                    Cancel
                </button>
                <button type="submit" form="reviewForm"
                    class="px-8 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-amber-200 transition-all active:scale-95">
                    Submit Review
                </button>
            </div>
        </div>
    </div>
    <div id="aiInsightModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/40 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[640px] border-0 bg-white/95 shadow-2xl overflow-hidden rounded-[2rem]">
            <div class="bg-indigo-600 px-8 py-5 flex items-center rounded-lg justify-between">
                <div class="flex items-center gap-3 text-white">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-wand-magic-sparkles text-sm animate-pulse"></i>
                    </div>
                    <h3 class="text-lg font-bold tracking-tight uppercase rounded-lg">AI Document Analysis</h3>
                </div>
                <button id="aiInsightClose" class="w-8 h-8 rounded-full hover:bg-white/10 text-white transition-colors" data-close="true">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="p-10">
                <div id="aiInsightContent" class="text-slate-700 text-xl leading-relaxed font-medium italic border-l-4 border-indigo-500 pl-8 mb-8 min-h-[150px] max-h-[400px] overflow-y-auto">
                    <!-- AI text -->
                </div>
                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <button type="button" class="px-8 py-3 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95" data-close="true">
                        Understood
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../app/js/documents.js?v=<?= filemtime(__DIR__.'/../app/js/documents.js') ?>"></script>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-3 items-center pointer-events-none"></div>
</body>

</html>