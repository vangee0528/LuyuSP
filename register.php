<?php
session_start();
include('navbar.php');  // 导入导航条（确保它存在）

// 初始化错误信息
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']);  // 清除 session 中的错误信息

// 处理注册请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // 获取用户输入
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // 数据库连接
    $db = new SQLite3('database2343/user.db');  // 确保数据库路径正确

    // 检查用户名是否为 "admin"
    if ($username === 'admin') {
        $_SESSION['error_message'] = '用户名不能是 "admin"';
        header('Location: register.php');
        exit();
    }

    // 检查用户名是否已存在
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    if ($result->fetchArray()) {
        $_SESSION['error_message'] = '用户名已存在';
        header('Location: register.php');
        exit();
    }

    // 检查密码是否一致
    if ($password !== $confirmPassword) {
        $_SESSION['error_message'] = '两次密码输入不一致';
        header('Location: register.php');
        exit();
    }

    // 密码加密
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 插入新用户
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
    $stmt->execute();

    // 注册成功，弹窗提示并跳转到登录页面
    echo "<script>
            alert('注册成功，请登录！');
            window.location.href = 'login.php';  // 跳转到登录页面
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册</title>
    <link rel="stylesheet" href="assets/style.css"> <!-- 引入样式文件 -->
    <script>
        window.onload = function() {
            <?php if ($errorMessage): ?>
                alert("错误: <?php echo $errorMessage; ?>");
            <?php endif; ?>
        }
    </script>
</head>
<body>

    <h1>用户注册</h1>

    <!-- 注册表单 -->
    <form method="POST">
        <input type="text" name="username" placeholder="用户名" required><br>
        <input type="password" name="password" placeholder="密码" required><br>
        <input type="password" name="confirm_password" placeholder="确认密码" required><br>
        <button type="submit" name="register">注册</button>
    </form>

    <p>已有账号？<a href="login.php">点击这里登录</a></p>

</body>
</html>
