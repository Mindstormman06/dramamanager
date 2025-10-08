<?php
include '../header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Analyze Script | <?=htmlspecialchars($config['site_title'])?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <h1 class="text-3xl font-bold text-[<?= htmlspecialchars($config['text_colour']) ?>] mb-6">📜 Analyze Script</h1>

    <form action="/backend/scripts/process_script.php" method="POST" enctype="multipart/form-data"
          class="bg-white p-6 rounded-lg shadow border space-y-6">

      <div>
        <label for="character_list" class="block font-medium mb-1">Character List (comma-separated)</label>
        <input type="text" name="character_list" id="character_list"
              placeholder="eg. Antony, Brutus, Cassius"
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($config['highlight_colour']) ?>]"
              required /> <!-- Added required attribute until auto-detect fixed! -->
      </div>

      <div>
        <label for="script_pdf" class="block font-medium mb-1">Upload Script PDF</label>
        <input type="file" name="script_pdf" id="script_pdf" accept="application/pdf" required
               class="w-full border rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($config['highlight_colour']) ?>]" />
      </div>

      <div class="flex justify-end">
        <button type="submit" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] text-white px-6 py-2 rounded hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] transition">
          Analyze Script
        </button>
      </div>
    </form>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>
