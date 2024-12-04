<?php
session_start();
include('navbar.php');



// 获取当前用户的用户名
$username = $_SESSION['username'];

?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查询您的生产记录</title>
    <link rel="stylesheet" href="assets/style.css"> <!-- 引入样式文件 -->
</head>
<body>

<h1>查询您的生产记录</h1>
<a href="user_dashboard.php" class="btn-back">返回主页</a>
<!-- 查询表单 -->
<form method="POST" action="query_result.php">
    <label for="producer">生产人：</label>
    <input type="text" name="producer" value="<?php echo htmlspecialchars($username); ?>" readonly><br>

    <label for="date">请选择要查询的日期：</label>
    <input type="month" name="date" required><br><br>

    <button type="submit">查询</button>
</form>

</body>
</html>
