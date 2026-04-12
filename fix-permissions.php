#!/usr/bin/env php
<?php

/**
 * This script fixes all permission issues on the Campus Event Hub Laravel backend
 * Run from the project root: php fix-permissions.php
 */

$basePath = __DIR__;
echo "🔧 Fixing Campus Event Hub permissions...\n";
echo "Base path: $basePath\n\n";

$directories = [
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

foreach ($directories as $dir) {
    $fullPath = "$basePath/$dir";
    if (is_dir($fullPath)) {
        // Make directory writable
        chmod($fullPath, 0775);
        echo "✓ Fixed permissions for: $dir\n";
    } else {
        echo "⚠ Directory not found: $dir\n";
    }
}

// Fix all files in storage and bootstrap
echo "\n📝 Fixing file permissions in storage and bootstrap directories...\n";

function makeWritable($path) {
    if (is_dir($path)) {
        foreach (glob($path . '/*') as $file) {
            if (is_dir($file)) {
                makeWritable($file);
                chmod($file, 0775);
            } else {
                chmod($file, 0664);
            }
        }
    }
}

makeWritable("$basePath/storage");
makeWritable("$basePath/bootstrap/cache");

echo "✓ Fixed all file permissions\n";

// Clear caches
echo "\n🧹 Clearing caches...\n";

// Remove cache files
$cacheFiles = glob("$basePath/storage/framework/cache/data/*");
foreach ($cacheFiles as $file) {
    if (is_file($file)) {
        @unlink($file);
        echo "✓ Removed cache file: " . basename($file) . "\n";
    }
}

// Remove view cache files
$viewFiles = glob("$basePath/storage/framework/views/*");
foreach ($viewFiles as $file) {
    if (is_file($file)) {
        @unlink($file);
        echo "✓ Removed view file: " . basename($file) . "\n";
    }
}

echo "\n✅ Permissions fixed successfully!\n";
echo "\nNext steps:\n";
echo "1. Run: php artisan migrate (if not already done)\n";
echo "2. Restart your Laravel server\n";
echo "3. Test the API by visiting: https://aseems.ddns.net/api/health\n";

?>
