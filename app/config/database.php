<?php

$host = 'mysql';
$dbname = 'news_db';
$username = 'news_user';
$password = 'news123';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}
