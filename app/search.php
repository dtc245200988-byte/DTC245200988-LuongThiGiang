<?php
session_start();
require_once 'config/database.php';

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

$posts = [];

if ($keyword !== '') {
    $stmt = $pdo->prepare("
        SELECT
            posts.id,
            posts.title,
            posts.content,
            posts.created_at,
            categories.name AS category_name,
            users.full_name AS author_name
        FROM posts
        LEFT JOIN categories ON posts.category_id = categories.id
        LEFT JOIN users ON posts.user_id = users.id
        WHERE posts.status = 'published'
          AND (
                posts.title LIKE ?
                OR posts.content LIKE ?
                OR categories.name LIKE ?
              )
        ORDER BY posts.created_at DESC
    ");

    $searchTerm = '%' . $keyword . '%';

    $stmt->execute([
        $searchTerm,
        $searchTerm,
        $searchTerm
    ]);

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm bài viết</title>
</head>
<body>

<h1>Tìm kiếm bài viết</h1>

<form method="GET" action="search.php">

    <input
        type="text"
        name="q"
        placeholder="Nhập từ khóa..."
        value="<?= htmlspecialchars($keyword) ?>"
        required
    >

    <button type="submit">
        Tìm kiếm
    </button>

</form>

<hr>

<?php if ($keyword !== ''): ?>

    <h2>
        Kết quả tìm kiếm cho:
        "<?= htmlspecialchars($keyword) ?>"
    </h2>

    <?php if (count($posts) === 0): ?>

        <p>Không tìm thấy bài viết phù hợp.</p>

    <?php else: ?>

        <?php foreach ($posts as $post): ?>

            <article>

                <h2>
                    <a href="post.php?id=<?= $post['id'] ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h2>

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

                <p>
                    <?php
                    $preview = mb_substr(
                        strip_tags($post['content']),
                        0,
                        180,
                        'UTF-8'
                    );

                    echo htmlspecialchars($preview);
                    ?>
                    ...
                </p>

                <p>
                    <a href="post.php?id=<?= $post['id'] ?>">
                        Xem chi tiết →
                    </a>
                </p>

            </article>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>

<?php endif; ?>

<p>
    <a href="index.php">
        ← Quay lại trang chủ
    </a>
</p>

</body>
</html>
