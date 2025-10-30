<?php
require_once __DIR__ . '/session_bootstrap.php';
echo "<pre>";
echo "session.save_path: " . ini_get('session.save_path') . "\n";
echo "session file: " . session_save_path() . "\n";
echo "session_name: " . session_name() . "\n";
echo "session_id: " . session_id() . "\n";
print_r(session_get_cookie_params());
echo "</pre>";
?>
