<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InstagramAccount;
use App\Models\Club;

$accounts = InstagramAccount::all();
echo "Instagram Accounts: " . $accounts->count() . "\n\n";

if($accounts->isEmpty()) {
    echo "NO accounts found!\n";
    echo "\nAvailable clubs:\n";
    $clubs = Club::all();
    foreach($clubs as $club) {
        echo "- Club ID {$club->id}: {$club->name}\n";
    }
    echo "\nNeed to insert account manually.\n";
} else {
    foreach($accounts as $acc) {
        echo "Account ID: {$acc->id}\n";
        echo "Username: {$acc->instagram_username}\n";
        echo "Club ID: {$acc->club_id}\n";
        echo "---\n";
    }
}
