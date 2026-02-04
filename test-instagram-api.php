<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\InstagramService;
use Illuminate\Support\Facades\Log;

try {
    $service = app(InstagramService::class);
    
    // Test with a simple public image
    $testImage = 'https://www.gstatic.com/images/icons/material/system/1x/home_white_24dp.png';
    $caption = 'Test Post - ' . date('Y-m-d H:i:s');
    
    echo "Attempting to post test image to Instagram...\n";
    echo "Image URL: $testImage\n";
    echo "Caption: $caption\n\n";
    
    $response = $service->postImage($testImage, $caption);
    
    echo "Response:\n";
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}
?>
