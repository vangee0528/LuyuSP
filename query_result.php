<?php
session_start();
include('navbar.php');

// 获取当前用户的角色
$user_role = $_SESSION['role'];

// 获取查询参数
$producer = isset($_POST['producer']) ? $_POST['producer'] : '';
$date = isset($_POST['date']) ? $_POST['date'] : '';

// 确保日期格式为 YYYY-MM-01
$date = date('Y-m-01', strtotime($date));

// 数据库连接
$db = new SQLite3('database2343/record.db');

// 查询数据库
$stmt = $db->prepare("SELECT * FROM records WHERE producer = :producer AND strftime('%Y-%m', date) = strftime('%Y-%m', :date)");
$stmt->bindValue(':producer', $producer, SQLITE3_TEXT);
$stmt->bindValue(':date', $date, SQLITE3_TEXT);

$result = $stmt->execute();

// 处理查询结果
$records = [];
$totalAmount = 0;

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $unitPrice = $row['unit_price'];
    $quantity = $row['quantity'];
    $amount = $unitPrice * $quantity;
    $row['amount'] = number_format($amount, 2); // 保留两位小数
    $totalAmount += $amount;
    $records[] = $row;
}

$db->close();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查询结果</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="header-container">
        <!-- 返回按钮 -->
        <?php
        // 根据用户角色设置返回链接
        if ($user_role == 'admin') {
            echo '<a href="admin_query.php" class="btn-back">返回查询</a>';
        } else {
            echo '<a href="user_query.php" class="btn-back">返回查询</a>';
        }
        ?>
        <!-- 标题 -->
        <h1>查询结果</h1>
    </div>

    <?php if (empty($records)): ?>
        <p>没有找到匹配的记录。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>生产编码</th>
                    <th>生产人</th>
                    <th>日期</th>
                    <th>产品名称</th>
                    <th>规格型号</th>
                    <th>生产单价</th>
                    <th>生产数量</th>
                    <th>应发金额</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['production_code']); ?></td>
                        <td><?php echo htmlspecialchars($record['producer']); ?></td>
                        <td><?php echo htmlspecialchars($record['date']); ?></td>
                        <td><?php echo htmlspecialchars($record['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['model']); ?></td>
                        <td><?php echo number_format($record['unit_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($record['quantity']); ?></td>
                        <td><?php echo $record['amount']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="7" style="text-align: right;">总金额：</td>
                    <td><?php echo number_format($totalAmount, 2); ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>
