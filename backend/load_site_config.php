<?php
// Return existing config, creating backend/config/site_config.php with defaults if missing.

$siteConfigFile = __DIR__ . '/config/site_config.php';
$siteConfigDir  = dirname($siteConfigFile);

$defaults = [
    'site_title' => 'Drama Manager',
    'header_bg_colour' => '#37213d',
    'header_text' => '#eceaea',
    'header_text_hover' => '#8bdada',
    'highlight_colour' => '#31adc8',
    'text_colour' => '#31adc8',
    'button_colour' => '#31adc8',
    'button_hover_colour' => '#5bc1d7',
    'border_colour' => '#31adc8',
    'enable_mascot' => false,
    'footer_bg_colour' => '#254678',
    'footer_text' => '#8a8a8a',
    'admin_creation_key' => 'changeme',
    'upload_base_url' => 'upload.qssdrama.site',
];

// Ensure config directory exists
if (!is_dir($siteConfigDir)) {
    @mkdir($siteConfigDir, 0755, true);
}

if (!file_exists($siteConfigFile)) {
    $php = "<?php\n\nreturn " . var_export($defaults, true) . ";\n";
    // Atomic write: write to temp then rename
    $tmp = $siteConfigFile . '.tmp';
    if (@file_put_contents($tmp, $php, LOCK_EX) !== false) {
        @rename($tmp, $siteConfigFile);
        @chmod($siteConfigFile, 0777);
    } else {
        // Can't write: fall back to defaults in-memory
        return $defaults;
    }
}

// Load config, fall back to defaults
$config = @include $siteConfigFile;
if (!is_array($config)) {
    return $defaults;
}
return $config;