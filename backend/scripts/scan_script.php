<?php
require_once __DIR__ . '/../db.php';

if (!isset($_GET['show_id'])) {
    die("Show ID is required.");
}

$show_id = intval($_GET['show_id']);

// Fetch the script path for the show
$stmt = $pdo->prepare("SELECT script_path FROM shows WHERE id = ?");
$stmt->execute([$show_id]);
$show = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$show || empty($show['script_path'])) {
    die("No script found for this show.");
}

$script_path = __DIR__ . '/../' . ltrim($show['script_path'], '/');

// Check if the script file exists
if (!file_exists($script_path)) {
    die("Script file not found.");
}

// Run the Python analyzer
$pythonScriptPath = __DIR__ . '/analyze_script.py';
$pythonBin = 'C:/Users/Aiden/AppData/Local/Programs/Python/Python38/python.exe';
$command = escapeshellcmd("$pythonBin $pythonScriptPath AUTO_DETECT " . escapeshellarg($script_path));
$output = shell_exec($command . " 2>&1");
$data = json_decode($output, true);

if (!$data || isset($data['error'])) {
    die("Failed to analyze script. Raw output: " . htmlspecialchars($output));
}

echo '<pre>';
print_r($data);
echo '</pre>';

error_log(print_r($data, true));

// Fetch existing characters for the show
$stmt = $pdo->prepare("SELECT stage_name FROM characters WHERE show_id = ?");
$stmt->execute([$show_id]);
$existingCharacters = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Normalize existing characters to lowercase for comparison
$existingCharactersLower = array_map('strtolower', $existingCharacters);

// Normalize new character names to title case and lowercase for comparison
$normalizedData = [];
foreach ($data as $name => $counts) {
    $normalizedName = ucwords(strtolower($name)); // Convert to title case
    $normalizedData[$normalizedName] = $counts;
}

// Normalize new character names to lowercase for comparison
$newCharactersLower = array_map('strtolower', array_keys($normalizedData));

// Find truly new characters by comparing normalized lowercase names
$newCharactersLowerDiff = array_diff($newCharactersLower, $existingCharactersLower);

// Map back to title case for display and insertion
$newCharacters = [];
$newCharacterCounts = []; // Store counts for new characters
foreach ($newCharactersLowerDiff as $lowerName) {
    foreach ($normalizedData as $titleCaseName => $counts) {
        if (strtolower($titleCaseName) === $lowerName) {
            $newCharacters[] = $titleCaseName;
            $newCharacterCounts[$titleCaseName] = $counts; // Save counts for the new character
            break;
        }
    }
}

// Update mention/line counts for existing characters
foreach ($normalizedData as $name => $counts) {
    if (in_array(strtolower($name), $existingCharactersLower)) { // Compare in lowercase
        $stmt = $pdo->prepare("
            UPDATE characters
            SET mention_count = ?, line_count = ?
            WHERE show_id = ? AND stage_name = ?
        ");
        $stmt->execute([$counts['mentions'], $counts['lines'], $show_id, $name]);

        // Print the query execution details
        echo "Updated $name with mentions: {$counts['mentions']}, lines: {$counts['lines']}<br>";
    }
}

// Store new characters and their counts in the session for prompting
if (!empty($newCharacters)) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['new_characters'] = $newCharacters;
    $_SESSION['new_character_counts'] = $newCharacterCounts; // Include counts
    $_SESSION['show_id'] = $show_id;

    header("Location: ../../shows/add_new_characters.php");
    exit;
}

// Redirect back to the shows page if no new characters are found
header("Location: /shows/?success=scan_complete");
exit;