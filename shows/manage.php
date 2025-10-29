<?php
include '../header.php';
require_once __DIR__ . '/../backend/upload_image.php';

$user_id = $_SESSION['user_id'];
$show_id = $_SESSION['active_show'];

// Check if current user is allowed (director/manager)
$stmt = $pdo->prepare("
    SELECT role 
    FROM show_users 
    WHERE user_id = ? AND show_id = ?
");
$stmt->execute([$user_id, $show_id]);
$userRole = $stmt->fetchColumn();

if (!in_array($userRole, ['director', 'manager'])) {
    die('<p class="text-center text-red-600 mt-10 font-semibold">Access Denied: You do not have permission to manage this show.</p>');
}

// Fetch show details
$stmt = $pdo->prepare("SELECT * FROM shows WHERE id = ?");
$stmt->execute([$show_id]);
$show = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$show) {
    die('<p class="text-text-red-600 mt-10 font-semibold">Show not found.</p>');
}

$error = '';
$success = '';

// Update show info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_show'])) {
    $newTitle = trim($_POST['title'] ?? '');
    $newCode = strtoupper(trim($_POST['show_code'] ?? ''));

    if ($newTitle === '' || $newCode === '') {
        $error = 'Please fill in both fields.';
    } else {
        $stmt = $pdo->prepare("UPDATE shows SET title = ?, show_code = ? WHERE id = ?");
        $stmt->execute([$newTitle, $newCode, $show_id]);

        $_SESSION['active_show_name'] = $newTitle;
        $success = 'Show details updated successfully.';
        $show['title'] = $newTitle;
        $show['show_code'] = $newCode;
    }
}

// Change member role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $memberId = $_POST['member_id'];
    $newRole = $_POST['role'];

    $allowedRoles = ['director', 'manager', 'cast', 'crew', 'guest'];
    if (in_array($newRole, $allowedRoles)) {
        $stmt = $pdo->prepare("UPDATE show_users SET role = ? WHERE user_id = ? AND show_id = ?");
        $stmt->execute([$newRole, $memberId, $show_id]);
        $success = 'Member role updated.';
    }
}

// Remove member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member'])) {
    $memberId = $_POST['member_id'];
    $stmt = $pdo->prepare("DELETE FROM show_users WHERE user_id = ? AND show_id = ?");
    $stmt->execute([$memberId, $show_id]);
    $success = 'Member removed from show.';
}

// Ban member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ban_member'])) {
    $memberId = $_POST['member_id'];
    $stmt = $pdo->prepare("UPDATE show_users SET banned = 1 WHERE user_id = ? AND show_id = ?");
    $stmt->execute([$memberId, $show_id]);
    $success = 'Member banned from rejoining this show.';
}

// ✅ Unban member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unban_member'])) {
    $memberId = $_POST['member_id'];
    $stmt = $pdo->prepare("UPDATE show_users SET banned = 0 WHERE user_id = ? AND show_id = ?");
    $stmt->execute([$memberId, $show_id]);
    $success = 'Member unbanned and restored to active status.';
}

// Handle photo upload (auto-submit from file input)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    $memberId = (int)($_POST['member_id'] ?? 0);
    if ($memberId <= 0) {
        $error = 'Invalid member.';
    } else {
        $targetDirFs   = __DIR__ . '/../uploads/show_photos';
        $publicBaseUrl = '/uploads/show_photos';
        $photoUrl = handle_image_upload('photo', $targetDirFs, $publicBaseUrl, $uploadErr);

        if ($uploadErr) {
            $error = $uploadErr; // show inline, do not continue
        } else if ($photoUrl) {
            // Remove old entry, then insert new
            $pdo->prepare("DELETE FROM show_user_photos WHERE show_id = ? AND user_id = ?")->execute([$show_id, $memberId]);
            $pdo->prepare("INSERT INTO show_user_photos (show_id, user_id, photo_url) VALUES (?, ?, ?)")
                ->execute([$show_id, $memberId, $photoUrl]);
            $success = 'Headshot uploaded successfully.';
        } else {
            $error = 'Please choose an image to upload.';
        }
    }
}


// Fetch all show members
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email, su.role, su.banned, sup.photo_url
    FROM show_users su
    JOIN users u ON su.user_id = u.id
    LEFT JOIN show_user_photos sup ON sup.show_id = su.show_id AND sup.user_id = su.user_id
    WHERE su.show_id = ?
    ORDER BY su.role, u.username
