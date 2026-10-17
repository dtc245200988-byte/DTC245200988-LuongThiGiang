<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Chỉ cho phép Admin truy cập
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Bạn không có quyền truy cập trang này.');
}

$message = '';
$error = '';

// Xóa người dùng
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // Không cho admin tự xóa chính mình
    if ($id === (int) ($_SESSION['user']['id'] ?? 0)) {
        $error = 'Không thể xóa tài khoản đang đăng nhập.';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            $message = 'Xóa người dùng thành công.';
        } catch (PDOException $e) {
            $error = 'Không thể xóa người dùng.';
        }
    }
}

// Cập nhật role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $role = $_POST['role'] ?? 'user';

    if (!in_array($role, ['admin', 'user'], true)) {
        $error = 'Vai trò không hợp lệ.';
    } elseif ($id === (int) ($_SESSION['user']['id'] ?? 0) && $role !== 'admin') {
        $error = 'Không thể tự hạ quyền tài khoản Admin đang đăng nhập.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $id]);

            $message = 'Cập nhật vai trò thành công.';
        } catch (PDOException $e) {
            $error = 'Không thể cập nhật vai trò.';
        }
    }
}

// Lấy danh sách người dùng
$stmt = $pdo->query("
    SELECT id, full_name, username, email, role, created_at
    FROM users
    ORDER BY id DESC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng</title>
</head>

<body>

<h1>Quản lý người dùng</h1>

<p>
    Xin chào,
    <strong>
        <?= htmlspecialchars($_SESSION['user']['full_name'] ?? $_SESSION['user']['username']) ?>
    </strong>
</p>

<?php if ($message): ?>
    <p style="color: green;">
        <?= htmlspecialchars($message) ?>
    </p>
<?php endif; ?>

<?php if ($error): ?>
    <p style="color: red;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

<table border="1" cellpadding="8" cellspacing="0">

    <thead>
        <tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Tên đăng nhập</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Ngày tạo</th>
            <th>Thao tác</th>
        </tr>
    </thead>

    <tbody>

    <?php if (empty($users)): ?>

        <tr>
            <td colspan="7">Chưa có người dùng.</td>
        </tr>

    <?php else: ?>

        <?php foreach ($users as $user): ?>

            <tr>

                <td>
                    <?= (int) $user['id'] ?>
                </td>

                <td>
                    <?= htmlspecialchars($user['full_name']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($user['username']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($user['email'] ?? '') ?>
                </td>

                <td>

                    <form method="POST" style="display:inline;">

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $user['id'] ?>"
                        >

                        <select name="role">

                            <option
                                value="user"
                                <?= $user['role'] === 'user' ? 'selected' : '' ?>
                            >
                                User
                            </option>

                            <option
                                value="admin"
                                <?= $user['role'] === 'admin' ? 'selected' : '' ?>
                            >
                                Admin
                            </option>

                        </select>

                        <button
                            type="submit"
                            name="update_role"
                        >
                            Cập nhật
                        </button>

                    </form>

                </td>

                <td>
                    <?= htmlspecialchars($user['created_at']) ?>
                </td>

                <td>

                    <?php if ((int) $user['id'] === (int) ($_SESSION['user']['id'] ?? 0)): ?>

                        Tài khoản hiện tại

                    <?php else: ?>

                        <a
                            href="users.php?delete=<?= (int) $user['id'] ?>"
                            onclick="return confirm('Bạn có chắc muốn xóa người dùng này?');"
                        >
                            Xóa
                        </a>

                    <?php endif; ?>

                </td>

            </tr>

        <?php endforeach; ?>

    <?php endif; ?>

    </tbody>

</table>

<br>

<a href="dashboard.php">← Quay lại Dashboard</a>

<br><br>

<a href="../index.php">← Quay lại trang chủ</a>

</body>
</html>
