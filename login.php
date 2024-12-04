<?php
session_start();
include('navbar.php');  

// 初始化错误信息
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']);  // 清除 session 中的错误信息

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // 获取用户输入
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 禁止管理员账号登录
    if ($username === 'admin') {
        $_SESSION['error_message'] = '管理员账号禁止通过此页面登录，请使用管理员登录页面';
        header('Location: admin_login.php');
        exit();
    }

    // 数据库连接
    $db = new SQLite3('database2343/user.db');  

    // 查找用户
    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        // 输出错误信息
        echo "SQL 错误: " . $db->lastErrorMsg();
        exit();
    }
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);  // 获取结果

    // 验证用户名和密码
    if ($user && password_verify($password, $user['password'])) {
        // 登录成功，设置 session 变量
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // 根据用户角色跳转
        if ($user['role'] == 'admin') {
            header('Location: admin_dashboard.php');  // 管理员跳转
        } else {
            header('Location: user_dashboard.php');  // 普通用户跳转
        }
        exit();
    } else {
        // 登录失败，设置错误信息
        $_SESSION['error_message'] = '用户名或密码错误';
        header('Location: login.php');  // 重定向回登录页面
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录</title>
    <link rel="stylesheet" href="assets/style.css"> 
    <script>
        window.onload = function() {
            <?php if ($errorMessage): ?>
                alert("错误: <?php echo $errorMessage; ?>");
            <?php endif; ?>
        }
    </script>
</head>
<body>

    <h1>用户登录</h1>

    <!-- 登录表单 -->
    <form method="POST">
        <input type="text" name="username" placeholder="用户名" required><br>
        <input type="password" name="password" placeholder="密码" required><br>
        <button type="submit" name="login">登录</button>
    </form>

    <p>还没有账号？<a href="register.php">点击这里注册</a></p>
    <p>管理员？<a href="admin_login.php">点击这里登录管理员账号</a></p>  <!-- 添加管理员登录链接 -->
    <p>忘记密码？请联系管理员重置</p>

</body>
</html>