<?php
// Quick debug - remove after testing
header('Content-Type: application/json');

$token = env('INSTAGRAM_ACCESS_TOKEN');
$id = env('INSTAGRAM_BUSINESS_ACCOUNT_ID');

echo json_encode([
    'token_length' => strlen($token),
    'token_start' => substr($token, 0, 40),
    'account_id' => $id,
    'error_hint' => 'If token_length < 100, token is incomplete or wrong'
]);
?>
