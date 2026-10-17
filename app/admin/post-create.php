<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';

$categoryStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $categoryId = (int) $_POST['category_id'];
    $status = $_POST['status'];

    $imageName = null;

    if ($title === '' || $content === '') {
        $message = 'Vui lòng nhập đầy đủ tiêu đề và nội dung.';
    } else {

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

            $allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            $fileType = mime_content_type($_FILES['image']['tmp_name']);

            if (!in_array($fileType, $allowedTypes, true)) {
                $message = 'Chỉ cho phép ảnh JPG, PNG hoặc WEBP.';
            } else {

                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

                $imageName = time() . '_' . uniqid() . '.' . strtolower($extension);

                $uploadPath = '../uploads/' . $imageName;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $message = 'Upload ảnh thất bại.';
                }
            }
        }

        if ($message === '') {

            $slug = strtolower(trim(
                preg_replace('/[^A-Za-z0-9-]+/', '-', $title)
            ));

            $stmt = $pdo->prepare("
                INSERT INTO posts
                (
                    title,
                    slug,
                    content,
                    image,
                    category_id,
                    user_id,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $title,
                $slug,
                $content,
                $imageName,
                $categoryId,
                $_SESSION['user_id'],
                $status
            ]);

            header('Location: posts.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm bài viết</title>
</head>

<body>

<h1>Thêm bài viết</h1>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Tiêu đề:</label><br>

    <input
        type="text"
        name="title"
        required
        style="width: 400px;"
    >

    <br><br>

    <label>Danh mục:</label><br>

    <select name="category_id" required>

        <?php foreach ($categories as $category): ?>

            <option value="<?= $category['id'] ?>">
                <?= htmlspecialchars($category['name']) ?>
            </option>

        <?php endforeach; ?>

    </select>

    <br><br>

    <label>Ảnh bài viết:</label><br>

    <input
        type="file"
        name="image"
        accept=".jpg,.jpeg,.png,.webp"
    >

    <br><br>

    <label>Nội dung:</label><br>

    <textarea
        name="content"
        rows="10"
        cols="70"
        required
    ></textarea>

    <br><br>

    <label>Trạng thái:</label><br>

    <select name="status">

        <option value="draft">
            Bản nháp
        </option>

        <option value="published">
            Xuất bản
        </option>

    </select>

    <br><br>

    <button type="submit">
        Lưu bài viết
    </button>

</form>

<br>

<a href="posts.php">
    ← Quay lại
</a>

</body>
</html>
