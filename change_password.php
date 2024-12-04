<?php
session_start();
include('navbar.php');

// 检查用户是否已登录
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // 如果用户没有登录，跳转到登录页面
    exit();
}

$username = $_SESSION['username']; // 当前用户的用户名

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 简单的密码验证
    if ($new_password !== $confirm_password) {
        $error_message = "两次输入密码不一致";
    } else {
        // 连接数据库
        $db = new SQLite3('database2343/user.db');
        
        // 获取当前用户的旧密码
        $stmt = $db->prepare("SELECT password FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        // 检查旧密码是否正确
        if ($row && password_verify($old_password, $row['password'])) {
            // 密码正确，更新为新密码
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // 安全存储密码
            $update_stmt = $db->prepare("UPDATE users SET password = :new_password WHERE username = :username");
            $update_stmt->bindValue(':new_password', $hashed_password, SQLITE3_TEXT);
            $update_stmt->bindValue(':username', $username, SQLITE3_TEXT);
            
            if ($update_stmt->execute()) {
                $success_message = "密码修改成功";
                // 跳转到 Dashboard 页面
                header('Location: user_dashboard.php');
            } else {
                $error_message = "密码修改失败，请稍后再试";
            }
        } else {
            $error_message = "旧密码错误";
        }

        $db->close();
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改密码</title>
    <link rel="stylesheet" href="assets/style.css">
    
</head>
<body>

<h1>修改密码</h1>
<a href="user_dashboard.php" class="btn-back">返回主页</a>
<?php if (isset($error_message)): ?>
    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
<?php endif; ?>

<?php if (isset($success_message)): ?>
    <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
<?php endif; ?>

<form method="POST" action="change_password.php">
    <label for="old_password">旧密码：</label>
    <input type="password" name="old_password" required><br>

    <label for="new_password">新密码：</label>
    <input type="password" name="new_password" required><br>

    <label for="confirm_password">确认新密码：</label>
    <input type="password" name="confirm_password" required><br>

    <button type="submit" class="btn-submit">确认修改</button>
</form>

</body>
</html>
