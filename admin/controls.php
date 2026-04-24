<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

$db = get_db_connection();
ensure_admin_tables($db);

$section = (string) ($_GET['section'] ?? 'products');
$allowedSections = ['products', 'categories', 'users', 'orders', 'stock', 'coupons'];
if (!in_array($section, $allowedSections, true)) {
    $section = 'products';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($section === 'categories') {
        if ($action === 'create_category') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            if ($name === '') {
                admin_flash_set('Category name is required.', 'error');
            } else {
                $slug = make_slug($name);
                $stmt = $db->prepare('INSERT INTO categories (name, slug, is_active) VALUES (?, ?, ?)');
                if ($stmt) {
                    $stmt->bind_param('ssi', $name, $slug, $isActive);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Category added.' : 'Could not add category.', $ok ? 'success' : 'error');
                }
            }
        } elseif ($action === 'update_category') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            if ($id > 0 && $name !== '') {
                $slug = make_slug($name);
                $stmt = $db->prepare('UPDATE categories SET name = ?, slug = ?, is_active = ? WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('ssii', $name, $slug, $isActive, $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Category updated.' : 'Could not update category.', $ok ? 'success' : 'error');
                }
            }
        } elseif ($action === 'delete_category') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('i', $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Category deleted.' : 'Could not delete category.', $ok ? 'success' : 'error');
                }
            }
        }
        admin_redirect('controls.php', ['section' => 'categories']);
    }

    if ($section === 'products') {
        if ($action === 'create_product') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $sku = trim((string) ($_POST['sku'] ?? ''));
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $price = (float) ($_POST['price'] ?? 0);
            $stock = (int) ($_POST['stock_qty'] ?? 0);
            $description = trim((string) ($_POST['description'] ?? ''));
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if ($name === '' || $sku === '') {
                admin_flash_set('Product name and SKU are required.', 'error');
            } else {
                $cat = $categoryId > 0 ? $categoryId : null;
                $stmt = $db->prepare(
                    'INSERT INTO products (category_id, name, sku, description, price, stock_qty, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                if ($stmt) {
                    $stmt->bind_param('isssdii', $cat, $name, $sku, $description, $price, $stock, $isActive);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Product added.' : 'Could not add product (SKU must be unique).', $ok ? 'success' : 'error');
                }
            }
        } elseif ($action === 'update_product') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $sku = trim((string) ($_POST['sku'] ?? ''));
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $price = (float) ($_POST['price'] ?? 0);
            $stock = (int) ($_POST['stock_qty'] ?? 0);
            $description = trim((string) ($_POST['description'] ?? ''));
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if ($id > 0 && $name !== '' && $sku !== '') {
                $cat = $categoryId > 0 ? $categoryId : null;
                $stmt = $db->prepare(
                    'UPDATE products
                     SET category_id = ?, name = ?, sku = ?, description = ?, price = ?, stock_qty = ?, is_active = ?
                     WHERE id = ?'
                );
                if ($stmt) {
                    $stmt->bind_param('isssdiii', $cat, $name, $sku, $description, $price, $stock, $isActive, $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Product updated.' : 'Could not update product.', $ok ? 'success' : 'error');
                }
            }
        } elseif ($action === 'delete_product') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('i', $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Product deleted.' : 'Could not delete product.', $ok ? 'success' : 'error');
                }
            }
        }
        admin_redirect('controls.php', ['section' => 'products']);
    }

    if ($section === 'users') {
        if ($action === 'create_user') {
            $fullName = trim((string) ($_POST['full_name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $phone = trim((string) ($_POST['phone'] ?? ''));
            $status = (string) ($_POST['status'] ?? 'active');
            if ($fullName !== '' && $email !== '') {
                $stmt = $db->prepare('INSERT INTO users (full_name, email, phone, status) VALUES (?, ?, ?, ?)');
                if ($stmt) {
                    $stmt->bind_param('ssss', $fullName, $email, $phone, $status);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'User added.' : 'Could not add user (email must be unique).', $ok ? 'success' : 'error');
                }
            } else {
                admin_flash_set('Name and email are required.', 'error');
            }
        } elseif ($action === 'delete_user') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('i', $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'User deleted.' : 'Could not delete user.', $ok ? 'success' : 'error');
                }
            }
        }
        admin_redirect('controls.php', ['section' => 'users']);
    }

    if ($section === 'orders') {
        if ($action === 'create_order') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $customerName = trim((string) ($_POST['customer_name'] ?? ''));
            $customerEmail = trim((string) ($_POST['customer_email'] ?? ''));
            $totalAmount = (float) ($_POST['total_amount'] ?? 0);
            $status = (string) ($_POST['status'] ?? 'pending');
            if ($customerName !== '' && $customerEmail !== '') {
                $orderNumber = 'ORD-' . strtoupper(substr(md5((string) microtime(true)), 0, 8));
                $uid = $userId > 0 ? $userId : null;
                $stmt = $db->prepare(
                    'INSERT INTO orders (order_number, user_id, customer_name, customer_email, total_amount, status)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                if ($stmt) {
                    $stmt->bind_param('sissds', $orderNumber, $uid, $customerName, $customerEmail, $totalAmount, $status);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Order created.' : 'Could not create order.', $ok ? 'success' : 'error');
                }
            } else {
                admin_flash_set('Customer name and email are required.', 'error');
            }
        } elseif ($action === 'update_order_status') {
            $id = (int) ($_POST['id'] ?? 0);
            $status = (string) ($_POST['status'] ?? 'pending');
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('si', $status, $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Order status updated.' : 'Could not update status.', $ok ? 'success' : 'error');
                }
            }
        } elseif ($action === 'delete_order') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare('DELETE FROM orders WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('i', $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Order deleted.' : 'Could not delete order.', $ok ? 'success' : 'error');
                }
            }
        }
        admin_redirect('controls.php', ['section' => 'orders']);
    }

    if ($section === 'stock') {
        if ($action === 'update_stock') {
            $id = (int) ($_POST['id'] ?? 0);
            $stock = (int) ($_POST['stock_qty'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE products SET stock_qty = ? WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('ii', $stock, $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Stock updated.' : 'Could not update stock.', $ok ? 'success' : 'error');
                }
            }
        }
        admin_redirect('controls.php', ['section' => 'stock']);
    }

    if ($section === 'coupons') {
        if ($action === 'create_coupon') {
            $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
            $type = (string) ($_POST['discount_type'] ?? 'percent');
            $value = (float) ($_POST['discount_value'] ?? 0);
            $startDate = trim((string) ($_POST['start_date'] ?? ''));
            $endDate = trim((string) ($_POST['end_date'] ?? ''));
            $usageLimit = trim((string) ($_POST['usage_limit'] ?? ''));
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            if ($code !== '') {
                $usage = $usageLimit === '' ? null : (int) $usageLimit;
                $start = $startDate === '' ? null : $startDate;
                $end = $endDate === '' ? null : $endDate;
                $stmt = $db->prepare(
                    'INSERT INTO coupons (code, discount_type, discount_value, start_date, end_date, usage_limit, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                if ($stmt) {
                    $stmt->bind_param('ssdssii', $code, $type, $value, $start, $end, $usage, $isActive);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Coupon added.' : 'Could not add coupon (code must be unique).', $ok ? 'success' : 'error');
                }
            }
        } elseif ($action === 'delete_coupon') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare('DELETE FROM coupons WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('i', $id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    admin_flash_set($ok ? 'Coupon deleted.' : 'Could not delete coupon.', $ok ? 'success' : 'error');
                }
            }
        }
        admin_redirect('controls.php', ['section' => 'coupons']);
    }
}

