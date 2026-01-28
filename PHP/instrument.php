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
  <title>Instrument • Rates</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../app/css/dashboard.css?v=1" />

  <style>
    /* Instrument page: fixed, slightly longer chip width (like before) */
    #instrumentList .program-chip {
      width: 100%;
    }

    /* Gawing full width ang bawat card */

    /* adjust 340-400px as needed */
    #instrumentList .program-chip span {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  </style>
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="flex min-h-screen">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-auto">
      <!-- Header: title + Verify Rates (ONLY) -->
      <header class="px-10 py-6 border-b bg-white">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <h1 class="text-2xl font-semibold">INSTRUMENT</h1>

          <div class="flex items-center gap-3">
            <button id="openCreateInstrument"
              class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
              <i class="fa-solid fa-plus mr-1"></i> Create Instrument
            </button>

            <button id="instrumentEditToggleBtn"
              class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow"
              title="Toggle edit mode">
              <i class="fa-solid fa-pen"></i></button>
          </div>
        </div>
      </header>
      <section class="px-6 py-6 mt-6">
        <div id="instrumentList" class="flex flex-col gap-4">
          <!-- items load here -->
        </div>
      </section>

      <!-- Create/Update Instrument Modal -->
      <div id="instrumentModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>
        <div class="modal-card">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Instrument</h3>
            <button id="instrumentCloseX" class="text-gray-500 hover:text-gray-700" title="Close">
              <i class="fa-solid fa-xmark"></i></button>
          </div>
          <form id="instrumentCreateForm" class="space-y-3">
            <input type="hidden" id="instrument_id" name="id" />
            <div>
              <label for="instrument_name" class="block text-sm font-medium text-slate-700 mb-1">Instrument</label>
              <input id="instrument_name" name="name" class="w-full border rounded-md px-3 py-2" placeholder="Instrument" required />
            </div>
            <div class="flex justify-between pt-2">
              <button type="button" id="instrumentCreateCancel"
                class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
              <button type="submit" id="instrumentCreateSubmit"
                class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Save</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
  <script src="../app/js/instruments.js?v=3"></script>
</body>

</html>