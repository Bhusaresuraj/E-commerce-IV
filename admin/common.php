<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_user_id'])) {
    header('Location: login.php');
    exit;
}

function admin_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function admin_redirect(string $path, array $query = []): void
{
    $url = $path;
    if ($query !== []) {
        $url .= '?' . http_build_query($query);
    }

    header('Location: ' . $url);
    exit;
}

function admin_flash_set(string $message, string $type = 'success'): void
{
    $_SESSION['admin_flash'] = ['message' => $message, 'type' => $type];
}

function admin_flash_get(): ?array
{
    if (!isset($_SESSION['admin_flash']) || !is_array($_SESSION['admin_flash'])) {
        return null;
    }

    $flash = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
    return $flash;
}

function ensure_admin_tables(mysqli $db): void
{
    $queries = [
        "CREATE TABLE IF NOT EXISTS categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(140) NOT NULL UNIQUE,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            email VARCHAR(180) NOT NULL UNIQUE,
            google_id VARCHAR(191) NULL UNIQUE,
            phone VARCHAR(50) DEFAULT '',
            avatar_url VARCHAR(255) DEFAULT '',
            status ENUM('active','blocked') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login_at TIMESTAMP NULL DEFAULT NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS products (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NULL,
            name VARCHAR(180) NOT NULL,
            sku VARCHAR(80) NOT NULL UNIQUE,
            image_path VARCHAR(255) DEFAULT '',
            description TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            stock_qty INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS orders (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(40) NOT NULL UNIQUE,
            user_id INT UNSIGNED NULL,
            customer_name VARCHAR(150) NOT NULL,
            customer_email VARCHAR(180) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS coupons (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(60) NOT NULL UNIQUE,
            discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
            discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            start_date DATE NULL,
            end_date DATE NULL,
            usage_limit INT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
    ];

    foreach ($queries as $query) {
        $db->query($query);
    }

    $columnCheck = $db->query("SHOW COLUMNS FROM products LIKE 'image_path'");
    if ($columnCheck instanceof mysqli_result && $columnCheck->num_rows === 0) {
        $db->query("ALTER TABLE products ADD COLUMN image_path VARCHAR(255) DEFAULT '' AFTER sku");
    }
    if ($columnCheck instanceof mysqli_result) {
        $columnCheck->free();
    }

    $userGoogleIdCheck = $db->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    if ($userGoogleIdCheck instanceof mysqli_result && $userGoogleIdCheck->num_rows === 0) {
        $db->query("ALTER TABLE users ADD COLUMN google_id VARCHAR(191) NULL AFTER email");
        $db->query("ALTER TABLE users ADD UNIQUE KEY uniq_google_id (google_id)");
    }
    if ($userGoogleIdCheck instanceof mysqli_result) {
        $userGoogleIdCheck->free();
    }

    $userAvatarCheck = $db->query("SHOW COLUMNS FROM users LIKE 'avatar_url'");
    if ($userAvatarCheck instanceof mysqli_result && $userAvatarCheck->num_rows === 0) {
        $db->query("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) DEFAULT '' AFTER phone");
    }
    if ($userAvatarCheck instanceof mysqli_result) {
        $userAvatarCheck->free();
    }

    $userLastLoginCheck = $db->query("SHOW COLUMNS FROM users LIKE 'last_login_at'");
    if ($userLastLoginCheck instanceof mysqli_result && $userLastLoginCheck->num_rows === 0) {
        $db->query("ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULL DEFAULT NULL AFTER created_at");
    }
    if ($userLastLoginCheck instanceof mysqli_result) {
        $userLastLoginCheck->free();
    }

    $seedCategory = $db->query('SELECT id FROM categories LIMIT 1');
    if ($seedCategory && $seedCategory->num_rows === 0) {
        $db->query("INSERT INTO categories (name, slug, is_active) VALUES ('General', 'general', 1)");
    }
    if ($seedCategory instanceof mysqli_result) {
        $seedCategory->free();
    }
}

function make_slug(string $text): string
{
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
    $slug = trim($slug, '-');
    if ($slug === '') {
        return 'item-' . time();
    }
    return $slug;
}
