<?php
session_start();
include('navbar.php');

// 检查用户是否为管理员
if ($_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// 数据库连接
$userDb = new SQLite3('database2343/user.db');
$recordDb = new SQLite3('database2343/record.db');

// 查询所有用户
$query = "SELECT id, username, role FROM users";
$results = $userDb->query($query);

// 检查查询是否成功
if (!$results) {
    die("查询失败: " . $userDb->lastErrorMsg());
}

// 处理密码重置
if (isset($_GET['reset_password']) && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // 重置密码为1234
    $new_password = password_hash('1234', PASSWORD_DEFAULT);

    // 更新密码
    $update_query = "UPDATE users SET password = :new_password WHERE id = :user_id";
    $stmt = $userDb->prepare($update_query);
    $stmt->bindValue(':new_password', $new_password, SQLITE3_TEXT);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->execute();

    // 提示密码已重置
    $success_message = "密码已重置为1234";
}

// 获取当前月份
$currentMonth = date('Y-m');

?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员 - 管理用户</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>管理用户</h1>

    <?php if (isset($success_message)): ?>
        <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>用户名</th>
                <th>权限</th>
                <th>当月收入</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 确保 $results 不为空并且能够正确读取
            if ($results) {
                // 循环遍历查询结果
                while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                    // 计算用户当月收入
                    $stmt = $recordDb->prepare("SELECT SUM(quantity * unit_price) as total_income FROM records WHERE producer = :producer AND strftime('%Y-%m', date) = :currentMonth");
                    $stmt->bindValue(':producer', $row['username'], SQLITE3_TEXT);
                    $stmt->bindValue(':currentMonth', $currentMonth, SQLITE3_TEXT);
                    $incomeResult = $stmt->execute();
                    $incomeRow = $incomeResult->fetchArray(SQLITE3_ASSOC);
                    $income = $incomeRow['total_income'] ? $incomeRow['total_income'] : 0;

                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['role']) . '</td>';
                    echo '<td>' . htmlspecialchars(number_format($income, 2)) . '</td>';
                    echo '<td>';
                    echo '<a href="admin_user.php?reset_password=true&user_id=' . $row['id'] . '" class="btn-reset" onclick="return confirm(\'确定要重置密码为1234吗？\')">重置密码</a>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4">没有找到用户</td></tr>';
            }
            ?>
        </tbody>
    </table>

</body>
</html>

<?php
$userDb->close();
$recordDb->close();
?>