<?php
session_start();

// 检查用户是否已登录，如果已登录，自动导航到对应的 dashboard 页面
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin_dashboard.php');
        exit();
    } else {
        header('Location: user_dashboard.php');
        exit();
    }
}

include('navbar.php');
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首页</title>
    <link rel="stylesheet" href="assets/style.css"> <!-- 引入样式文件 -->

</head>
<body>

    <h1>欢迎使用炉羽饰品信息管理系统！</h1>
    <p>请登录或注册以开始使用系统。</p>

    <!-- 如果用户未登录，显示登录和注册链接 -->
    <?php if (!isset($_SESSION['username'])): ?>
        <p><a href="login.php">登录</a> 或 <a href="register.php">注册</a></p>
    <?php endif; ?>

</body>
</html>
