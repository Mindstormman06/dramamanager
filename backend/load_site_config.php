<?php
// Return existing config, creating site_config.php with defaults if missing.

$siteConfigFile = __DIR__ . '/site_config.php';

$defaults = [
    'site_title' => 'QSS Drama',
    'header_bg_colour' => '#4e0f0f',
    'header_text' => '#eceaea',
    'header_text_hover' => '#ffc966',
    'highlight_colour' => '#4e0f0f',
    'text_colour' => '#4e0f0f',
    'button_colour' => '#4e0f0f',
    'button_hover_colour' => '#9b3454',
    'border_colour' => '#4e0f0f',
    'enable_mascot' => true,
    'footer_bg_colour' => '#1f2937',
    'footer_text' => '#8a8a8a',
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