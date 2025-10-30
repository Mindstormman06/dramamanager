<?php
// session_bootstrap.php — single shared session across *.qssdrama.site

// Use one shared directory for all subdomains
$SESSION_DIR = 'C:/xampp/sessions'; // use forward slashes on Windows
if (!is_dir($SESSION_DIR)) { @mkdir($SESSION_DIR, 0777, true); }

$COOKIE_PARAMS = [
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '.qssdrama.site',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'None',
];

$TARGET_NAME = 'qssdrama_sess';

// Helper to apply ini + save_path only when no session is active
$apply_ini = function() use ($SESSION_DIR, $COOKIE_PARAMS) {
    ini_set('session.use_cookies',        '1');
    ini_set('session.use_only_cookies',   '1');
    ini_set('session.cookie_domain',      $COOKIE_PARAMS['domain']);
    ini_set('session.cookie_path',        $COOKIE_PARAMS['path']);
    ini_set('session.cookie_secure',      '1');
    ini_set('session.cookie_httponly',    '1');
    ini_set('session.cookie_samesite',    'None');
    ini_set('session.gc_maxlifetime',     '86400'); // optional
    session_save_path($SESSION_DIR);
};

$start_target = function() use ($COOKIE_PARAMS, $TARGET_NAME) {
    session_name($TARGET_NAME);
    session_set_cookie_params($COOKIE_PARAMS);
    // Continue existing ID if present
    if (!empty($_COOKIE[$TARGET_NAME])) {
        session_id($_COOKIE[$TARGET_NAME]);
    }
    session_start();
};

// --- Main logic ---
$status = session_status();

if ($status === PHP_SESSION_NONE) {
    // Safe to set ini/save_path now
    $apply_ini();
    $start_target();
    return;
}

if ($status === PHP_SESSION_ACTIVE) {
    if (session_name() === $TARGET_NAME) {
        // Already correct; DO NOT call ini_set/session_save_path now
        return;
    }

    // Wrong session name (likely PHPSESSID) → migrate
    $oldName = session_name();
    $oldData = $_SESSION ?? [];
    session_write_close();              // closes wrong session

    // Now safe to set ini/save_path
    $apply_ini();
    $start_target();

    // Merge any data set in the old session (rare but safe)
    if (!empty($oldData)) {
        $_SESSION = $oldData + $_SESSION;
    }

    // Expire stray cookie across the zone
    setcookie($oldName, '', time() - 3600, '/', '.qssdrama.site', true, true);
    return;
}

// If PHP_SESSION_DISABLED: nothing we can do here
