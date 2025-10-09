<?php
$dbConfigFile = __DIR__ . '/db_config.php';

$defaults = [
    'host' => 'localhost',
    'db'   => 'dramamanager',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
];

// If config file doesn't exist, try create it
if (!file_exists($dbConfigFile)) {
    $php = "<?php\n\nreturn " . var_export($defaults, true) . ";\n";
    @file_put_contents($dbConfigFile, $php, LOCK_EX);
    // ignore write failure; we'll return defaults below
}

// Load config, fall back to defaults on failure
$config = @include $dbConfigFile;
if (!is_array($config)) {
    $config = $defaults;
}

return $config;