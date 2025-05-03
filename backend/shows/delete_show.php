<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    // Check for linked characters or lines
    $charCount = $pdo->prepare("SELECT COUNT(*) FROM characters WHERE show_id = ?");
    $charCount->execute([$id]);
    $hasCharacters = $charCount->fetchColumn() > 0;

    $lineCount = $pdo->prepare("SELECT COUNT(*) FROM showlines WHERE show_id = ?");
    $lineCount->execute([$id]);
    $hasLines = $lineCount->fetchColumn() > 0;

    $costumeCount = $pdo->prepare("SELECT COUNT(*) FROM showcostumes WHERE show_id = ?");
    $costumeCount->execute([$id]);
    $hasCostumes = $costumeCount->fetchColumn() > 0;

    $propCount = $pdo->prepare("SELECT COUNT(*) FROM showprops WHERE show_id = ?");
    $propCount->execute([$id]);
    $hasProps = $propCount->fetchColumn() > 0;


    if ($hasCharacters || $hasLines || $hasCostumes || $hasProps) {
        // Redirect with error
        header("Location: shows.php?error=linked");
        exit;
    }

    // If no dependencies, delete
    $stmt = $pdo->prepare("DELETE FROM shows WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: ../../shows/shows.php");
exit;
