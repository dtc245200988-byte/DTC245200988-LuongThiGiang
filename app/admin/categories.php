<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

function createSlug($text)
{
    $text = mb_strtolower($text, 'UTF-8');

    $search = [
        'á','à','ả','ã','ạ','ă','ắ','ằ','ẳ','ẵ','ặ','â','ấ','ầ','ẩ','ẫ','ậ',
        'é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ',
        'í','ì','ỉ','ĩ','ị',
        'ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ',
        'ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự',
        'ý','ỳ','ỷ','ỹ','ỵ',
        'đ'
    ];

    $replace = [
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y',
        'd'
    ];

    $text = str_replace($search, $replace, $text);

    $text = preg_replace('/[^a-z0-9]+/', '-', $text);

    return trim($text, '-');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if ($name === '') {
        $message = 'Vui lòng nhập tên danh mục.';
    } else {
        $slug = createSlug($name);

        $check = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE name = ? OR slug = ?
            LIMIT 1
        ");

        $check->execute([$name, $slug]);

        if ($check->fetch()) {
            $message = 'Danh mục đã tồn tại.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, slug)
                VALUES (?, ?)
            ");

            $stmt->execute([$name, $slug]);

            header('Location: categories.php');
            exit;
        }
    }
}

$stmt = $pdo->query("
    SELECT id, name, slug, created_at
    FROM categories
    ORDER BY id DESC
");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý danh mục</title>
</head>
<body>

<h1>Quản lý danh mục</h1>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">

    <label>Tên danh mục:</label>

    <input
        type="text"
        name="name"
        required
    >

    <button type="submit">
        Thêm danh mục
    </button>

</form>

<br>

<table border="1" cellpadding="8" cellspacing="0">

    <tr>
        <th>ID</th>
        <th>Tên danh mục</th>
        <th>Slug</th>
        <th>Ngày tạo</th>
    </tr>

    <?php foreach ($categories as $category): ?>

        <tr>

            <td>
                <?= $category['id'] ?>
            </td>

            <td>
                <?= htmlspecialchars($category['name']) ?>
            </td>

            <td>
                <?= htmlspecialchars($category['slug']) ?>
            </td>

            <td>
                <?= htmlspecialchars($category['created_at']) ?>
            </td>

        </tr>

    <?php endforeach; ?>

</table>

<br>

<a href="dashboard.php">
    ← Quay lại Dashboard
</a>

</body>
</html>
