<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'fms_ecommerce';
const DB_USER = 'root';
const DB_PASS = '';

function get_db_connection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $connection = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($connection->connect_errno) {
        http_response_code(500);
        exit('Database connection failed. Please check db.php settings.');
    }

    $connection->set_charset('utf8mb4');
    return $connection;
}
