<?php
// Debug script to check Python availability
echo "<h3>PHP Python Environment Debug</h3>";

echo "<strong>PHP SAPI:</strong> " . php_sapi_name() . "<br>";
echo "<strong>Operating System:</strong> " . PHP_OS . "<br>";
echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br><br>";

// Check PATH environment variable
echo "<strong>PATH Environment Variable:</strong><br>";
echo "<pre>" . htmlspecialchars($_ENV['PATH'] ?? $_SERVER['PATH'] ?? 'Not found') . "</pre><br>";

// Test different Python commands
$commands = ['python --version', 'py --version', 'python3 --version'];

foreach ($commands as $cmd) {
    echo "<strong>Testing: $cmd</strong><br>";
    
    // Method 1: shell_exec
    $output1 = shell_exec($cmd . " 2>&1");
    echo "shell_exec(): " . htmlspecialchars($output1 ?: 'No output') . "<br>";
    
    // Method 2: exec
    exec($cmd . " 2>&1", $output2, $returnCode);
    echo "exec(): " . htmlspecialchars(implode("\n", $output2)) . " (Return code: $returnCode)<br>";
    
    // Method 3: system
    ob_start();
    system($cmd . " 2>&1", $returnCode2);
    $output3 = ob_get_clean();
    echo "system(): " . htmlspecialchars($output3) . " (Return code: $returnCode2)<br><br>";
    
    // Reset output array for next iteration
    $output2 = [];
}

// Test 'where' or 'which' command
if (PHP_OS_FAMILY === 'Windows') {
    echo "<strong>Testing: where python</strong><br>";
    $whereOutput = shell_exec("where python 2>&1");
    echo "where python: " . htmlspecialchars($whereOutput ?: 'No output') . "<br>";
} else {
    echo "<strong>Testing: which python</strong><br>";
    $whichOutput = shell_exec("which python 2>&1");
    echo "which python: " . htmlspecialchars($whichOutput ?: 'No output') . "<br>";
}
?>