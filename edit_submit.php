<?php
session_start();
include('navbar.php');  
// 检查用户是否已登录，否则跳转到登录页
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// 检查是否提供了记录 ID
if (!isset($_GET['id'])) {
    echo "未提供记录 ID。";
    exit();
}

$record_id = $_GET['id'];

// 数据库连接
$db = new SQLite3('database2343/submit.db');

// 获取记录信息
$query = "SELECT * FROM records WHERE id = :id AND username = :username";
$stmt = $db->prepare($query);
if (!$stmt) {
    echo "SQL 错误: " . $db->lastErrorMsg();
    exit();
}
$stmt->bindValue(':id', $record_id, SQLITE3_INTEGER);
$stmt->bindValue(':username', $_SESSION['username'], SQLITE3_TEXT);
$result = $stmt->execute();
$record = $result->fetchArray(SQLITE3_ASSOC);

if (!$record) {
    echo "记录未找到或您无权修改此记录。";
    exit();
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $products = $_POST['product'];
    $quantities = $_POST['quantity'];
    $production_date = $_POST['production_date'];
    $remarks = $_POST['remarks'];

    // 删除旧的产品记录
    $query = "DELETE FROM records WHERE id = :id";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        echo "SQL 错误: " . $db->lastErrorMsg();
        exit();
    }
    $stmt->bindValue(':id', $record_id, SQLITE3_INTEGER);
    $stmt->execute();

    // 插入新的产品记录
    $query = "INSERT INTO records (id, username, product, quantity, production_date, submit_time, status, stage, remarks) VALUES (:id, :username, :product, :quantity, :production_date, :submit_time, '进行中', '等待审核', :remarks)";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        echo "SQL 错误: " . $db->lastErrorMsg();
        exit();
    }
    $submit_time = date('Y-m-d H:i:s');

    foreach ($products as $index => $product) {
        $stmt->bindValue(':id', $record_id, SQLITE3_INTEGER);
        $stmt->bindValue(':username', $_SESSION['username'], SQLITE3_TEXT);
        $stmt->bindValue(':product', $product, SQLITE3_TEXT);
        $stmt->bindValue(':quantity', $quantities[$index], SQLITE3_INTEGER);
        $stmt->bindValue(':production_date', $production_date, SQLITE3_TEXT);
        $stmt->bindValue(':submit_time', $submit_time, SQLITE3_TEXT);
        $stmt->bindValue(':remarks', $remarks, SQLITE3_TEXT);
        $stmt->execute();
    }

    // 设置成功消息
    $_SESSION['message'] = "记录已更新，等待管理员审核。";
    echo "<script>
            alert('记录已更新，等待管理员审核。');
            window.location.href = 'my_submit.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改记录</title>
    <a href="my_submit.php" class="btn-back">返回</a>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .product-entry {
            margin-bottom: 10px;
        }

        .quantity-remove {
            display: flex;
            align-items: center;
            margin-top: 5px;
        }

        input[type="number"] {
            width: 100px;
        }
    </style>
</head>
<body>
    <h1>修改记录</h1>
    <form method="POST">
        <div id="products-container">
            <?php
            // 将现有的产品和数量显示在表单中
            $products = explode(',', $record['product']);
            $quantities = explode(',', $record['quantity']);
            foreach ($products as $index => $product) {
                echo '<div class="product-entry">';
                echo '<label>产品: <input type="text" name="product[]" value="' . htmlspecialchars($product) . '" required></label>';
                echo '<div class="quantity-remove">';
                echo '<label>生产数量: <input type="number" name="quantity[]" value="' . htmlspecialchars($quantities[$index]) . '" required></label>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
        <br>
        <label for="production_date">生产日期:</label>
        <input type="date" id="production_date" name="production_date" value="<?php echo htmlspecialchars($record['production_date']); ?>" required>
        <br>
        <label for="remarks">备注:</label>
        <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
        <br>
        <label for="review_comments">审核批语:</label>
        <textarea id="review_comments" name="review_comments" readonly><?php echo htmlspecialchars($record['review_comments']); ?></textarea>
        <br>
        <button type="submit">更新记录</button>
    </form>
</body>
</html>