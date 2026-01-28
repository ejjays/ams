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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../app/css/dashboard.css?v=3" />
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="flex-1 overflow-auto">
            <header class="px-10 py-6 border-b bg-white">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <h1 class="text-2xl font-semibold">DOCUMENTS</h1>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input id="docSearch" type="text" placeholder="Search document"
                                class="pl-10 pr-3 py-2 w-72 rounded-lg border outline-none focus:ring-2 focus:ring-blue-600" />
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <button id="docUploadOpen" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow transition-all active:scale-95">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Upload Document</span>
                        </button>
                    </div>
                </div>
            </header>

            <div class="px-10">
                <div class="flex items-center gap-6 border-b">
                    <button id="tabMine" class="py-2 -mb-px border-b-2 border-blue-700 text-blue-700 font-medium">Your uploads</button>
                    <button id="tabShared" class="py-2 -mb-px border-b-2 border-transparent text-slate-500 hover:text-slate-700">Shared</button>
                </div>
            </div>

            <section class="px-10 pt-4">
                <div id="docList" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                </div>
            </section>
        </main>
    </div>

    <div id="docModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>
        <div class="modal-card w-[560px]">
            <h3 id="docModalTitle" class="text-lg font-semibold mb-3">Upload document</h3>
            <form id="docForm" class="space-y-3" enctype="multipart/form-data">
                <input type="hidden" name="id" id="doc_id" />
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                    <input id="title" name="title" class="w-full border rounded-md px-3 py-2" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Comment</label>
                    <input id="comment" name="comment" class="w-full border rounded-md px-3 py-2" placeholder="Optional" />
                </div>
                <div id="fileRow">
                    <label class="block text-sm font-medium text-slate-700 mb-1">File</label>
                    <input id="file" name="file" type="file" class="w-full border rounded-md px-3 py-2" />
                    <p class="text-xs text-slate-500 mt-1">Allowed: pdf, docx, xlsx, pptx, txt, jpg, png (max 10MB)</p>
                </div>
                <div class="flex justify-between pt-2">
                    <button type="button" id="docCancel" class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="shareModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>
        <div class="modal-card w-[480px]">
            <h3 class="text-lg font-semibold mb-3">Share document</h3>
            <form id="shareForm" class="space-y-3">
                <input type="hidden" id="share_doc_id" />
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Share with (user ID)</label>
                    <input id="share_user_id" type="number" min="1" class="w-full border rounded-md px-3 py-2" required />
                </div>
                <div class="flex justify-between pt-2">
                    <button type="button" id="shareCancel" class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Share</button>
                </div>
            </form>
        </div>
    </div>

    <div id="reviewModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>

        <div class="modal-card w-[900px] max-w-[98vw] flex flex-col" style="max-height: 90vh;">

            <div class="flex-shrink-0">
                <h3 class="text-lg font-semibold mb-3">Review document</h3>
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm text-slate-600">
                        You can preview the document below.
                    </div>
                    <div class="flex gap-2">
                        <button id="reviewTogglePreview" type="button"
                            class="px-3 py-1.5 rounded-lg bg-gray-200 hover:bg-gray-300 text-slate-700">
                            <i class="fa-regular fa-eye mr-1"></i> View document
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-grow overflow-y-auto -mx-6 px-6 py-4 border-y">
                <div id="reviewPreviewWrap" class="hidden mb-4 border rounded-lg overflow-hidden bg-slate-50">
                    <iframe id="reviewPreviewFrame" class="w-full" style="height:60vh;" src=""></iframe>
                </div>

                <form id="reviewForm" class="space-y-3">
                    <input type="hidden" id="review_doc_id" />
                    <input type="hidden" id="review_stored_name" />
                    <input type="hidden" id="review_file_ext" />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Rating</label>
                        <div class="flex items-center gap-2">
                            <input id="review_rating" type="number" min="1" max="5" required
                                class="w-24 border rounded-md px-3 py-2" />
                            <span class="text-sm text-slate-500">1 (lowest) – 5 (highest)</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Comment</label>
                        <textarea id="review_comment" rows="4" class="w-full border rounded-md px-3 py-2"
                            placeholder="Optional short feedback..."></textarea>
                    </div>
                </form>
            </div>

            <div class="flex-shrink-0 flex justify-between pt-4 mt-4">
                <button type="button" id="reviewCancel" class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                <button type="submit" form="reviewForm" class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Submit review</button>
            </div>
        </div>
    </div>
    <script src="../app/js/documents.js?v=7"></script>
</body>

</html>