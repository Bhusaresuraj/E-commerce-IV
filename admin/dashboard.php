<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_user_id'])) {
    header('Location: login.php');
    exit;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$db = get_db_connection();
$submissions = [];
$result = $db->query(
    'SELECT id, source_page, full_name, email, phone, message, payload_json, ip_address, user_agent, created_at
     FROM form_submissions
     ORDER BY id DESC'
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    $result->free();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
    .topbar { background: #2c3e50; color: #fff; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; }
    .topbar a { color: #fff; text-decoration: none; background: #c0392b; padding: 8px 12px; border-radius: 4px; }
    .container { padding: 20px; }
    table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #ddd; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: top; font-size: 13px; }
    th { background: #f0f2f5; }
    pre { margin: 0; white-space: pre-wrap; word-break: break-word; max-width: 360px; }
    .muted { color: #666; font-size: 12px; }
  </style>
</head>
<body>
  <div class="topbar">
    <div>
      Logged in as <strong><?php echo h((string) ($_SESSION['admin_username'] ?? 'admin')); ?></strong>
    </div>
    <div>
      <a href="controls.php" style="margin-right:8px;">Admin Controls</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <div class="container">
    <h2>Form Submissions</h2>
    <p class="muted">All form POST submissions are listed here.</p>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Source Page</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Message</th>
          <th>All Submitted Data</th>
          <th>Meta</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($submissions === []): ?>
          <tr>
            <td colspan="8">No submissions yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($submissions as $row): ?>
            <tr>
              <td><?php echo (int) $row['id']; ?></td>
              <td><?php echo h((string) $row['source_page']); ?></td>
              <td><?php echo h((string) $row['full_name']); ?></td>
              <td><?php echo h((string) $row['email']); ?></td>
              <td><?php echo h((string) $row['phone']); ?></td>
              <td><?php echo h((string) $row['message']); ?></td>
              <td><pre><?php echo h((string) $row['payload_json']); ?></pre></td>
              <td>
                <div><strong>IP:</strong> <?php echo h((string) $row['ip_address']); ?></div>
                <div><strong>UA:</strong> <?php echo h((string) $row['user_agent']); ?></div>
                <div><strong>At:</strong> <?php echo h((string) $row['created_at']); ?></div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
