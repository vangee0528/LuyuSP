<?php
session_start();
include('navbar.php');  // 导入导航条（确保它存在）
// 检查用户是否为管理员，否则跳转到登录页
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// 数据库连接
$db = new SQLite3('database2343/submit.db');

// 处理审核操作
if (isset($_POST['approve_record'])) {
    $record_id = $_POST['record_id'];
    $review_comments = $_POST['review_comments'];
    $query = "UPDATE records SET status = '结束', stage = '通过', review_comments = :review_comments WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $record_id, SQLITE3_INTEGER);
    $stmt->bindValue(':review_comments', $review_comments, SQLITE3_TEXT);
    $stmt->execute();
    header('Location: admin_approval.php');
    exit();
}

if (isset($_POST['reject_record'])) {
    $record_id = $_POST['record_id'];
    $review_comments = $_POST['review_comments'];
    $query = "UPDATE records SET stage = '不通过', review_comments = :review_comments WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $record_id, SQLITE3_INTEGER);
    $stmt->bindValue(':review_comments', $review_comments, SQLITE3_TEXT);
    $stmt->execute();
    header('Location: admin_approval.php');
    exit();
}

// 获取所有待审核的记录
$query = "SELECT * FROM records WHERE stage = '等待审核' ORDER BY submit_time DESC";
$result = $db->query($query);

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
    <title>审批记录</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
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

        .btn-approve {
            background-color: green;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-right: 5px;
        }

        .btn-approve:hover {
            background-color: darkgreen;
        }

        .btn-reject {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        .btn-reject:hover {
            background-color: darkred;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }
    </style>
    <script>
        function confirmApprove(recordId) {
            if (confirm('确定要将此记录标记为通过吗？')) {
                document.getElementById('approve-form-' + recordId).submit();
            }
        }

        function confirmReject(recordId) {
            if (confirm('确定要将此记录标记为不通过吗？')) {
                document.getElementById('reject-form-' + recordId).submit();
            }
        }
    </script>
</head>
<body>
    <h1>审批记录</h1>
    <a href="admin_submit_search.php" class="btn">高级选项</a> 
    <a href="admin_dashboard.php" class="btn">返回dashboard</a>
    <table>
        <thead>
            <tr>
                <th>生产人</th>
                <th>产品</th>
                <th>数量</th>
                <th>日期</th>
                <th>备注</th>
                <th>状态</th>
                <!-- <th>当前阶段</th> -->
                <!-- <th>审核批语</th> -->
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
                        <!-- <td><?php echo htmlspecialchars($record['review_comments']); ?></td> -->
                        <td>
                            <div class="action-buttons">
                                <form id="approve-form-<?php echo $record['id']; ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                    <input type="hidden" name="approve_record" value="1">
                                    <input type="text" name="review_comments" placeholder="审核批语" required>
                                    <button type="button" class="btn-approve" onclick="confirmApprove(<?php echo $record['id']; ?>)">通过</button>
                                </form>
                                <form id="reject-form-<?php echo $record['id']; ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                    <input type="hidden" name="reject_record" value="1">
                                    <input type="text" name="review_comments" placeholder="审核批语" required>
                                    <button type="button" class="btn-reject" onclick="confirmReject(<?php echo $record['id']; ?>)">不通过</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">全部审批完成！</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>