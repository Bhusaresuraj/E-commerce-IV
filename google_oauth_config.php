<?php
declare(strict_types=1);

function google_oauth_config(): array
{
    $clientId = getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID';
    $clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET';

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');

    $projectBasePath = '';
    if ($scriptName !== '') {
        if (str_ends_with($scriptName, '/auth/google-start.php')) {
            $projectBasePath = substr($scriptName, 0, -strlen('/auth/google-start.php'));
        } elseif (str_ends_with($scriptName, '/auth/google-callback.php')) {
            $projectBasePath = substr($scriptName, 0, -strlen('/auth/google-callback.php'));
        } else {
            $projectBasePath = rtrim(dirname($scriptName), '/');
        }
    }

    if ($projectBasePath === '') {
        $projectBasePath = '/E-commerce-IV';
    }

    $autoRedirectUri = $scheme . '://' . $host . $projectBasePath . '/auth/google-callback.php';
    $redirectUri = getenv('GOOGLE_REDIRECT_URI') ?: $autoRedirectUri;

    return [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'auth_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_endpoint' => 'https://oauth2.googleapis.com/token',
        'userinfo_endpoint' => 'https://openidconnect.googleapis.com/v1/userinfo',
        'scope' => 'openid email profile',
    ];
}
