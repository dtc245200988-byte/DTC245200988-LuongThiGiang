<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('Không tìm thấy bài viết.');
}

$categoryStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $categoryId = (int) $_POST['category_id'];
    $status = $_POST['status'];

    $imageName = $post['image'];

    if ($title === '' || $content === '') {
        $message = 'Vui lòng nhập đầy đủ thông tin.';
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

                $newImageName = time() . '_' . uniqid() . '.' . strtolower($extension);

                $uploadPath = '../uploads/' . $newImageName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {

                    if (!empty($post['image'])) {
                        $oldImagePath = '../uploads/' . $post['image'];

                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    $imageName = $newImageName;

                } else {
                    $message = 'Upload ảnh mới thất bại.';
                }
            }
        }

        if ($message === '') {

            $slug = strtolower(trim(
                preg_replace('/[^A-Za-z0-9-]+/', '-', $title)
            ));

            $update = $pdo->prepare("
                UPDATE posts
                SET
                    title = ?,
                    slug = ?,
                    content = ?,
                    image = ?,
                    category_id = ?,
                    status = ?
                WHERE id = ?
            ");

            $update->execute([
                $title,
                $slug,
                $content,
                $imageName,
                $categoryId,
                $status,
                $id
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
    <title>Sửa bài viết</title>
</head>

<body>

<h1>Sửa bài viết</h1>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Tiêu đề:</label><br>

    <input
        type="text"
        name="title"
        value="<?= htmlspecialchars($post['title']) ?>"
        required
        style="width: 400px;"
    >

    <br><br>

    <label>Danh mục:</label><br>

    <select name="category_id" required>

        <?php foreach ($categories as $category): ?>

            <option
                value="<?= $category['id'] ?>"
                <?= $category['id'] == $post['category_id'] ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($category['name']) ?>
            </option>

        <?php endforeach; ?>

    </select>

    <br><br>

    <label>Ảnh hiện tại:</label><br>

    <?php if (!empty($post['image'])): ?>

        <img
            src="../uploads/<?= htmlspecialchars($post['image']) ?>"
            alt="Ảnh bài viết"
            style="max-width: 300px;"
        >

    <?php else: ?>

        <p>Chưa có ảnh.</p>

    <?php endif; ?>

    <br><br>

    <label>Chọn ảnh mới:</label><br>

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
    ><?= htmlspecialchars($post['content']) ?></textarea>

    <br><br>

    <label>Trạng thái:</label><br>

    <select name="status">

        <option
            value="draft"
            <?= $post['status'] === 'draft' ? 'selected' : '' ?>
        >
            Bản nháp
        </option>

        <option
            value="published"
            <?= $post['status'] === 'published' ? 'selected' : '' ?>
        >
            Xuất bản
        </option>

    </select>

    <br><br>

    <button type="submit">
        Cập nhật bài viết
    </button>

</form>

<br>

<a href="posts.php">
    ← Quay lại
</a>

</body>
</html>
