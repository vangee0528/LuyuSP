<?php
session_start();
include('navbar.php');  // 导入导航条（确保它存在）
// 检查用户是否为管理员，否则跳转到登录页
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// 连接到 user.db 数据库获取用户数据
$user_db = new SQLite3('database2343/user.db');

// 获取所有用户
$user_query = "SELECT username FROM users";
$user_result = $user_db->query($user_query);
if (!$user_result) {
    echo "SQL 错误: " . $user_db->lastErrorMsg();
    exit();
}
$users = [];
while ($row = $user_result->fetchArray(SQLITE3_ASSOC)) {
    $users[] = $row['username'];
}

// 连接到 submit.db 数据库处理提交记录
$db = new SQLite3('database2343/submit.db');

// 处理删除操作
if (isset($_POST['delete_record'])) {
    $record_id = $_POST['record_id'];
    $query = "DELETE FROM records WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $record_id, SQLITE3_INTEGER);
    $stmt->execute();
    header('Location: admin_submit_search.php');
    exit();
}

// 处理修改阶段操作
if (isset($_POST['update_stage'])) {
    $record_id = $_POST['record_id'];
    $new_stage = $_POST['new_stage'];
    $new_status = ($new_stage == '手动结束' || $new_stage == '通过') ? '结束' : '进行中';
    $query = "UPDATE records SET stage = :new_stage, status = :new_status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $record_id, SQLITE3_INTEGER);
    $stmt->bindValue(':new_stage', $new_stage, SQLITE3_TEXT);
    $stmt->bindValue(':new_status', $new_status, SQLITE3_TEXT);
    $stmt->execute();
    header('Location: admin_submit_search.php');
    exit();
}

// 设置分页参数
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 处理搜索功能
$search_username = isset($_GET['username']) ? $_GET['username'] : '';
$search_production_date = isset($_GET['production_date']) ? $_GET['production_date'] : '';

// 构建查询条件
$where_clauses = [];
if ($search_username) {
    $where_clauses[] = "username LIKE :username";
}
if ($search_production_date) {
    $where_clauses[] = "production_date = :production_date";
}
$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// 获取记录总数
$query = "SELECT COUNT(*) as count FROM records $where_sql";
$stmt = $db->prepare($query);
if ($search_username) {
    $stmt->bindValue(':username', '%' . $search_username . '%', SQLITE3_TEXT);
}
if ($search_production_date) {
    $stmt->bindValue(':production_date', $search_production_date, SQLITE3_TEXT);
}
$result = $stmt->execute();
if (!$result) {
    echo "SQL 错误: " . $db->lastErrorMsg();
    exit();
}
$row = $result->fetchArray(SQLITE3_ASSOC);
$total_records = $row['count'];
$total_pages = ceil($total_records / $limit);

// 获取记录
$query = "SELECT * FROM records $where_sql ORDER BY submit_time DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
if ($search_username) {
    $stmt->bindValue(':username', '%' . $search_username . '%', SQLITE3_TEXT);
}
if ($search_production_date) {
    $stmt->bindValue(':production_date', $search_production_date, SQLITE3_TEXT);
}
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
if (!$result) {
    echo "SQL 错误: " . $db->lastErrorMsg();
    exit();
}

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
    <title>提交记录查询</title>
    <!-- <link rel="stylesheet" href="assets/style.css"> -->
    <style>
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

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            padding: 20px;
        }

        .search-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        form {
            margin: 20px auto;
            padding: 20px;
            background-color: #f4f4f9;
            border-radius: 8px;
        }

        .custom-table {
            margin: 20px auto;
            padding: 20px;
            background-color: #f4f4f9;
            border-radius: 8px;
            max-width: 90%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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

        .btn-delete {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-right: 5px;
        }

        .btn-delete:hover {
            background-color: darkred;
        }

        .btn-update {
            background-color: blue;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        .btn-update:hover {
            background-color: darkblue;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
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
        function confirmDelete(recordId) {
            if (confirm('确定要删除这条记录吗？')) {
                document.getElementById('delete-form-' + recordId).submit();
            }
        }

        function confirmUpdate(recordId) {
            if (confirm('确定要修改这条记录的阶段吗？')) {
                document.getElementById('update-form-' + recordId).submit();
            }
        }
    </script>
</head>
<body>
    <h1>提交记录查询</h1>
    <a href="admin_approval.php" class="btn-back">返回审核</a>
    <a href="admin_dashboard.php" class="btn-back">返回dashboard</a>
    </a>
    <form method="GET" action="admin_submit_search.php" class="search-container">
        <label for="username">生产人:</label>
        <select id="username" name="username">
            <option value="">所有</option>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo htmlspecialchars($user); ?>" <?php echo ($user === $search_username) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($user); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="production_date">生产日期:</label>
        <input type="date" id="production_date" name="production_date" value="<?php echo htmlspecialchars($search_production_date); ?>">
        <button type="submit">搜索</button>
    </form>
    <div class="custom-table">
        <table>
            <thead>
                <tr>
                    <th>生产人</th>
                    <th>产品</th>
                    <th>数量</th>
                    <th>日期</th>
                    <th>备注</th>
                    <!-- <th>状态</th> -->
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($records) > 0): ?>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['username']); ?></td>
                            <td><?php echo htmlspecialchars($record['product']); ?></td>
                            <td><?php echo htmlspecialchars($record['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($record['production_date']); ?></td>
                            <td><?php echo htmlspecialchars($record['remarks']); ?></td>
                            <!-- <td class="status-<?php echo htmlspecialchars($record['status']); ?>"><?php echo htmlspecialchars($record['status']); ?></td> -->
                            <td class="stage-<?php echo htmlspecialchars($record['stage']); ?>"><?php echo htmlspecialchars($record['stage']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form id="delete-form-<?php echo $record['id']; ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                        <input type="hidden" name="delete_record" value="1">
                                        <button type="button" class="btn-delete" onclick="confirmDelete(<?php echo $record['id']; ?>)">删除</button>
                                    </form>
                                    <form id="update-form-<?php echo $record['id']; ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                        <select name="new_stage" required>
                                        
                                            <option value="等待审核">等待审核</option>
                                            <option value="通过">通过</option>
                                            <option value="不通过">不通过</option>
                                            <option value="手动结束">手动结束</option>
                                        </select>
                                        <input type="hidden" name="update_stage" value="1">
                                        <button type="button" class="btn-update" onclick="confirmUpdate(<?php echo $record['id']; ?>)">修改阶段</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">没有找到记录。</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&username=<?php echo urlencode($search_username); ?>&production_date=<?php echo urlencode($search_production_date); ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>