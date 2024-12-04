<?php
session_start();

// 检查用户是否为普通用户，否则跳转到登录页
if ($_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit();
}

// 引入导航栏
include('navbar.php');

// 数据库连接
$recordDb = new SQLite3('database2343/record.db');  // 确保数据库路径正确

// 获取当前用户的用户名
$username = $_SESSION['username'];

// 获取当前月份
$currentMonth = date('Y-m');

// 查询当前月份该用户的记录并计算总金额
$stmt = $recordDb->prepare("SELECT * FROM records WHERE producer = :producer AND strftime('%Y-%m', date) = :currentMonth");
$stmt->bindValue(':producer', $username, SQLITE3_TEXT);
$stmt->bindValue(':currentMonth', $currentMonth, SQLITE3_TEXT);
$result = $stmt->execute();

$income = 0;
$records = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $unitPrice = $row['unit_price'];
    $quantity = $row['quantity'];
    $amount = $unitPrice * $quantity;
    $income += $amount;
    $records[] = $row;
}

// 关闭数据库连接
$recordDb->close();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>主页</title>
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
    <h1>主页</h1>

    <div class="dashboard-content">
        <h2>欢迎，用户 <?php echo htmlspecialchars($_SESSION['username']); ?>！</h2>
        <p>您本月的总收入为：<?php echo number_format($income, decimals: 2); ?> 元，继续加油ヾ(◍°∇°◍)ﾉﾞ</p>
        
        <p>您可以在这里执行以下操作：</p>
        <ul>
            <li><a href="submit_record.php" class="btn">提交新的生产记录</a></li>
            <li><a href="my_submit.php" class="btn">查询审批结果</a></li> 
            <li><a href="user_query.php" class="btn">查询过去的生产记录</a></li>
            <li><a href="change_password.php" class="btn">修改账户密码</a></li>
            <li><a href="daily_update.php" class="btn">请求数据更新</a></li>
            <li><a href="logout.php" class="btn">退出登录</a></li>
        </ul>
    </div>
</body>
</html>