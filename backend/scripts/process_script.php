<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sanity check
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['script_pdf'])) {
    die("Invalid request.");
}

$characterList = $_POST['character_list'] ?? '';
$characterList = trim($characterList);

if (empty($characterList)) {
    die("Character list is required.");
}

// Handle PDF upload
$uploadDir = __DIR__ . "/../../uploads/";
$uploadedFile = $_FILES['script_pdf'];
$ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

if ($ext !== 'pdf') {
    die("Only PDF files are allowed.");
}

$tempName = uniqid("script_", true) . ".pdf";
$targetPath = $uploadDir . $tempName;

if (!move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
    die("Failed to upload file.");
}

// Call Python script
$escapedChars = escapeshellarg($characterList);
$escapedPath = escapeshellarg($targetPath);
$pythonScriptPath = __DIR__ . '/analyze_script.py';
$escapedScriptPath = escapeshellarg($pythonScriptPath);
$command = "C:/Users/Aiden/AppData/Local/Programs/Python/Python38/python.exe $escapedScriptPath $escapedChars $escapedPath";
$output = shell_exec($command . " 2>&1"); // Capture both stdout and stderr
if (!$output) {
    die("Failed to analyze script. Command output: " . htmlspecialchars($output));
}

$data = json_decode($output, true);

if (!$data) {
    die("Failed to parse output. Raw output: " . htmlspecialchars($output));
}

$_SESSION['script_analysis'] = $data;
$_SESSION['character_list'] = $characterList;

// Redirect to master show creation form
header("Location: ../../scripts/create_show_from_script.php");
exit;
?>

