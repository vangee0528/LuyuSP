<?php
session_start();

// 检查用户是否为管理员，否则跳转到登录页
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// 引入导航栏
include('navbar.php');
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员 Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .dashboard-content ul {
            display: flex;
            flex-wrap: wrap;
            list-style-type: none;
            padding: 0;
            justify-content: space-between; /* 在行之间添加间距 */
        }

        .dashboard-content li {
            flex: 1 1 45%; /* 每行两个按钮 */
            margin: 10px;
        }

        .dashboard-content .btn {
            display: block;
            width: calc(100% - 20px); /* 使按钮稍短 */
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .dashboard-content .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>管理员 Dashboard</h1>

    <div class="dashboard-content">
        <h2>欢迎，管理员 <?php echo htmlspecialchars($_SESSION['username']); ?>！</h2>
        
        <p>您可以在这里执行以下操作：</p>
        <ul>
            <li><a href="admin_approval.php" class="btn">开始审批</a></li>
            <li><a href="admin_submit_search.php" class="btn">审批高级设置</a> </li>
            
            <li><a href="admin_user.php" class="btn">管理用户账户</a></li>
            <li><a href="admin_query.php" class="btn">查询生产记录</a></li>
           
            <li><a href="admin_upload.php" class="btn">上传数据文件</a></li>
            <li><a href="daily_update.php" class="btn">数据更新</a></li>
        </ul>
    </div>

</body>
</html>