<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$token = config('services.telegram.bot_token');
$apiUrl = rtrim(config('services.telegram.api_url'), '/');
$webhookUrl = config('services.telegram.webhook_url');

if (!$token || !$webhookUrl) {
    fwrite(STDERR, "Missing TELEGRAM_BOT_TOKEN or TELEGRAM_WEBHOOK_URL in .env\n");
    exit(1);
}

$client = new GuzzleHttp\Client(['timeout' => 15]);
$response = $client->post("{$apiUrl}/bot{$token}/setWebhook", [
    'json' => ['url' => $webhookUrl],
]);
$data = json_decode((string) $response->getBody(), true);

if (!($data['ok'] ?? false)) {
    fwrite(STDERR, "Telegram rejected the webhook request.\n");
    exit(1);
}

echo "Telegram webhook configured for {$webhookUrl}\n";
