<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../google_oauth_config.php';

$config = google_oauth_config();

if (
    $config['client_id'] === 'YOUR_GOOGLE_CLIENT_ID' ||
    $config['client_secret'] === 'YOUR_GOOGLE_CLIENT_SECRET'
) {
    $_SESSION['google_login_error'] = 'Google login is not configured yet. Add your Client ID and Client Secret first.';
    header('Location: ../google-login.php');
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = [
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'response_type' => 'code',
    'scope' => $config['scope'],
    'state' => $state,
    'access_type' => 'offline',
    'prompt' => 'select_account',
];

$authUrl = $config['auth_endpoint'] . '?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;

