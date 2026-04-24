<?php
declare(strict_types=1);

session_start();

$error = (string) ($_SESSION['google_login_error'] ?? '');
unset($_SESSION['google_login_error']);

$isLoggedIn = isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
$userName = (string) ($_SESSION['user_name'] ?? 'User');
$userEmail = (string) ($_SESSION['user_email'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login with Google</title>
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #f5f7fb; color: #1f2937; }
    .page { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
    .card { width: 100%; max-width: 460px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
    h1 { margin: 0 0 12px; font-size: 28px; }
    p { margin: 0 0 16px; line-height: 1.5; color: #4b5563; }
    .error { margin-bottom: 16px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 10px 12px; border-radius: 8px; }
    .google-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; background: #fff; color: #111827; border: 1px solid #d1d5db; border-radius: 8px; text-decoration: none; font-weight: 600; padding: 12px 14px; }
    .google-btn:hover { background: #f9fafb; }
    .links { margin-top: 16px; display: flex; gap: 12px; flex-wrap: wrap; }
    .links a { color: #c0392b; text-decoration: none; font-weight: 600; }
    .status { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 10px 12px; border-radius: 8px; margin-bottom: 16px; }
  </style>
</head>
<body>
  <main class="page">
    <section class="card">
      <h1>Sign in with Google</h1>

      <?php if ($error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if ($isLoggedIn): ?>
        <div class="status">
          You are logged in as <strong><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></strong>
          <?php if ($userEmail !== ''): ?>
            (<?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?>)
          <?php endif; ?>.
        </div>
        <p>You can continue shopping or log out and switch account.</p>
      <?php else: ?>
        <p>Use your Google account for instant sign-in.</p>
      <?php endif; ?>

      <a class="google-btn" href="auth/google-start.php">
        <span>G</span>
        <span>Continue with Google</span>
      </a>

      <div class="links">
        <a href="shop-account.php">Back to My Account</a>
        <?php if ($isLoggedIn): ?>
          <a href="user-logout.php">Logout</a>
        <?php endif; ?>
      </div>
    </section>
  </main>
</body>
</html>

