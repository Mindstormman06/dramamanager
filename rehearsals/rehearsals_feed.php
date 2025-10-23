<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../backend/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  echo json_encode([]);
  exit;
}

$user_id = $_SESSION['user_id'];
$show_id = $_SESSION['active_show'];

// Grab rehearsals and whether the logged-in user is attending
$stmt = $pdo->prepare("
  SELECT
    r.*,
    EXISTS(SELECT 1 FROM rehearsal_attendees ra WHERE ra.rehearsal_id = r.id AND ra.user_id = ?) AS is_attending
  FROM rehearsals r
  WHERE r.show_id = ?
");
$stmt->execute([$user_id, $show_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($rows as $r) {
  $start = $r['date'] . 'T' . substr($r['start_time'], 0, 8);
  $end   = $r['date'] . 'T' . substr($r['end_time'], 0, 8);

  $events[] = [
    'id'    => (int)$r['id'],
    'title' => $r['title'],
    'start' => $start,
    'end'   => $end,
    'extendedProps' => [
      'location'  => $r['location'],
      'notes'     => $r['notes'],
      'attending' => (bool)$r['is_attending'],
    ],
  ];
}

echo json_encode($events);