$flash = admin_flash_get();
$categories = $db->query('SELECT id, name, slug, is_active, created_at FROM categories ORDER BY id DESC');
$products = $db->query(
    'SELECT p.id, p.name, p.sku, p.price, p.stock_qty, p.is_active, p.description, p.created_at, p.category_id, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.id DESC'
);
$users = $db->query('SELECT id, full_name, email, phone, status, created_at FROM users ORDER BY id DESC');
$orders = $db->query('SELECT id, order_number, user_id, customer_name, customer_email, total_amount, status, created_at FROM orders ORDER BY id DESC');
$coupons = $db->query('SELECT id, code, discount_type, discount_value, start_date, end_date, usage_limit, is_active, created_at FROM coupons ORDER BY id DESC');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Controls</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
    .topbar { background: #1f2d3d; color: #fff; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; }
    .topbar a { color: #fff; text-decoration: none; margin-left: 8px; background: #c0392b; padding: 8px 12px; border-radius: 4px; display: inline-block; }
    .topbar .alt { background: #34495e; }
    .wrap { padding: 18px; }
    .tabs a { text-decoration: none; color: #333; background: #e9edf1; padding: 8px 12px; margin-right: 6px; border-radius: 4px; display: inline-block; }
    .tabs a.active { background: #1f2d3d; color: #fff; }
    .panel { margin-top: 14px; background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 14px; }
    .flash { margin-top: 10px; padding: 10px; border-radius: 4px; border: 1px solid #ddd; }
    .flash.success { background: #e8f8ef; color: #0b6b3a; border-color: #bce6cd; }
    .flash.error { background: #ffecec; color: #8a1f1f; border-color: #f5bdbd; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
    th { background: #f0f2f5; }
    input, select, textarea { width: 100%; padding: 6px; box-sizing: border-box; border: 1px solid #bbb; border-radius: 4px; }
    textarea { min-height: 64px; }
    form.inline { display: inline; }
    .btn { border: 0; border-radius: 4px; padding: 7px 10px; cursor: pointer; }
    .btn-primary { background: #1f78d1; color: #fff; }
    .btn-danger { background: #c0392b; color: #fff; }
    .btn-muted { background: #666; color: #fff; }
    .grid-4 { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .mt { margin-top: 10px; }
    @media (max-width: 900px) { .grid-4, .grid-3 { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="topbar">
    <div>Admin Controls</div>
    <div>
      <a class="alt" href="dashboard.php">Dashboard</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>
  <div class="wrap">
    <div class="tabs">
      <a class="<?php echo $section === 'products' ? 'active' : ''; ?>" href="controls.php?section=products">Products</a>
      <a class="<?php echo $section === 'categories' ? 'active' : ''; ?>" href="controls.php?section=categories">Categories</a>
      <a class="<?php echo $section === 'users' ? 'active' : ''; ?>" href="controls.php?section=users">Users</a>
      <a class="<?php echo $section === 'orders' ? 'active' : ''; ?>" href="controls.php?section=orders">Orders</a>
      <a class="<?php echo $section === 'stock' ? 'active' : ''; ?>" href="controls.php?section=stock">Stock</a>
      <a class="<?php echo $section === 'coupons' ? 'active' : ''; ?>" href="controls.php?section=coupons">Offers/Coupons</a>
    </div>

    <?php if ($flash): ?>
      <div class="flash <?php echo admin_h((string) ($flash['type'] ?? 'success')); ?>">
        <?php echo admin_h((string) ($flash['message'] ?? '')); ?>
      </div>
    <?php endif; ?>

    <?php if ($section === 'categories'): ?>
      <div class="panel">
        <h3>Manage Categories</h3>
        <form method="post" action="controls.php?section=categories">
          <input type="hidden" name="action" value="create_category">
          <div class="grid-4">
            <div><input type="text" name="name" placeholder="Category name" required></div>
            <div><label><input type="checkbox" name="is_active" checked> Active</label></div>
            <div><button class="btn btn-primary" type="submit">Add Category</button></div>
          </div>
        </form>
        <table>
          <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if (!$categories || $categories->num_rows === 0): ?>
              <tr><td colspan="6">No categories found.</td></tr>
            <?php else: while ($row = $categories->fetch_assoc()): ?>
              <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td colspan="4">
                  <form method="post" action="controls.php?section=categories">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                    <div class="grid-4">
                      <div><input type="text" name="name" value="<?php echo admin_h((string) $row['name']); ?>" required></div>
                      <div><input type="text" value="<?php echo admin_h((string) $row['slug']); ?>" disabled></div>
                      <div><label><input type="checkbox" name="is_active" <?php echo (int) $row['is_active'] === 1 ? 'checked' : ''; ?>> Active</label></div>
                      <div><small><?php echo admin_h((string) $row['created_at']); ?></small></div>
                    </div>
                    <div class="mt"><button class="btn btn-muted" type="submit">Update</button></div>
                  </form>
                </td>
                <td>
                  <form class="inline" method="post" action="controls.php?section=categories">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                    <button class="btn btn-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($section === 'products'): ?>
      <div class="panel">
        <h3>Add/Edit/Delete Products</h3>
        <form method="post" action="controls.php?section=products">
          <input type="hidden" name="action" value="create_product">
          <div class="grid-4">
            <div><input type="text" name="name" placeholder="Product name" required></div>
            <div><input type="text" name="sku" placeholder="SKU" required></div>
            <div>
              <select name="category_id">
                <option value="0">No category</option>
                <?php if ($categories): while ($c = $categories->fetch_assoc()): ?>
                  <option value="<?php echo (int) $c['id']; ?>"><?php echo admin_h((string) $c['name']); ?></option>
                <?php endwhile; $categories->data_seek(0); endif; ?>
              </select>
            </div>
            <div><input type="number" step="0.01" name="price" placeholder="Price" required></div>
            <div><input type="number" name="stock_qty" placeholder="Stock" value="0" required></div>
            <div><label><input type="checkbox" name="is_active" checked> Active</label></div>
          </div>
          <div class="mt"><textarea name="description" placeholder="Description"></textarea></div>
          <div class="mt"><button class="btn btn-primary" type="submit">Add Product</button></div>
        </form>
        <table>
          <thead><tr><th>ID</th><th>Name</th><th>SKU</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (!$products || $products->num_rows === 0): ?>
              <tr><td colspan="8">No products found.</td></tr>
            <?php else: while ($row = $products->fetch_assoc()): ?>
              <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td colspan="6">
                  <form method="post" action="controls.php?section=products">
                    <input type="hidden" name="action" value="update_product">
                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                    <div class="grid-4">
                      <div><input type="text" name="name" value="<?php echo admin_h((string) $row['name']); ?>" required></div>
                      <div><input type="text" name="sku" value="<?php echo admin_h((string) $row['sku']); ?>" required></div>
                      <div>
                        <select name="category_id">
                          <option value="0">No category</option>
                          <?php if ($categories): $categories->data_seek(0); while ($c = $categories->fetch_assoc()): ?>
                            <option value="<?php echo (int) $c['id']; ?>" <?php echo (int) $row['category_id'] === (int) $c['id'] ? 'selected' : ''; ?>>
                              <?php echo admin_h((string) $c['name']); ?>
                            </option>
                          <?php endwhile; endif; ?>
                        </select>
                      </div>
                      <div><input type="number" step="0.01" name="price" value="<?php echo admin_h((string) $row['price']); ?>" required></div>
                      <div><input type="number" name="stock_qty" value="<?php echo (int) $row['stock_qty']; ?>" required></div>
                      <div><label><input type="checkbox" name="is_active" <?php echo (int) $row['is_active'] === 1 ? 'checked' : ''; ?>> Active</label></div>
                    </div>
                    <div class="mt"><textarea name="description"><?php echo admin_h((string) $row['description']); ?></textarea></div>
                    <div class="mt"><button class="btn btn-muted" type="submit">Update</button></div>
                  </form>
                </td>
                <td>
                  <form class="inline" method="post" action="controls.php?section=products">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                    <button class="btn btn-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($section === 'users'): ?>
      <div class="panel">
        <h3>View All Users</h3>
        <form method="post" action="controls.php?section=users">
          <input type="hidden" name="action" value="create_user">
          <div class="grid-4">
            <div><input type="text" name="full_name" placeholder="Full name" required></div>
            <div><input type="email" name="email" placeholder="Email" required></div>
            <div><input type="text" name="phone" placeholder="Phone"></div>
            <div>
              <select name="status">
                <option value="active">Active</option>
                <option value="blocked">Blocked</option>
              </select>
            </div>
          </div>
          <div class="mt"><button class="btn btn-primary" type="submit">Add User</button></div>
        </form>
        <table>
          <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (!$users || $users->num_rows === 0): ?>
              <tr><td colspan="7">No users found.</td></tr>
            <?php else: while ($row = $users->fetch_assoc()): ?>
              <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td><?php echo admin_h((string) $row['full_name']); ?></td>
                <td><?php echo admin_h((string) $row['email']); ?></td>
                <td><?php echo admin_h((string) $row['phone']); ?></td>
                <td><?php echo admin_h((string) $row['status']); ?></td>
                <td><?php echo admin_h((string) $row['created_at']); ?></td>
                <td>
                  <form class="inline" method="post" action="controls.php?section=users">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                    <button class="btn btn-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($section === 'orders'): ?>
      <div class="panel">
        <h3>View/Manage Orders + Update Status</h3>
        <form method="post" action="controls.php?section=orders">
          <input type="hidden" name="action" value="create_order">
          <div class="grid-4">
            <div><input type="text" name="customer_name" placeholder="Customer name" required></div>
            <div><input type="email" name="customer_email" placeholder="Customer email" required></div>
            <div><input type="number" step="0.01" name="total_amount" placeholder="Total amount" required></div>
            <div>
              <select name="status">
                <option value="pending">pending</option>
                <option value="processing">processing</option>
                <option value="shipped">shipped</option>
                <option value="delivered">delivered</option>
                <option value="cancelled">cancelled</option>
              </select>
            </div>
          </div>
          <div class="mt"><button class="btn btn-primary" type="submit">Create Order</button></div>
        </form>
        <table>
          <thead><tr><th>Order #</th><th>Customer</th><th>Email</th><th>Total</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if (!$orders || $orders->num_rows === 0): ?>
              <tr><td colspan="7">No orders found.</td></tr>
            <?php else: while ($row = $orders->fetch_assoc()): ?>
              <tr>
                <td><?php echo admin_h((string) $row['order_number']); ?></td>
                <td><?php echo admin_h((string) $row['customer_name']); ?></td>
                <td><?php echo admin_h((string) $row['customer_email']); ?></td>
                <td><?php echo number_format((float) $row['total_amount'], 2); ?></td>
                <td>
                  <form class="inline" method="post" action="controls.php?section=orders">
                    <input type="hidden" name="action" value="update_order_status">
                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                    <select name="status">
                      <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $row['status'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn btn-muted mt" type="submit">Update</button>
                  </form>
                </td>
                <td><?php echo admin_h((string) $row['created_at']); ?></td>
                <td>
                  <form class="inline" method="post" action="controls.php?section=orders">
                    <input type="hidden" name="action" value="delete_order">
                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                    <button class="btn btn-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($section === 'stock'): ?>
      <div class="panel">
        <h3>Manage Stock</h3>
        <table>
          <thead><tr><th>ID</th><th>Product</th><th>SKU</th><th>Current Stock</th><th>Update</th></tr></thead>
          <tbody>
            <?php if (!$products || $products->num_rows === 0): ?>
              <tr><td colspan="5">No products found.</td></tr>
            <?php else: $products->data_seek(0); while ($row = $products->fetch_assoc()): ?>
              <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td><?php echo admin_h((string) $row['name']); ?></td>
                <td><?php echo admin_h((string) $row['sku']); ?></td>
                <td><?php echo (int) $row['stock_qty']; ?></td>
                <td>
                  <form class="inline" method="post" action="controls.php?section=stock">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                    <input type="number" name="stock_qty" value="<?php echo (int) $row['stock_qty']; ?>" style="width:110px;">
                    <button class="btn btn-primary" type="submit">Save</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($section === 'coupons'): ?>
      <div class="panel">
        <h3>Create Offers/Coupons</h3>
        <form method="post" action="controls.php?section=coupons">
          <input type="hidden" name="action" value="create_coupon">
          <div class="grid-4">
            <div><input type="text" name="code" placeholder="Coupon code" required></div>
            <div>
              <select name="discount_type">
                <option value="percent">percent</option>
                <option value="fixed">fixed</option>
              </select>
            </div>
            <div><input type="number" step="0.01" name="discount_value" placeholder="Discount value" required></div>
            <div><input type="number" name="usage_limit" placeholder="Usage limit"></div>
            <div><input type="date" name="start_date"></div>
            <div><input type="date" name="end_date"></div>
            <div><label><input type="checkbox" name="is_active" checked> Active</label></div>
          </div>
          <div class="mt"><button class="btn btn-primary" type="submit">Add Coupon</button></div>
        </form>
        <table>
          <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Start</th><th>End</th><th>Usage Limit</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (!$coupons || $coupons->num_rows === 0): ?>
              <tr><td colspan="8">No coupons found.</td></tr>
            <?php else: while ($row = $coupons->fetch_assoc()): ?>
              <tr>
                <td><?php echo admin_h((string) $row['code']); ?></td>
                <td><?php echo admin_h((string) $row['discount_type']); ?></td>
                <td><?php echo number_format((float) $row['discount_value'], 2); ?></td>
                <td><?php echo admin_h((string) $row['start_date']); ?></td>
                <td><?php echo admin_h((string) $row['end_date']); ?></td>
                <td><?php echo admin_h((string) $row['usage_limit']); ?></td>
                <td><?php echo (int) $row['is_active'] === 1 ? 'Active' : 'Inactive'; ?></td>
                <td>
                  <form class="inline" method="post" action="controls.php?section=coupons">
                    <input type="hidden" name="action" value="delete_coupon">
                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                    <button class="btn btn-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
