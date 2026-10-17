<?php
session_start();
require_once 'config/database.php';

$stmt = $pdo->query("
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
    WHERE posts.status = 'published'
    ORDER BY posts.created_at DESC
");

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Website Tin tức</title>
</head>

<body>

<h1>Website Tin tức / Cổng thông tin</h1>

<p>
    Đề tài 19 - DTC245200988
</p>

<hr>

<?php if (isset($_SESSION['user_id'])): ?>

    <p>
        Xin chào,
        <strong>
            <?= htmlspecialchars($_SESSION['full_name']) ?>
        </strong>

        |

        <a href="logout.php">
            Đăng xuất
        </a>

        <?php if ($_SESSION['role'] === 'admin'): ?>

            |

            <a href="admin/dashboard.php">
                Trang quản trị
            </a>

        <?php endif; ?>
    </p>

<?php else: ?>

    <p>
        <a href="login.php">
            Đăng nhập
        </a>

        |

        <a href="register.php">
            Đăng ký
        </a>
    </p>

<?php endif; ?>

<hr>

<form method="GET" action="search.php">

    <input
        type="text"
        name="q"
        placeholder="Tìm kiếm bài viết..."
        required
    >

    <button type="submit">
        Tìm kiếm
    </button>

</form>

<hr>

<h2>Tin mới nhất</h2>

<?php if (count($posts) === 0): ?>

    <p>
        Chưa có bài viết nào.
    </p>

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

            <?php if (!empty($post['image'])): ?>

                <p>
                    <img
                        src="uploads/<?= htmlspecialchars($post['image']) ?>"
                        alt="<?= htmlspecialchars($post['title']) ?>"
                        style="max-width:300px;"
                    >
                </p>

            <?php endif; ?>

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

</body>
</html>
