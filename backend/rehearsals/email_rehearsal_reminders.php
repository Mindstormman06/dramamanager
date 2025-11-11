<?php
/**
 * Automated rehearsal email reminders
 * -----------------------------------
 * Sends reminder emails 24 hours before each rehearsal
 * using theatre.manager.site@gmail.com (via mailer.php)
 *
 * Schedule this script with cron, e.g.:
 * 0 * * * * php /var/www/html/backend/email_rehearsal_reminders.php >/dev/null 2>&1
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../mailer.php';

// Get all rehearsals starting between 23.5–24.5 hours from now
$stmt = $pdo->prepare("
    SELECT r.*, s.title AS show_title
    FROM rehearsals r
    JOIN shows s ON r.show_id = s.id
    WHERE TIMESTAMPDIFF(MINUTE, NOW(), CONCAT(r.date, ' ', r.start_time))
          BETWEEN 1410 AND 1470
");
$stmt->execute();
$rehearsals = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rehearsals)) {
    exit; // nothing to send
}

foreach ($rehearsals as $r) {
    // Get all attendees who haven't been notified yet
    $q = $pdo->prepare("
        SELECT u.id, u.email, u.full_name
        FROM rehearsal_attendees ra
        JOIN users u ON ra.user_id = u.id
        WHERE ra.rehearsal_id = ? AND ra.notified = 0
    ");
    $q->execute([$r['id']]);
    $attendees = $q->fetchAll(PDO::FETCH_ASSOC);

    if (empty($attendees)) continue;

    foreach ($attendees as $u) {
        // Format times and date
        $date  = date('l, F j', strtotime($r['date']));
        $start = date('g:i A', strtotime($r['start_time']));
        $end   = date('g:i A', strtotime($r['end_time']));

        // Build HTML email
        $subject = "Reminder: Tomorrow's Rehearsal for {$r['show_title']}";
        $body = "
        <div style='font-family:Arial, sans-serif; background:#f9fafb; padding:20px;'>
          <div style='max-width:600px;margin:auto;background:#fff;padding:24px;border-radius:8px;'>
            <h2 style='color:#d6336c;margin-top:0;'>🎭 Reminder: Tomorrow's Rehearsal</h2>
            <p>Hi <strong>{$u['full_name']}</strong>,</p>
            <p>This is a reminder that you're called for the rehearsal <strong>“{$r['title']}”</strong> tomorrow.</p>

            <table style='border-collapse:collapse;width:100%;margin:12px 0;'>
              <tr><td style='padding:4px 0;'><strong>📅 Date:</strong></td><td>{$date}</td></tr>
              <tr><td style='padding:4px 0;'><strong>🕓 Time:</strong></td><td>{$start} – {$end}</td></tr>
              <tr><td style='padding:4px 0;'><strong>📍 Location:</strong></td><td>" . htmlspecialchars($r['location']) . "</td></tr>
            </table>
        ";

        if (!empty($r['notes'])) {
            $body .= "<p style='font-style:italic;color:#555;'>“" . htmlspecialchars($r['notes']) . "”</p>";
        }

        $body .= "
            <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
            <p style='font-size:12px;color:#999;text-align:center;'>This reminder was sent automatically from Theatre Manager.</p>
          </div>
        </div>
        ";

        // Send the email
        $sent = send_email($u['email'], $subject, $body);
        if ($sent) {
            // Mark as notified
            $update = $pdo->prepare("UPDATE rehearsal_attendees SET notified = 1 WHERE rehearsal_id = ? AND user_id = ?");
            $update->execute([$r['id'], $u['id']]);
        } else {
            error_log("Failed to send rehearsal reminder to {$u['email']} for rehearsal ID {$r['id']}");
        }
    }
}
