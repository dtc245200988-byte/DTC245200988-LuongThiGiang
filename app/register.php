<?php
require_once 'config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($fullName === '' || $username === '' || $email === '' || $password === '') {
        $message = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);

        if ($check->fetch()) {
            $message = 'Tên đăng nhập hoặc email đã tồn tại.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, username, email, password, role)
                VALUES (?, ?, ?, ?, 'user')
            ");

            $stmt->execute([
                $fullName,
                $username,
                $email,
                $hashedPassword
            ]);

            $message = 'Đăng ký thành công!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
</head>
<body>

<h2>Đăng ký tài khoản</h2>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">

    <label>Họ và tên:</label><br>
    <input type="text" name="full_name" required>
    <br><br>

    <label>Tên đăng nhập:</label><br>
    <input type="text" name="username" required>
    <br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required>
    <br><br>

    <label>Mật khẩu:</label><br>
    <input type="password" name="password" required>
    <br><br>

    <button type="submit">Đăng ký</button>

</form>

<p>
    Đã có tài khoản?
    <a href="login.php">Đăng nhập</a>
</p>

</body>
</html>