");
$stmt->execute([$show_id]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Show | <?=htmlspecialchars($show['title'])?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-5xl mx-auto mt-10 bg-white p-8 rounded-lg shadow">
    <h1 class="text-3xl font-bold mb-6 text-center">🎭 Manage “<?= htmlspecialchars($show['title']) ?>”</h1>

    <?php if ($error): ?>
      <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
      <p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <!-- Show Info -->
    <form method="POST" class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
      <div>
        <label class="block font-semibold mb-2">Show Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($show['title']) ?>" class="w-full border border-gray-300 rounded p-2" required>
      </div>
      <div>
        <label class="block font-semibold mb-2">Show Code</label>
        <input type="text" name="show_code" value="<?= htmlspecialchars($show['show_code']) ?>" class="w-full border border-gray-300 rounded p-2 uppercase" required>
      </div>
      <div class="md:col-span-2 flex justify-center">
        <button type="submit" name="update_show" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded">Save Changes</button>
      </div>
    </form>

    <!-- Members Table -->
    <h2 class="text-2xl font-semibold mb-4">👥 Members</h2>
    <div class="overflow-x-auto">
      <table class="w-full border border-gray-200 rounded-lg">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 border-b">Username</th>
            <th class="px-4 py-2 border-b">Email</th>
            <th class="px-4 py-2 border-b">Role</th>
            <th class="px-4 py-2 border-b">Headshot</th>
            <th class="px-4 py-2 border-b text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($members as $member): ?>
            <tr class="border-b <?= $member['banned'] ? 'bg-red-50' : '' ?>">
              <td class="px-4 py-2"><?= htmlspecialchars($member['username']) ?></td>
              <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($member['email']) ?></td>
              <td class="px-4 py-2">
                <?php if (!$member['banned']): ?>
                  <form method="POST" class="flex gap-2">
                    <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                    <select name="role" class="border border-gray-300 rounded p-1 text-sm">
                      <?php
                        $roles = ['director', 'manager', 'cast', 'crew', 'guest'];
                        foreach ($roles as $role) {
                            $selected = $member['role'] === $role ? 'selected' : '';
                            echo "<option value='$role' $selected>" . ucfirst($role) . "</option>";
                        }
                      ?>
                    </select>
                    <button type="submit" name="update_role" class="bg-blue-500 hover:bg-blue-400 text-white px-2 rounded text-sm">Update</button>
                  </form>
                <?php else: ?>
                  <span class="text-gray-500 italic">N/A</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-2 text-center align-top">
                <?php if (!$member['banned']): ?>
                  <td class="px-4 py-2 text-center align-top">
                    <?php if (!empty($member['photo_url'])): ?>
                      <img src="<?= htmlspecialchars($member['photo_url']) ?>" alt="Headshot"
                          class="mx-auto mb-2 w-12 h-12 object-cover rounded-full border" />
                    <?php else: ?>
                      <div class="mx-auto mb-2 w-12 h-12 rounded-full bg-gray-200 grid place-items-center text-gray-500">👤</div>
                    <?php endif; ?>

                    <form method="POST" class="inline upload-form" enctype="multipart/form-data">
                      <input type="hidden" name="member_id" value="<?= (int)$member['id'] ?>">
                      <input type="hidden" name="upload_photo" value="1">
                      <label class="bg-purple-600 hover:bg-purple-500 text-white px-2 py-1 rounded text-xs cursor-pointer inline-block">
                        Upload
                        <input type="file" name="photo" id="photo-<?= (int)$member['id'] ?>" accept="image/*" class="hidden upload-input">
                      </label>
                    </form>

                    <img id="preview-<?= (int)$member['id'] ?>" class="hidden mx-auto mt-2 w-12 h-12 object-cover rounded-full border" alt="Preview"/>
                  </td>

                <?php endif; ?>
              </td>
              <td class="px-4 py-2 text-center space-x-2 align-top">
                <?php if (!$member['banned']): ?>
                  <form method="POST" class="inline">
                    <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                    <button type="submit" name="remove_member" class="bg-yellow-500 hover:bg-yellow-400 text-white px-2 py-1 rounded text-sm">Remove</button>
                  </form>
                  <form method="POST" class="inline">
                    <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                    <button type="submit" name="ban_member" class="bg-red-600 hover:bg-red-500 text-white px-2 py-1 rounded text-sm">Ban</button>
                  </form>
                <?php else: ?>
                  <form method="POST" class="inline">
                    <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                    <button type="submit" name="unban_member" class="bg-green-600 hover:bg-green-500 text-white px-2 py-1 rounded text-sm">Unban</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-8 text-center">
      <a href="/index.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.upload-input').forEach(input => {
        input.addEventListener('change', function() {
          if (this.files.length > 0) {
            const form = this.closest('.upload-form');
            const formData = new FormData(form);
            formData.append('upload_photo', '1');

            fetch(window.location.href, {
              method: 'POST',
              body: formData
            })
            .then(response => response.text())
            .then(() => {
              // Refresh to show success or update instantly
              location.reload();
            })
            .catch(err => console.error('Upload failed:', err));
          }
        });
      });
    });
  </script>
</body>
</html>
