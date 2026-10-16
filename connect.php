<?php
require_once __DIR__ . '/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, (int) DB_PORT);
if ($conn->connect_error) {
    http_response_code(500);
    exit('Database connection failed.');
}
$conn->set_charset('utf8mb4');
