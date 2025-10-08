<?php
function log_event($message, $level = 'INFO') {
    $logFile = __DIR__ . '/logs/site.log';
    $date = date('Y-m-d H:i:s');
    $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')[0] ?: ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN');
    $entry = "[$date][$ip][$level] $message" . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}