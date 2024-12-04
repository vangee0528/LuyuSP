<?php
session_start();
include('navbar.php');  // 导入导航条（确保它存在）

// 初始化错误信息
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']);  // 清除 session 中的错误信息

// 处理管理员登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    // 获取管理员输入
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 数据库连接
    $db = new SQLite3('database2343/user.db');  // 确保数据库路径正确

    // 查找管理员
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND role = 'admin'");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);  // 获取结果

    // 验证用户名和密码
    if ($user && password_verify($password, $user['password'])) {
        // 登录成功，设置 session 变量
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // 管理员登录成功，跳转到管理员 Dashboard
        header('Location: admin_dashboard.php');
        exit();
    } else {
        // 登录失败，设置错误信息
        $_SESSION['error_message'] = '用户名或密码错误';
        header('Location: admin_login.php');  // 重定向回管理员登录页面
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
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

    <h1>管理员登录</h1>

    <!-- 管理员登录表单 -->
    <form method="POST">
        <input type="text" name="username" placeholder="用户名" required><br>
        <input type="password" name="password" placeholder="密码" required><br>
        <button type="submit" name="admin_login">登录</button>
    </form>

    <p>返回普通用户登录？<a href="login.php">点击这里</a></p>

</body>
</html>
