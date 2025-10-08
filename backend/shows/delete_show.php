<?php
require_once __DIR__ . '/../db.php';
include __DIR__ . '/../../log.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $pdo->beginTransaction();

        // Fetch the script path for the show
        $scriptStmt = $pdo->prepare("SELECT script_path, title FROM shows WHERE id = ?");
        $scriptStmt->execute([$id]);
        $show = $scriptStmt->fetch(PDO::FETCH_ASSOC);

        // Delete the script file if it exists (BROKEN)
        // if (!empty($show['script_path'])) {
        //     $scriptFullPath = __DIR__ . '/../../' . ltrim($show['script_path'], '/');
        //     if (file_exists($scriptFullPath)) {
        //         unlink($scriptFullPath);
        //     }
        // }

        // Delete all characters linked to the show
        $charStmt = $pdo->prepare("DELETE FROM characters WHERE show_id = ?");
        $charStmt->execute([$id]);

        // Delete all lines linked to the show
        $lineStmt = $pdo->prepare("DELETE FROM showlines WHERE show_id = ?");
        $lineStmt->execute([$id]);

        // Delete links between the show and costumes
        $costumeLinkStmt = $pdo->prepare("DELETE FROM showcostumes WHERE show_id = ?");
        $costumeLinkStmt->execute([$id]);

        // Delete links between the show and props
        $propLinkStmt = $pdo->prepare("DELETE FROM showprops WHERE show_id = ?");
        $propLinkStmt->execute([$id]);

        // Delete the show itself
        $showStmt = $pdo->prepare("DELETE FROM shows WHERE id = ?");
        $showStmt->execute([$id]);

        $pdo->commit();

        log_event("Show '{$show['title']}' (ID: $id) deleted by user '{$_SESSION['username']}'", 'INFO');

        header("Location: /shows/?success=deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: /shows/?error=linked");
        exit;
    }
}

header("Location: /shows/");
exit;
