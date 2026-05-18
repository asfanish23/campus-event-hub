#!/usr/bin/env php
<?php

/**
 * This script fixes all permission issues on the Campus Event Hub Laravel backend
 * Run from the project root: php fix-permissions.php
 */

$basePath = __DIR__;
echo "🔧 Fixing Campus Event Hub permissions...\n";
echo "Base path: $basePath\n\n";

require_once $basePath . '/bootstrap/ensure_runtime_paths.php';

$purgeRuntimeCache = in_array('--purge-cache', $argv ?? [], true) || in_array('--clear-cache', $argv ?? [], true);

$report = campusEventHubEnsureRuntimePaths($basePath);

if (! empty($report['created_directories'])) {
    echo "✓ Created directories:\n";
    foreach ($report['created_directories'] as $directory) {
        echo "  - $directory\n";
    }
    echo "\n";
}

if (! empty($report['updated_directories'])) {
    echo "✓ Standardized permissions for directories:\n";
    foreach ($report['updated_directories'] as $directory) {
        echo "  - $directory\n";
    }
    echo "\n";
}

if (! empty($report['created_files'])) {
    echo "✓ Ensured runtime files exist:\n";
    foreach ($report['created_files'] as $file) {
        echo "  - $file\n";
    }
    echo "\n";
}

if (! empty($report['warnings'])) {
    echo "⚠ Warnings:\n";
    foreach ($report['warnings'] as $warning) {
        echo "  - $warning\n";
    }
    echo "\n";
}

function campusEventHubStandardizePermissions(string $path, bool $changeOwnership): void
{
    if (! is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $fullPath = $item->getPathname();

        if ($item->isDir()) {
            @chmod($fullPath, 02775);

            if ($changeOwnership) {
                @chown($fullPath, 'www-data');
                @chgrp($fullPath, 'www-data');
            }

            continue;
        }

        @chmod($fullPath, 0664);

        if ($changeOwnership) {
            @chown($fullPath, 'www-data');
            @chgrp($fullPath, 'www-data');
        }
    }
}

echo "🛠 Standardizing existing storage and bootstrap cache permissions...\n";

campusEventHubStandardizePermissions($basePath . '/storage', function_exists('posix_geteuid') && posix_geteuid() === 0);
campusEventHubStandardizePermissions($basePath . '/bootstrap/cache', function_exists('posix_geteuid') && posix_geteuid() === 0);

echo "✓ Existing nested files and directories normalized\n\n";

if ($purgeRuntimeCache) {
    echo "🧹 Purging file-based runtime cache...\n";

    $cacheFiles = glob($basePath . '/storage/framework/cache/data/*') ?: [];
    foreach ($cacheFiles as $file) {
        if (is_file($file) && @unlink($file)) {
            echo "✓ Removed cache file: " . basename($file) . "\n";
        }
    }

    $viewFiles = glob($basePath . '/storage/framework/views/*') ?: [];
    foreach ($viewFiles as $file) {
        if (is_file($file) && @unlink($file)) {
            echo "✓ Removed view cache file: " . basename($file) . "\n";
        }
    }

    echo "\n";
}

echo "\n✅ Permissions fixed successfully!\n";
echo "\nNext steps:\n";
echo "1. Run: php artisan migrate (if not already done)\n";
echo "2. Restart your Laravel server\n";
echo "3. Test the API by visiting: https://aseems.ddns.net/api/health\n";

?>
