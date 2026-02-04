<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;

$event = Event::find(22);
if(!$event) {
    echo "Event not found\n";
    exit;
}

echo "Event: " . $event->name . "\n";
echo "Club ID: " . $event->club_id . "\n";

$club = $event->club;
echo "Club Name: " . $club->name . "\n";

$igAccount = $club->instagramAccounts()->first();
if($igAccount) {
    echo "\n✅ IG Account Found!\n";
    echo "IG Username: " . $igAccount->instagram_username . "\n";
    echo "IG User ID: " . $igAccount->instagram_user_id . "\n";
    echo "Access Token: " . (strlen($igAccount->access_token) > 10 ? substr($igAccount->access_token, 0, 10) . '...' : 'EMPTY') . "\n";
} else {
    echo "\n❌ NO Instagram Account configured!\n";
    echo "Club must connect Instagram account first.\n";
}
