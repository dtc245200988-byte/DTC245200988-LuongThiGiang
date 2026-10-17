<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    die('Bạn không có quyền truy cập trang quản trị.');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>

<h1>Trang quản trị Website Tin tức</h1>

<p>
    Xin chào,
    <?= htmlspecialchars($_SESSION['full_name']) ?>
</p>

<p>Vai trò: Admin</p>

<ul>
    <li><a href="posts.php">Quản lý bài viết</a></li>
    <li><a href="categories.php">Quản lý danh mục</a></li>
    <li><a href="users.php">Quản lý người dùng</a></li>
</ul>

<a href="../logout.php">Đăng xuất</a>

</body>
</html>
