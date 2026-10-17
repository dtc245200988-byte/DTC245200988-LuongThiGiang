<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->query("
    SELECT 
        posts.id,
        posts.title,
        posts.status,
        posts.created_at,
        categories.name AS category_name,
        users.full_name AS author_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    LEFT JOIN users ON posts.user_id = users.id
    ORDER BY posts.created_at DESC
");

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý bài viết</title>
</head>
<body>

<h1>Quản lý bài viết</h1>

<p>
    <a href="post-create.php">+ Thêm bài viết mới</a>
</p>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Tiêu đề</th>
        <th>Danh mục</th>
        <th>Tác giả</th>
        <th>Trạng thái</th>
        <th>Ngày tạo</th>
        <th>Thao tác</th>
    </tr>

    <?php foreach ($posts as $post): ?>
        <tr>
            <td><?= $post['id'] ?></td>
            <td><?= htmlspecialchars($post['title']) ?></td>
            <td><?= htmlspecialchars($post['category_name'] ?? 'Không có') ?></td>
            <td><?= htmlspecialchars($post['author_name'] ?? 'Không có') ?></td>
            <td><?= htmlspecialchars($post['status']) ?></td>
            <td><?= htmlspecialchars($post['created_at']) ?></td>
            <td>
                <a href="post-edit.php?id=<?= $post['id'] ?>">Sửa</a>
                |
                <a href="post-delete.php?id=<?= $post['id'] ?>"
                   onclick="return confirm('Bạn có chắc muốn xóa bài viết này?')">
                    Xóa
                </a>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<br>

<a href="dashboard.php">← Quay lại Dashboard</a>

</body>
</html>
