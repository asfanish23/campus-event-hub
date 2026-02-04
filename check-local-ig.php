<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InstagramAccount;

$account = InstagramAccount::first();

if($account) {
    echo "Found Instagram Account:\n";
    echo "ID: {$account->id}\n";
    echo "Club ID: {$account->club_id}\n";
    echo "Username: {$account->instagram_username}\n";
    echo "User ID: {$account->instagram_user_id}\n";
    echo "Access Token (first 50): " . substr($account->access_token, 0, 50) . "...\n";
} else {
    echo "No Instagram account in database!\n";
}
