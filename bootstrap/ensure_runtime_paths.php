<?php

if (! function_exists('campusEventHubEnsureRuntimePaths')) {
    function campusEventHubEnsureRuntimePaths(?string $basePath = null): array
    {
        $basePath = $basePath ?? dirname(__DIR__);

        $directories = [
            'storage',
            'storage/app',
            'storage/app/public',
            'storage/framework',
            'storage/framework/cache',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache',
        ];

        $report = [
            'base_path' => $basePath,
            'created_directories' => [],
            'updated_directories' => [],
            'created_files' => [],
            'warnings' => [],
        ];

        $canChangeOwnership = function_exists('posix_geteuid') && posix_geteuid() === 0;
        $targetUser = 'www-data';
        $targetGroup = 'www-data';

        foreach ($directories as $relativePath) {
            $absolutePath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (! is_dir($absolutePath)) {
                if (! @mkdir($absolutePath, 02775, true) && ! is_dir($absolutePath)) {
                    $report['warnings'][] = sprintf('Unable to create directory: %s', $absolutePath);
                    continue;
                }

                $report['created_directories'][] = $relativePath;
            }

            if (@chmod($absolutePath, 02775)) {
                $report['updated_directories'][] = $relativePath;
            }

            if ($canChangeOwnership) {
                @chown($absolutePath, $targetUser);
                @chgrp($absolutePath, $targetGroup);
            }
        }

        foreach ($report['warnings'] as $warning) {
            error_log('[CampusEventHub] ' . $warning);
        }

        return $report;
    }
}