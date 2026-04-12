<?php
// Simple test - just verify routes are loaded
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Get the router
$router = $app['router'];

// List all registered routes
foreach ($router->getRoutes() as $route) {
    if (strpos($route->uri, 'api/register') !== false || strpos($route->uri, 'register') !== false) {
        echo "Found route: " . $route->uri . " - Methods: " . implode(', ', $route->methods) . "\n";
    }
}

