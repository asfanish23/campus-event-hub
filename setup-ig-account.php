<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InstagramAccount;
use App\Models\Club;

// Load .env file directly
$envPath = __DIR__ . '/.env';
$envLines = file($envPath);
$env = [];

foreach($envLines as $line) {
    $line = trim($line);
    if($line && strpos($line, '=') !== false && strpos($line, '#') !== 0) {
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

// Get credentials from loaded .env
$accessToken = $env['INSTAGRAM_ACCESS_TOKEN'] ?? null;
$businessAccountId = $env['INSTAGRAM_BUSINESS_ACCOUNT_ID'] ?? null;

if(!$accessToken || !$businessAccountId) {
    echo "❌ Missing INSTAGRAM_ACCESS_TOKEN or INSTAGRAM_BUSINESS_ACCOUNT_ID in .env\n";
    exit;
}

echo "Using credentials from .env:\n";
echo "Business Account ID: {$businessAccountId}\n";
echo "Access Token: " . substr($accessToken, 0, 50) . "...\n\n";

// Get first club (Frisbee Club)
$club = Club::first();

if(!$club) {
    echo "❌ No clubs found!\n";
    exit;
}

echo "Creating account for club: {$club->name} (ID: {$club->id})\n\n";

// Create Instagram account entry
$account = InstagramAccount::create([
    'club_id' => $club->id,
    'instagram_username' => 'setup_account',
    'instagram_user_id' => $businessAccountId,
    'instagram_business_id' => $businessAccountId, // Same as user ID for business account
    'access_token' => $accessToken,
    'token_expires_at' => now()->addYears(5), // Set to 5 years in future
    'is_valid' => true,
]);

echo "✅ Created Instagram Account:\n";
echo "Account ID: {$account->id}\n";
echo "Club ID: {$account->club_id}\n";
echo "Username: {$account->instagram_username}\n";
echo "User ID: {$account->instagram_user_id}\n";
