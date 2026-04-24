<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../db.php';

if (isset($_SESSION['admin_user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1');

        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($user && password_verify($password, (string) $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_user_id'] = (int) $user['id'];
                $_SESSION['admin_username'] = (string) $user['username'];
                header('Location: dashboard.php');
                exit;
            }
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
    .wrap { max-width: 420px; margin: 60px auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px; }
    h1 { margin: 0 0 20px; font-size: 24px; }
    label { display: block; margin-bottom: 6px; font-weight: 600; }
    input { width: 100%; padding: 10px; margin-bottom: 14px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { width: 100%; padding: 10px; border: 0; background: #c0392b; color: #fff; border-radius: 4px; cursor: pointer; }
    .error { background: #ffe8e8; border: 1px solid #ffb8b8; color: #8a1f1f; padding: 10px; margin-bottom: 14px; border-radius: 4px; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Admin Login</h1>
    <?php if ($error !== ''): ?>
      <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
