<?php
session_start();
include('navbar.php');  
// 检查用户是否已登录，否则跳转到登录页
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $products = $_POST['product'];
    $quantities = $_POST['quantity'];
    $production_date = $_POST['production_date'];
    $remarks = $_POST['remarks'];
    $submit_time = date('Y-m-d H:i:s');

    // 数据库连接
    $db = new SQLite3('database2343/submit.db');

    // 插入记录到数据库
    $query = "INSERT INTO records (username, product, quantity, production_date, submit_time, status, stage, remarks) VALUES (:username, :product, :quantity, :production_date, :submit_time, '进行中', '等待审核', :remarks)";
    $stmt = $db->prepare($query);

    foreach ($products as $index => $product) {
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':product', $product, SQLITE3_TEXT);
        $stmt->bindValue(':quantity', $quantities[$index], SQLITE3_INTEGER);
        $stmt->bindValue(':production_date', $production_date, SQLITE3_TEXT);
        $stmt->bindValue(':submit_time', $submit_time, SQLITE3_TEXT);
        $stmt->bindValue(':remarks', $remarks, SQLITE3_TEXT);
        $stmt->execute();
    }

    // 设置成功消息
    $_SESSION['message'] = "记录已提交，等待管理员审核。";
    echo "<script>
            alert('记录已提交，等待管理员审核。');
            window.location.href = 'user_dashboard.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>提交新的生产记录</title>
    <a href="user_dashboard.php" class="btn-back">返回主页</a>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        function addProduct() {
            var container = document.getElementById('products-container');
            var productDiv = document.createElement('div');
            productDiv.className = 'product-entry';
            productDiv.innerHTML = `
                <label>产品名称: <input type="text" name="product[]" required></label>
                <div class="quantity-remove">
                    <label>生产数量: <input type="number" name="quantity[]" required></label>
                    <button type="button" class="remove-btn" onclick="removeProduct(this)">移除</button>
                </div>
            `;
            container.appendChild(productDiv);
        }

        function removeProduct(button) {
            var productDiv = button.closest('.product-entry');
            productDiv.remove();
        }
    </script>
    <style>
        .product-entry {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .quantity-remove {
            display: flex;
            align-items: center;
            margin-left: 10px;
        }

        .remove-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 3px 6px;
            cursor: pointer;
            margin-left: 10px;
        }

        .remove-btn:hover {
            background-color: darkred;
        }

        .add-btn {
            background-color: blue;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }

        .add-btn:hover {
            background-color: darkblue;
        }

        input[type="number"], input[type="text"], input[type="date"], textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 1em;
        }

        textarea {
            height: 100px;
        }

        .btn-back, button[type="submit"] {
            width: 100%;
            padding: 10px 20px;
            margin-top: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }

        .btn-back {
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-back:hover {
            background-color: #45a049;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>提交生产记录</h1>
    <form method="POST">
        <label for="production_date">生产日期:</label>
        <input type="date" id="production_date" name="production_date" value="<?php echo date('Y-m-d'); ?>" required>
        <div id="products-container">
            <div class="product-entry">
                <label>产品名称: <input type="text" name="product[]" required></label>
                <div class="quantity-remove">
                    <label>生产数量: <input type="number" name="quantity[]" required></label>
                    <button type="button" class="remove-btn" onclick="removeProduct(this)">移除</button>
                </div>
            </div>
        </div>
        <button type="button" class="add-btn" onclick="addProduct()">添加产品+</button>
        <br>
        <label for="remarks">备注:</label>
        <textarea id="remarks" name="remarks"></textarea>
        <br>
        <button type="submit">提交</button>
    </form>
</body>
</html>