<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function get_redirect_target(): string
{
    $fallback = 'shop-index.php';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    if (!is_string($referer) || $referer === '') {
        return $fallback;
    }

    $path = parse_url($referer, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return $fallback;
    }

    $file = basename($path);
    if (!preg_match('/\.php$/i', $file)) {
        return $fallback;
    }

    return $file;
}

function with_status_query(string $target, string $status): string
{
    return $target . (str_contains($target, '?') ? '&' : '?') . 'form_status=' . urlencode($status);
}

function first_non_empty(array $data, array $keys): string
{
    foreach ($keys as $key) {
        if (!array_key_exists($key, $data)) {
            continue;
        }

        $value = is_array($data[$key]) ? json_encode($data[$key]) : (string) $data[$key];
        $value = trim((string) $value);
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: shop-index.php');
    exit;
}

$redirectTarget = get_redirect_target();
$sourcePage = basename($redirectTarget);

$payload = [];
foreach ($_POST as $key => $value) {
    if (!is_string($key) || $key === '' || str_starts_with($key, '_')) {
        continue;
    }

    if (is_array($value)) {
        $payload[$key] = json_encode($value);
        continue;
    }

    $payload[$key] = trim((string) $value);
}

if ($payload === []) {
    header('Location: ' . with_status_query($redirectTarget, 'empty'));
    exit;
}

$fullName = first_non_empty($payload, ['name', 'full_name', 'fullname', 'first_name']);
$email = first_non_empty($payload, ['email', 'mail', 'user_email']);
$phone = first_non_empty($payload, ['phone', 'mobile', 'telephone']);
$message = first_non_empty($payload, ['message', 'comments', 'query', 'description', 'address']);
$ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
$userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
$payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);

$db = get_db_connection();
$statement = $db->prepare(
    'INSERT INTO form_submissions (source_page, full_name, email, phone, message, payload_json, ip_address, user_agent)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);

if (!$statement) {
    header('Location: ' . with_status_query($redirectTarget, 'error'));
    exit;
}

$statement->bind_param(
    'ssssssss',
    $sourcePage,
    $fullName,
    $email,
    $phone,
    $message,
    $payloadJson,
    $ipAddress,
    $userAgent
);

$ok = $statement->execute();
$statement->close();

header('Location: ' . with_status_query($redirectTarget, $ok ? 'success' : 'error'));
exit;
