<?php
// Return existing config, creating site_config.php with defaults if missing.

$siteConfigFile = __DIR__ . '/site_config.php';

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
];

if (!file_exists($siteConfigFile)) {
    $php = "<?php\n\nreturn " . var_export($defaults, true) . ";\n";
    if (@file_put_contents($siteConfigFile, $php, LOCK_EX) === false) {
        return $defaults;
    }
}

$config = @include $siteConfigFile;
if (!is_array($config)) {
    return $defaults;
}
return $config;