<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function open_database(): mysqli
{
    $conn = new mysqli(
        getenv('DB_HOST') ?: 'db',
        getenv('DB_USER') ?: 'webapp_user',
        getenv('DB_PASSWORD') ?: 'lab09_demo_password',
        getenv('DB_NAME') ?: 'db_lab08'
    );
    $conn->set_charset('utf8mb4');
    return $conn;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
