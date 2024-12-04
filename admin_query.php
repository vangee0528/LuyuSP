<?php
session_start();
include('navbar.php');

// 只有管理员可以访问
if ($_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员查询数据</title>
    <link rel="stylesheet" href="assets/style.css"> <!-- 引入样式文件 -->
</head>
<body>

<h1>管理员查询生产记录</h1>

<!-- 查询表单 -->
<form method="POST" action="query_result.php">
    <label for="producer">生产人：</label>
    <input type="text" name="producer" required><br>

    <label for="date">日期：</label>
    <input type="month" name="date" required><br>

    <button type="submit">查询</button>
</form>

</body>
</html>
