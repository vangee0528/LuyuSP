<?php
session_start();
include('navbar.php');  
// 检查用户是否已登录，否则跳转到登录页
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// 数据库连接
$db = new SQLite3('database2343/submit.db');

// 处理结束操作
if (isset($_POST['end_record'])) {
    $record_id = $_POST['record_id'];
    $query = "UPDATE records SET status = '结束', stage = '手动结束' WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $record_id, SQLITE3_INTEGER);
    $stmt->execute();
    header('Location: my_submit.php');
    exit();
}

// 获取当前用户的提交记录总数
$username = $_SESSION['username'];
$query = "SELECT COUNT(*) as count FROM records WHERE username = :username";
$stmt = $db->prepare($query);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$total_records = $row['count'];

// 设置分页参数
$limit = 20;
$total_pages = ceil($total_records / $limit);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 获取当前用户的最新20条记录，优先展示进行中的记录
$query = "
    SELECT * FROM records 
    WHERE username = :username 
    ORDER BY 
        CASE status 
            WHEN '进行中' THEN 1 
            ELSE 2 
        END, 
        submit_time DESC 
    LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

$records = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $records[] = $row;
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的提交</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
            padding: 20px;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .status-进行中 {
            color: green;
        }

        .status-结束 {
            color: gray;
        }

        .stage-等待审核 {
            color: orange;
        }

        .stage-不通过 {
            color: red;
        }

        .stage-手动结束 {
            color: gray;
        }

        .btn-end {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-right: 5px;
        }

        .btn-end:hover {
            background-color: darkred;
        }

        .btn-edit {
            background-color: blue;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        .btn-edit:hover {
            background-color: darkblue;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        nav {
            background-color: #333;
            overflow: hidden;
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
        }

        nav li {
            margin: 0;
            padding: 0;
        }

        nav a {
            display: inline-block;
            padding: 14px 20px;
            color: white;
            text-align: center;
            text-decoration: none;
        }

        nav a:hover {
            background-color: #ddd;
            color: black;
        }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 16px;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #45a049;
        }

        .pagination .active {
            background-color: #45a049;
            pointer-events: none;
        }
        .btn-back {
    display: inline-block;
    width: 100%;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin: 20px;
}

.btn-back:hover {
    background-color: #45a049;
}

    </style>
    <script>
        function confirmEnd(recordId) {
            if (confirm('注意结束后无法恢复，确定要结束这条记录吗？')) {
                document.getElementById('end-form-' + recordId).submit();
            }
        }
    </script>
</head>
<body>
    <h1>我的提交记录</h1>
    <a href="user_dashboard.php" class="btn-back">返回主页</a>
    <table>
        <thead>
            <tr>
                <th>产品</th>
                <th>数量</th>
                <th>日期</th>
                <!-- <th>提交时间</th> -->
                <th>状态</th>
                <!-- <th>当前阶段</th> -->
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($records) > 0): ?>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['product']); ?></td>
                        <td><?php echo htmlspecialchars($record['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($record['production_date']); ?></td>
                        <!-- <td><?php echo htmlspecialchars($record['submit_time']); ?></td> -->
                        <!-- <td class="status-<?php echo htmlspecialchars($record['status']); ?>"><?php echo htmlspecialchars($record['status']); ?></td> -->
                        <td class="stage-<?php echo htmlspecialchars($record['stage']); ?>"><?php echo htmlspecialchars($record['stage']); ?></td>
                        <td>
                            <?php if ($record['status'] === '进行中'): ?>
                                <div class="action-buttons">
                                    <form id="end-form-<?php echo $record['id']; ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                        <input type="hidden" name="end_record" value="1">
                                        <button type="button" class="btn-end" onclick="confirmEnd(<?php echo $record['id']; ?>)">结束</button>
                                    </form>
                                    <a href="edit_submit.php?id=<?php echo $record['id']; ?>"><button class="btn-edit">修改</button></a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">没有找到提交记录。</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>