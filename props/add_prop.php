<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';
if ($_SESSION['role'] != 'teacher' && !in_array('props', $_SESSION['student_roles'])) die('You are not authorized to access this page.');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '') ?: null; // Convert blank to NULL
    $location = trim($_POST['location'] ?? '') ?: null; // Convert blank to NULL
    $condition = trim($_POST['itemcondition'] ?? '') ?: null; // Convert blank to NULL
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $show_ids = $_POST['show_ids'] ?? []; // Array for selected shows

    if ($name === '') {
        die("Name is required.");
    }

    // Photo handling
    $photo_url = null;
    if (!empty($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $filename = uniqid('prop_', true) . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photo_url = '/uploads/' . $filename;
        }
    }

    // Insert prop
    $stmt = $pdo->prepare("
        INSERT INTO props (name, description, location, itemcondition, category_id, photo_url)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $description, $location, $condition, $category_id, $photo_url]);
    $prop_id = $pdo->lastInsertId();

    // Link to shows
    if (!empty($show_ids)) {
        $stmt = $pdo->prepare("INSERT INTO showprops (show_id, prop_id) VALUES (?, ?)");
        foreach ($show_ids as $show_id) {
            $stmt->execute([$show_id, $prop_id]);
        }
    }

    header("Location: props.php");
    exit;
}

// Fetch categories and shows for dropdowns
$categories = $pdo->query("SELECT id, name FROM propcategories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$shows = $pdo->query("SELECT id, title, semester, year FROM shows ORDER BY year DESC, semester DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Prop | QSS Drama</title>
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
      fetch('../backend/props/add_category.php', {
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
    <h1 class="text-3xl font-bold text-[#7B1E3B] mb-6">Add New Prop</h1>
    <a href="props.php" class="text-blue-600 hover:underline mb-4">← Back to Prop List</a>

    <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label for="name" class="block font-medium mb-1">Name *</label>
        <input type="text" name="name" id="name" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
      </div>

      <div>
        <label for="photo" class="block font-medium mb-1">Photo</label>
        <input type="file" name="photo" id="photo" accept="image/*" class="w-full border rounded px-3 py-2">
      </div>

      <div>
        <label for="description" class="block font-medium mb-1">Description</label>
        <textarea name="description" id="description" rows="3" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]"></textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="location" class="block font-medium mb-1">Location</label>
          <input type="text" name="location" id="location" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
        </div>
        <div>
          <label for="itemcondition" class="block font-medium mb-1">Condition</label>
          <select name="itemcondition" id="itemcondition" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
            <option value="">-- Select Condition --</option>
            <option value="A">Excellent</option>
            <option value="B">Good</option>
            <option value="C">Fair</option>
            <option value="D">Poor</option>
          </select>
        </div>
      </div>

      <div>
        <label for="category_id" class="block font-medium mb-1">Category</label>
        <div class="flex items-center gap-4">
          <select name="category_id" id="category_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="button" onclick="openCategoryPopup()" class="bg-[#7B1E3B] text-white px-6 py-2 rounded hover:bg-[#9B3454] transition">
            +Category
          </button>
        </div>
      </div>

      <div>
        <label class="block font-medium mb-1">Used in Shows</label>
        <div class="relative">
          <select id="showDropdown" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
            <option value="">-- Select a Show --</option>
            <?php foreach ($shows as $show): ?>
              <option value="<?= $show['id'] ?>"><?= htmlspecialchars($show['title'] . " ({$show['semester']} {$show['year']})") ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="selectedShows" class="mt-4 space-y-2">
          <!-- Dynamically added selected shows will appear here -->
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
            // Create a new entry for the selected show
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
              // Remove the show from the selected list
              showWrapper.remove();

              // Re-add the show to the dropdown
              const option = document.createElement('option');
              option.value = showId;
              option.textContent = showName;
              showDropdown.appendChild(option);
            });

            showWrapper.appendChild(hiddenInput);
            showWrapper.appendChild(label);
            showWrapper.appendChild(removeButton);

            selectedShowsContainer.appendChild(showWrapper);

            // Remove the selected option from the dropdown
            selectedOption.remove();
          }
        });
      </script>

      <div class="flex justify-end">
        <button type="submit" class="bg-[#7B1E3B] text-white px-6 py-2 rounded hover:bg-[#9B3454] transition">
          Save Prop
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
