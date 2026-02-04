#!/bin/bash
cd c:\laragon\www\CampusEventHub

# Add Instagram account for Jaguar FC (Club ID 1)
php artisan tinker --execute="
App\Models\InstagramAccount::create([
    'club_id' => 1,
    'instagram_username' => 'jaguar_fc_official',
    'instagram_business_id' => '17843928374',
    'access_token' => 'test_token_jaguar_fc_real_data',
    'is_active' => true,
]);
echo 'Instagram account created for Jaguar FC';
"

# Add Instagram account for Netcentric Club (Club ID 2)
php artisan tinker --execute="
App\Models\InstagramAccount::create([
    'club_id' => 2,
    'instagram_username' => 'netcentric_club_official',
    'instagram_business_id' => '28954039485',
    'access_token' => 'test_token_netcentric_real_data',
    'is_active' => true,
]);
echo 'Instagram account created for Netcentric Club';
"

# Verify
php artisan instagram:debug
