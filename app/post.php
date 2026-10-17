<?php
session_start();
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT
        posts.id,
        posts.title,
        posts.content,
        posts.image,
        posts.created_at,
        categories.name AS category_name,
        users.full_name AS author_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    LEFT JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
      AND posts.status = 'published'
    LIMIT 1
");

$stmt->execute([$id]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('Không tìm thấy bài viết.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['user_id'])) {
        $message = 'Bạn cần đăng nhập để bình luận.';
    } else {

        $commentContent = trim($_POST['comment']);

        if ($commentContent === '') {
            $message = 'Vui lòng nhập nội dung bình luận.';
        } else {

            $commentStmt = $pdo->prepare("
                INSERT INTO comments
                (content, post_id, user_id)
                VALUES (?, ?, ?)
            ");

            $commentStmt->execute([
                $commentContent,
                $id,
                $_SESSION['user_id']
            ]);

            header('Location: post.php?id=' . $id);
            exit;
        }
    }
}

$commentStmt = $pdo->prepare("
    SELECT
        comments.id,
        comments.content,
        comments.created_at,
        users.full_name
    FROM comments
    LEFT JOIN users
        ON comments.user_id = users.id
    WHERE comments.post_id = ?
    ORDER BY comments.created_at DESC
");

$commentStmt->execute([$id]);

$comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['title']) ?></title>
</head>

<body>

<p>
    <a href="index.php">
        ← Quay lại trang chủ
    </a>
</p>

<h1>
    <?= htmlspecialchars($post['title']) ?>
</h1>

<p>
    Danh mục:
    <strong>
        <?= htmlspecialchars($post['category_name'] ?? 'Không có') ?>
    </strong>

    |

    Tác giả:
    <?= htmlspecialchars($post['author_name'] ?? 'Không có') ?>

    |

    Ngày đăng:
    <?= htmlspecialchars($post['created_at']) ?>
</p>

<?php if (!empty($post['image'])): ?>

    <p>
        <img
            src="uploads/<?= htmlspecialchars($post['image']) ?>"
            alt="<?= htmlspecialchars($post['title']) ?>"
            style="max-width: 500px;"
        >
    </p>

<?php endif; ?>

<hr>

<div>
    <?= nl2br(htmlspecialchars($post['content'])) ?>
</div>

<hr>

<h2>Bình luận</h2>

<?php if ($message): ?>

    <p>
        <?= htmlspecialchars($message) ?>
    </p>

<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>

    <form method="POST">

        <textarea
            name="comment"
            rows="4"
            cols="60"
            placeholder="Nhập bình luận..."
            required
        ></textarea>

        <br><br>

        <button type="submit">
            Gửi bình luận
        </button>

    </form>

<?php else: ?>

    <p>
        Bạn cần
        <a href="login.php">
            đăng nhập
        </a>
        để bình luận.
    </p>

<?php endif; ?>

<hr>

<h3>Danh sách bình luận</h3>

<?php if (count($comments) === 0): ?>

    <p>
        Chưa có bình luận nào.
    </p>

<?php else: ?>

    <?php foreach ($comments as $comment): ?>

        <div>

            <p>
                <strong>
                    <?= htmlspecialchars($comment['full_name'] ?? 'Người dùng') ?>
                </strong>

                -

                <?= htmlspecialchars($comment['created_at']) ?>
            </p>

            <p>
                <?= nl2br(
                    htmlspecialchars(
                        $comment['content']
                    )
                ) ?>
            </p>

        </div>

        <hr>

    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>
