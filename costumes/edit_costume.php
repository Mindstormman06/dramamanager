<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';
if ($_SESSION['role'] != 'teacher' && !in_array('costumes', $_SESSION['student_roles'])) die('You are not authorized to access this page.');


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch costume
$stmt = $pdo->prepare("SELECT * FROM costumes WHERE id = ?");
$stmt->execute([$id]);
$costume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$costume) {
    die("Costume not found.");
}

// Fetch categories and shows
$categories = $pdo->query("SELECT id, name FROM costumecategories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$shows = $pdo->query("SELECT id, title, semester, year FROM shows ORDER BY year DESC, semester DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch linked shows
$linkStmt = $pdo->prepare("SELECT show_id FROM showcostumes WHERE costume_id = ?");
$linkStmt->execute([$id]);
$linkedShowIds = array_column($linkStmt->fetchAll(PDO::FETCH_ASSOC), 'show_id');

// Fetch linked characters
$linkedCharacterStmt = $pdo->prepare("SELECT character_id FROM costumecharacters WHERE costume_id = ?");
$linkedCharacterStmt->execute([$id]);
$linkedCharacterIds = array_column($linkedCharacterStmt->fetchAll(PDO::FETCH_ASSOC), 'character_id');

// Fetch all characters for the dropdown
$characters = $pdo->query("SELECT id, stage_name FROM characters ORDER BY stage_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $decade = trim($_POST['decade'] ?? '') ?: null; // Convert blank to NULL
    $style = trim($_POST['style'] ?? '') ?: null;  // Convert blank to NULL
    $location = trim($_POST['location'] ?? '') ?: null; // Convert blank to NULL
    $condition = trim($_POST['itemcondition'] ?? '') ?: null; // Convert blank to NULL
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $show_ids = $_POST['show_ids'] ?? [];
    $character_ids = $_POST['character_ids'] ?? []; // New field for selected characters

    if ($name === '') {
        die("Name is required.");
    }

    // Handle optional photo update
    $photo_url = $costume['photo_url'];
    if (!empty($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $filename = uniqid('costume_', true) . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photo_url = '/uploads/' . $filename;
        }
    }

    // Update costume
    $stmt = $pdo->prepare("
        UPDATE costumes SET
            name = ?, photo_url = ?, decade = ?, style = ?, location = ?, itemcondition = ?, category_id = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $photo_url, $decade, $style, $location, $condition, $category_id, $id]);

    // Update show links
    $pdo->prepare("DELETE FROM showcostumes WHERE costume_id = ?")->execute([$id]);
    if (!empty($show_ids)) {
        $stmt = $pdo->prepare("INSERT INTO showcostumes (show_id, costume_id) VALUES (?, ?)");
        foreach ($show_ids as $show_id) {
            $stmt->execute([$show_id, $id]);
        }
    }

    // Update character links
    $pdo->prepare("DELETE FROM costumecharacters WHERE costume_id = ?")->execute([$id]);
    if (!empty($character_ids)) {
        $stmt = $pdo->prepare("INSERT INTO costumecharacters (costume_id, character_id) VALUES (?, ?)");
        foreach ($character_ids as $character_id) {
            $stmt->execute([$id, $character_id]);
        }
    }

    header("Location: costumes.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Costume | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function openCategoryPopup() {
      document.getElementById('category-popup').classList.remove('hidden');
    }

    function closeCategoryPopup() {
      document.getElementById('category-popup').classList.add('hidden');
    }

    function addCategory() {
      const categoryName = document.getElementById('new-category-name').value.trim();
      if (categoryName === '') {
        alert('Category name cannot be empty.');
        return;
      }

      // Send AJAX request to add the category
      fetch('../backend/costumes/add_category.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: categoryName })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Add the new category to the dropdown
          const categoryDropdown = document.getElementById('category_id');
          const newOption = document.createElement('option');
          newOption.value = data.id;
          newOption.textContent = categoryName;
          categoryDropdown.appendChild(newOption);

          // Close the popup and clear the input
          closeCategoryPopup();
          document.getElementById('new-category-name').value = '';
        } else {
          alert('Failed to add category: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the category.');
      });
    }
  </script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <h1 class="text-3xl font-bold text-[#7B1E3B] mb-6">Edit Costume</h1>
    <a href="costumes.php" class="text-blue-600 hover:underline mb-4">← Back to Costume List</a>

    <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label for="name" class="block font-medium mb-1">Name *</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($costume['name']) ?>" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
      </div>

      <div>
        <label for="photo" class="block font-medium mb-1">Photo</label>
        <?php if ($costume['photo_url']): ?>
          <img src="../<?= htmlspecialchars($costume['photo_url']) ?>" alt="Current photo" class="h-24 mb-2 rounded">
        <?php endif; ?>
        <input type="file" name="photo" id="photo" accept="image/*" class="w-full border rounded px-3 py-2">
      </div>

      <div>
        <label for="category_id" class="block font-medium mb-1">Category</label>
        <div class="flex items-center gap-4">
          <select name="category_id" id="category_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= $category['id'] ?>" <?= $costume['category_id'] == $category['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="button" onclick="openCategoryPopup()" class="bg-[#7B1E3B] text-white px-6 py-2 rounded hover:bg-[#9B3454] transition">
            +Category
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="decade" class="block font-medium mb-1">Era</label>
          <select name="decade" id="decade" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
            <option value="">-- Select Era --</option>
            <option value="Ancient" <?= $costume['decade'] === 'Ancient' ? 'selected' : '' ?>>Ancient</option>
            <option value="Medieval" <?= $costume['decade'] === 'Medieval' ? 'selected' : '' ?>>Medieval</option>
            <option value="Renaissance" <?= $costume['decade'] === 'Renaissance' ? 'selected' : '' ?>>Renaissance</option>
            <option value="Victorian" <?= $costume['decade'] === 'Victorian' ? 'selected' : '' ?>>Victorian</option>
            <option value="1920s" <?= $costume['decade'] === '1920s' ? 'selected' : '' ?>>1920s</option>
            <option value="1930s" <?= $costume['decade'] === '1930s' ? 'selected' : '' ?>>1930s</option>
            <option value="1940s" <?= $costume['decade'] === '1940s' ? 'selected' : '' ?>>1940s</option>
            <option value="1950s" <?= $costume['decade'] === '1950s' ? 'selected' : '' ?>>1950s</option>
            <option value="1960s" <?= $costume['decade'] === '1960s' ? 'selected' : '' ?>>1960s</option>
            <option value="1970s" <?= $costume['decade'] === '1970s' ? 'selected' : '' ?>>1970s</option>
            <option value="1980s" <?= $costume['decade'] === '1980s' ? 'selected' : '' ?>>1980s</option>
            <option value="1990s" <?= $costume['decade'] === '1990s' ? 'selected' : '' ?>>1990s</option>
            <option value="Modern" <?= $costume['decade'] === 'Modern' ? 'selected' : '' ?>>Modern</option>
            <option value="Future" <?= $costume['decade'] === 'Future' ? 'selected' : '' ?>>Future</option>
          </select>
        </div>
        <div>
          <label for="style" class="block font-medium mb-1">Style</label>
          <input type="text" name="style" id="style" value="<?= htmlspecialchars($costume['style']) ?>" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="location" class="block font-medium mb-1">Location</label>
          <input type="text" name="location" id="location" value="<?= htmlspecialchars($costume['location']) ?>" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
        </div>

        <div>
          <label for="itemcondition" class="block font-medium mb-1">Condition</label>
          <select name="itemcondition" id="itemcondition" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
            <option value="">-- Select Condition --</option>
            <option value="A" <?= $costume['itemcondition'] === 'A' ? 'selected' : '' ?>>Excellent</option>
            <option value="B" <?= $costume['itemcondition'] === 'B' ? 'selected' : '' ?>>Good</option>
            <option value="C" <?= $costume['itemcondition'] === 'C' ? 'selected' : '' ?>>Fair</option>
            <option value="D" <?= $costume['itemcondition'] === 'D' ? 'selected' : '' ?>>Poor</option>
            <option value="R" <?= $costume['itemcondition'] === 'R' ? 'selected' : '' ?>>Needs Repair</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block font-medium mb-1">Used in Shows</label>
        <div class="relative">
          <select id="showDropdown" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
            <option value="">-- Select a Show --</option>
            <?php foreach ($shows as $show): ?>
              <?php if (!in_array($show['id'], $linkedShowIds)): ?>
                <option value="<?= $show['id'] ?>"><?= htmlspecialchars($show['title'] . " ({$show['semester']} {$show['year']})") ?></option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="selectedShows" class="mt-4 space-y-2">
          <?php foreach ($linkedShowIds as $showId): ?>
            <div class="flex items-center gap-2" id="show-<?= $showId ?>">
              <input type="hidden" name="show_ids[]" value="<?= $showId ?>">
              <span class="text-gray-700"><?= htmlspecialchars($shows[array_search($showId, array_column($shows, 'id'))]['title']) ?></span>
              <button type="button" class="text-red-500 hover:text-red-700 ml-2" onclick="removeShow(<?= $showId ?>)">✖</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <script>
        const showDropdown = document.getElementById('showDropdown');
        const selectedShowsContainer = document.getElementById('selectedShows');

        showDropdown.addEventListener('change', () => {
          const selectedOption = showDropdown.options[showDropdown.selectedIndex];
          const showId = selectedOption.value;
          const showName = selectedOption.text;

          if (showId) {
            const showWrapper = document.createElement('div');
            showWrapper.classList.add('flex', 'items-center', 'gap-2');
            showWrapper.id = `show-${showId}`;

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'show_ids[]';
            hiddenInput.value = showId;

            const label = document.createElement('span');
            label.textContent = showName;
            label.classList.add('text-gray-700');

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.textContent = '✖';
            removeButton.classList.add('text-red-500', 'hover:text-red-700', 'ml-2');
            removeButton.addEventListener('click', () => {
              showWrapper.remove();
              const option = document.createElement('option');
              option.value = showId;
              option.textContent = showName;
              showDropdown.appendChild(option);
            });

            showWrapper.appendChild(hiddenInput);
            showWrapper.appendChild(label);
            showWrapper.appendChild(removeButton);

            selectedShowsContainer.appendChild(showWrapper);
            selectedOption.remove();
          }
        });
      </script>

      <div>
        <label class="block font-medium mb-1">Linked Characters</label>
        <div class="relative">
          <select id="characterDropdown" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
            <option value="">-- Select a Character --</option>
            <?php foreach ($characters as $character): ?>
              <?php if (!in_array($character['id'], $linkedCharacterIds)): ?>
                <option value="<?= $character['id'] ?>"><?= htmlspecialchars($character['stage_name']) ?></option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="selectedCharacters" class="mt-4 space-y-2">
          <?php foreach ($linkedCharacterIds as $characterId): ?>
            <div class="flex items-center gap-2" id="character-<?= $characterId ?>">
              <input type="hidden" name="character_ids[]" value="<?= $characterId ?>">
              <span class="text-gray-700"><?= htmlspecialchars($characters[array_search($characterId, array_column($characters, 'id'))]['stage_name']) ?></span>
              <button type="button" class="text-red-500 hover:text-red-700 ml-2" onclick="removeCharacter(<?= $characterId ?>)">✖</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <script>
        const characterDropdown = document.getElementById('characterDropdown');
        const selectedCharactersContainer = document.getElementById('selectedCharacters');

        characterDropdown.addEventListener('change', () => {
          const selectedOption = characterDropdown.options[characterDropdown.selectedIndex];
          const characterId = selectedOption.value;
          const characterName = selectedOption.text;

          if (characterId) {
            const characterWrapper = document.createElement('div');
            characterWrapper.classList.add('flex', 'items-center', 'gap-2');
            characterWrapper.id = `character-${characterId}`;

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'character_ids[]';
            hiddenInput.value = characterId;

            const label = document.createElement('span');
            label.textContent = characterName;
            label.classList.add('text-gray-700');

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.textContent = '✖';
            removeButton.classList.add('text-red-500', 'hover:text-red-700', 'ml-2');
            removeButton.addEventListener('click', () => {
              characterWrapper.remove();
              const option = document.createElement('option');
              option.value = characterId;
              option.textContent = characterName;
              characterDropdown.appendChild(option);
            });

            characterWrapper.appendChild(hiddenInput);
            characterWrapper.appendChild(label);
            characterWrapper.appendChild(removeButton);

            selectedCharactersContainer.appendChild(characterWrapper);
            selectedOption.remove();
          }
        });
      </script>

      <div class="flex justify-end">
        <button type="submit" class="bg-[#7B1E3B] text-white px-6 py-2 rounded hover:bg-[#9B3454] transition">
          Update Costume
        </button>
      </div>
    </form>
  </main>
  <?php include '../footer.php'; ?>


  <!-- Category Popup -->
  <div id="category-popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded shadow-lg w-96">
      <h2 class="text-xl font-bold mb-4">Add New Category</h2>
      <input type="text" id="new-category-name" placeholder="Category Name" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B] mb-4">
      <div class="flex justify-end gap-4">
        <button type="button" onclick="closeCategoryPopup()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">
          Cancel
        </button>
        <button type="button" onclick="addCategory()" class="bg-[#7B1E3B] text-white px-4 py-2 rounded hover:bg-[#9B3454] transition">
          Add Category
        </button>
      </div>
    </div>
  </div>
</body>
</html>
