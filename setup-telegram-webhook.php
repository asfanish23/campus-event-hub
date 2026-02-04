<?php
// Direct webhook setup using cURL
$token = '8298576733:AAHv-8cuYZ21no92uskc24NvQ6ON_yaNNFs';
$webhookUrl = 'https://aseems.ddns.net/api/telegram/webhook';

echo "Setting Telegram webhook...\n";
echo "URL: " . $webhookUrl . "\n\n";

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/setWebhook");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['url' => $webhookUrl]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    echo "Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "\n\n";
    
    if ($data['ok'] ?? false) {
        echo "✅ Webhook set successfully!\n";
        echo "\nVerifying webhook...\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/getWebhookInfo");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $verify = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        echo "Webhook Info:\n";
        echo json_encode($verify, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        echo "❌ Failed to set webhook\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
