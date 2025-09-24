<?php
if (session_status() === PHP_SESSION_NONE) session_start();


// Sanity check
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['script_pdf'])) {
    die("Invalid request.");
}

require_once '../../backend/db.php';

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

// Store the uploaded script path in the session
$_SESSION['uploaded_script_path'] = '../uploads/' . $tempName;

// Get character list (manual or auto-detect)
$characterList = trim($_POST['character_list'] ?? '');
$pythonScriptPath = __DIR__ . '/analyze_script.py';
$escapedScriptPath = escapeshellarg($pythonScriptPath);
$escapedPath = escapeshellarg($targetPath);

// Set correct Python binary path
$pythonBin = 'py';

if (empty($characterList)) {
    // AUTO-DETECT mode
    $command = "$pythonBin $escapedScriptPath AUTO_DETECT $escapedPath";
    $output = shell_exec($command . " 2>&1");
    $data = json_decode($output, true);

    if (!$data || isset($data['error'])) {
        die("Failed to auto-detect characters. Raw output: " . htmlspecialchars($output));
    }

    // Build character list string for storing
    $characterList = implode(', ', array_keys($data));
} else {
    // MANUAL mode
    $escapedChars = escapeshellarg($characterList);
    $command = "$pythonBin $escapedScriptPath $escapedChars $escapedPath";
    $output = shell_exec($command . " 2>&1");
    $data = json_decode($output, true);

    if (!$data || isset($data['error'])) {
        die("Failed to analyze script. Raw output: " . htmlspecialchars($output));
    }
}

// Store in session for next step
$_SESSION['script_analysis'] = $data;
$_SESSION['character_list'] = $characterList;

// Redirect to master show creation form
header("Location: /scripts/show/");
exit;
