<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InstagramAccount;
use App\Models\Club;

$accessToken = config('services.instagram.token');
$businessAccountId = config('services.instagram.user_id');

if(!$accessToken || !$businessAccountId) {
    echo "❌ Missing INSTAGRAM_ACCESS_TOKEN or INSTAGRAM_BUSINESS_ACCOUNT_ID in .env\n";
    exit;
}

echo "Using Instagram credentials from Laravel configuration.\n";
echo "Business Account ID: {$businessAccountId}\n";

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
