<?php
// Test script to debug JSON parsing

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Simulate a POST request with JSON
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_SERVER['CONTENT_LENGTH'] = strlen(json_encode(['name' => 'Test', 'email' => 'test@test.com', 'password' => 'pass123']));
$_SERVER['REQUEST_URI'] = '/api/register';
$_SERVER['PATH_INFO'] = '/api/register';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTP_HOST'] = 'aseems.ddns.net';

// Set JSON body
$json = json_encode(['name' => 'Test', 'email' => 'test@test.com', 'password' => 'pass123']);
$input = fopen('php://memory', 'r+');
fwrite($input, $json);
rewind($input);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        \Illuminate\Http\Request::capture()
    );
    
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('content-type') . "\n";
    echo "Body: \n" . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
