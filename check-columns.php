<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('events');
echo "Events table columns:\n";
foreach($columns as $col) {
    if(strpos($col, 'instagram') !== false) {
        echo "✓ $col\n";
    }
}

if(!Schema::hasColumn('events', 'instagram_scheduled_at')) {
    echo "\n❌ instagram_scheduled_at column is MISSING!\n";
} else {
    echo "\n✓ All Instagram scheduling columns exist!\n";
}
