<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /');
    exit;
}

// Helper to update .env file
function updateEnv($data) {
    $envPath = __DIR__ . '/discord-rehearsal-bot/.env';
    $env = file_exists($envPath) ? file_get_contents($envPath) : '';
    foreach ($data as $key => $value) {
        if (preg_match("/^$key=/m", $env)) {
            $env = preg_replace("/^$key=.*/m", "$key=$value", $env);
        } else {
            $env .= "\n$key=$value";
        }
    }
    file_put_contents($envPath, trim($env) . "\n");
}

// Load current settings
$envVars = file_exists(__DIR__ . '/discord-rehearsal-bot/.env')
    ? parse_ini_file(__DIR__ . '/discord-rehearsal-bot/.env')
    : [];
$schedule_pings = !empty($envVars['SCHEDULE_PINGS']) && trim(strtolower($envVars['SCHEDULE_PINGS'])) === 'true';
$schedule_channel = $envVars['SCHEDULE_CHANNEL_ID'] ?? '';
$changelog_pings = !empty($envVars['CHANGELOG_PINGS']) && trim(strtolower($envVars['CHANGELOG_PINGS'])) === 'true';
$changelog_channel = $envVars['CHANGELOG_CHANNEL_ID'] ?? '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_pings = isset($_POST['schedule_pings']);
    $schedule_channel = trim($_POST['schedule_channel']);
    $changelog_pings = isset($_POST['changelog_pings']);
    $changelog_channel = trim($_POST['changelog_channel']);

    updateEnv([
        'SCHEDULE_PINGS' => $schedule_pings ? 'true' : 'false',
        'SCHEDULE_CHANNEL_ID' => $schedule_channel,
        'CHANGELOG_PINGS' => $changelog_pings ? 'true' : 'false',
        'CHANGELOG_CHANNEL_ID' => $changelog_channel
    ]);
    $success = true;

    log_event("Discord bot settings updated by user '{$_SESSION['username']}'");
}
?>

<main class="flex-1 w-full max-w-xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-[<?= htmlspecialchars($config['text_colour']) ?>] mb-6">🤖 Discord Bot Settings</h1>
    <?php if ($success): ?>
        <div class="mb-4 text-green-700 bg-green-100 rounded px-3 py-2">Settings updated! The bot will use the new values automatically.</div>
    <?php endif; ?>
    <form method="POST" class="bg-white rounded-xl shadow p-6 border border-gray-200 space-y-6">
        <div>
            <label class="flex items-center gap-2 mb-1">
                <input type="checkbox" name="schedule_pings" value="1" <?= $schedule_pings ? 'checked' : '' ?> class="accent-[#7B1E3B]">
                Schedule Messages?
            </label>
            <label class="block font-medium mb-1" for="schedule_channel">Schedule Message Channel ID</label>
            <input type="text" name="schedule_channel" id="schedule_channel" required
                   value="<?= htmlspecialchars($schedule_channel) ?>"
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($config['highlight_colour']) ?>]">
        </div>
        <div>
            <label class="flex items-center gap-2 mb-1">
                <input type="checkbox" name="changelog_pings" value="1" <?= $changelog_pings ? 'checked' : '' ?> class="accent-[#7B1E3B]">
                Changelog Messages?
            </label>
            <label class="block font-medium mb-1" for="changelog_channel">Changelog Message Channel ID</label>
            <input type="text" name="changelog_channel" id="changelog_channel" required
                   value="<?= htmlspecialchars($changelog_channel) ?>"
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($config['highlight_colour']) ?>]">
        </div>
        <button type="submit" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] text-white px-4 py-2 rounded hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>]">Save Settings</button>
    </form>
</main>
<?php include '../footer.php'; ?>