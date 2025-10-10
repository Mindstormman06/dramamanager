<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../log.php';
include __DIR__ . '/../header.php';

// Require admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /');
    exit;
}

$configPath = __DIR__ . '/../backend/config/site_config.php';
$config = file_exists($configPath) ? (require $configPath) : [];

// Allowed keys and simple validators
$allowedKeys = [
    'site_title' => 'string',
    'header_bg_colour' => 'color',
    'header_text' => 'color',
    'header_text_hover' => 'color', // added
    'highlight_colour' => 'color',
    'text_colour' => 'color',
    'button_colour' => 'color',
    'button_hover_colour' => 'color',
    'border_colour' => 'color',
    'enable_mascot' => 'bool',
    'footer_bg_colour' => 'color',
    'footer_text' => 'color',
];

$errors = [];
$success = '';

function normalize_color($s) {
    $s = trim($s);
    if ($s === '') return '';
    // strip alpha if present and ensure leading #
    if ($s[0] !== '#') $s = '#' . $s;
    // Keep only RRGGBB (first 7 chars)
    if (preg_match('/^#([0-9a-fA-F]{6})/', $s, $m)) {
        return '#' . strtolower($m[1]);
    }
    // fallback: try 3-digit form
    if (preg_match('/^#([0-9a-fA-F]{3})$/', $s, $m)) {
        $r = $m[1];
        return '#' . strtolower($r[0].$r[0].$r[1].$r[1].$r[2].$r[2]);
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save settings
    $newConfig = $config;
    foreach ($allowedKeys as $key => $type) {
        if ($type === 'bool') {
            $newConfig[$key] = isset($_POST[$key]) && ($_POST[$key] === '1' || $_POST[$key] === 'on' || $_POST[$key] === 'true');
        } else {
            $val = trim($_POST[$key] ?? '');
            if ($type === 'color') {
                $norm = normalize_color($val);
                if ($norm === '') {
                    $errors[] = "Invalid color for {$key}. Use a hex colour like #RRGGBB.";
                    continue;
                }
                $newConfig[$key] = $norm;
            } else { // string
                $newConfig[$key] = $val;
            }
        }
    }

    // Handle logo upload (same rules as before)
    // Replace the entire logo upload block with this simplified version
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading logo file.';
        } else {
            $file = $_FILES['logo'];
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Logo file too large (max 5MB).';
            } else {
                $tmp = $file['tmp_name'];
                $info = @getimagesize($tmp);
                $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                if ($info === false || !isset($allowedMime[$info['mime']])) {
                    $errors[] = 'Logo must be a valid image (jpg, png, gif, webp).';
                } else {
                    $uploadDir = __DIR__ . '/../assets/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    
                    $finalPath = $uploadDir . 'logo.png';
                    
                    // Always convert to PNG
                    if (function_exists('imagecreatefromstring') && function_exists('imagepng')) {
                        $imgData = file_get_contents($tmp);
                        $im = @imagecreatefromstring($imgData);
                        if ($im !== false) {
                            // Delete old logo if exists
                            if (file_exists($finalPath)) @unlink($finalPath);
                            
                            if (imagepng($im, $finalPath)) {
                                imagedestroy($im);
                                log_event("Site logo uploaded and converted to PNG by '{$_SESSION['username']}'", 'INFO');
                            } else {
                                $errors[] = 'Failed to save converted logo.';
                                imagedestroy($im);
                            }
                        } else {
                            $errors[] = 'Failed to process image. GD library error.';
                        }
                    } else {
                        $errors[] = 'Server cannot convert images. GD library not available.';
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        // Build output only with allowed keys (preserve unknown keys)
        $out = [];
        foreach ($allowedKeys as $key => $type) {
            if (array_key_exists($key, $newConfig)) $out[$key] = $newConfig[$key];
        }
        foreach ($config as $k => $v) {
            if (!array_key_exists($k, $out) && !array_key_exists($k, $allowedKeys)) $out[$k] = $v;
        }

        $php = "<?php\n\nreturn " . var_export($out, true) . ";\n";
        if (file_put_contents($configPath, $php, LOCK_EX) === false) {
            $errors[] = 'Failed to write config file (check permissions).';
            log_event("Failed to write site_config.php by '{$_SESSION['username']}'", 'ERROR');
        } else {
            log_event("Site configuration updated by '{$_SESSION['username']}'", 'INFO');
            $success = 'Settings saved.';
            $config = $out;
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        }
    }
}

// Prepare logo URL for display: prefer canonical /uploads/logo.png else config uploaded_logo
// Prepare logo URL for display
$logoUrl = '/assets/logo.png';
$logoFsPath = __DIR__ . '/../assets/logo.png';
$logoExists = file_exists($logoFsPath);
if ($logoExists) {
    $logoUrl .= '?ts=' . filemtime($logoFsPath);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Site Settings | Drama Manager</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
      <h1 class="text-2xl font-bold mb-4">Site Settings</h1>

      <?php if ($errors): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="block font-semibold">Site title</label>
          <input name="site_title" value="<?= htmlspecialchars($config['site_title'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-semibold">Header background colour</label>
            <input type="color" name="header_bg_colour" value="<?= htmlspecialchars(normalize_color($config['header_bg_colour'] ?? '#ffffff')) ?>" class="w-full h-10 p-1 rounded">
          </div>

          <div>
            <label class="block font-semibold">Header text colour</label>
            <input type="color" name="header_text" value="<?= htmlspecialchars(normalize_color($config['header_text'] ?? '#000000')) ?>" class="w-full h-10 p-1 rounded">
          </div>

          <div>
            <label class="block font-semibold">Header text hover colour</label>
            <input type="color" name="header_text_hover" value="<?= htmlspecialchars(normalize_color($config['header_text_hover'] ?? $config['header_text_hover'] ?? '#000000')) ?>" class="w-full h-10 p-1 rounded">
          </div>

          <div>
            <label class="block font-semibold">Highlight colour</label>
            <input type="color" name="highlight_colour" value="<?= htmlspecialchars(normalize_color($config['highlight_colour'] ?? '#00b7ff')) ?>" class="w-full h-10 p-1 rounded">
          </div>
          <div>
            <label class="block font-semibold">Text colour</label>
            <input type="color" name="text_colour" value="<?= htmlspecialchars(normalize_color($config['text_colour'] ?? '#000000')) ?>" class="w-full h-10 p-1 rounded">
          </div>
          <div>
            <label class="block font-semibold">Button colour</label>
            <input type="color" name="button_colour" value="<?= htmlspecialchars(normalize_color($config['button_colour'] ?? '#1e3a8a')) ?>" class="w-full h-10 p-1 rounded">
          </div>
          <div>
            <label class="block font-semibold">Button hover colour</label>
            <input type="color" name="button_hover_colour" value="<?= htmlspecialchars(normalize_color($config['button_hover_colour'] ?? '#45b6e2')) ?>" class="w-full h-10 p-1 rounded">
          </div>
          <div>
            <label class="block font-semibold">Border colour</label>
            <input type="color" name="border_colour" value="<?= htmlspecialchars(normalize_color($config['border_colour'] ?? '#1e3a8a')) ?>" class="w-full h-10 p-1 rounded">
          </div>
          <div>
            <label class="block font-semibold">Footer background colour</label>
            <input type="color" name="footer_bg_colour" value="<?= htmlspecialchars(normalize_color($config['footer_bg_colour'] ?? '#1e3a8a')) ?>" class="w-full h-10 p-1 rounded">
          </div>
          <div>
            <label class="block font-semibold">Footer text colour</label>
            <input type="color" name="footer_text" value="<?= htmlspecialchars(normalize_color($config['footer_text'] ?? '#b6aa8d')) ?>" class="w-full h-10 p-1 rounded">
          </div>
        </div>

        <div>
          <label class="inline-flex items-center">
            <input type="checkbox" name="enable_mascot" value="1" <?= (!empty($config['enable_mascot']) ? 'checked' : '') ?> class="mr-2">
            Enable mascot
          </label>
        </div>

        <hr class="my-4">

        <div>
          <label class="block font-semibold mb-2">Current logo</label>
          <?php if ($logoExists): ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="h-20 mb-2">
          <?php else: ?>
            <div class="text-sm text-gray-600 mb-2">No logo uploaded.</div>
          <?php endif; ?>

          <div>
            <label class="block font-semibold">Upload new logo (jpg, png, gif, webp) - max 5MB</label>
            <input type="file" name="logo" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
          </div>
        </div>

        <div>
          <button type="submit" class="bg-[<?= htmlspecialchars($config['button_colour'] ?? '#7B1E3B') ?>] hover:bg-[<?= htmlspecialchars($config['button_hover_colour'] ?? '#9B3454') ?>] text-white px-4 py-2 rounded">Save settings</button>
        </div>
      </form>
    </div>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>