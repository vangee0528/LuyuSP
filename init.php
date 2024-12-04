<?php
// 定义重置码
$reset_user_code = 'resetuser';
$reset_submit_code = 'resetsubmit';

// 检查是否提交了表单
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取用户输入的重置码
    $input_code = $_POST['reset_code'];

    // 验证重置码并执行相应的初始化操作
    if ($input_code === $reset_user_code) {
        // 如果 user.db 文件已存在，先删除它
        if (file_exists('database2343/user.db')) {
            unlink('database2343/user.db'); // 删除数据库文件
            echo "原有的 user.db 文件已被删除。\n";
        }

        // 创建或连接到新的 SQLite 数据库 user.db
        $user_db = new SQLite3('database2343/user.db');

        // 创建用户表
        $query = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL,
            income REAL DEFAULT 0
        )";
        $user_db->exec($query);

        // 插入管理员账号（密码使用 password_hash 加密）
        $admin_password_hash = password_hash('admin', PASSWORD_DEFAULT);
        $query = "INSERT OR IGNORE INTO users (username, password, role, income) VALUES ('admin', :password, 'admin', 0)";
        $stmt = $user_db->prepare($query);
        if (!$stmt) {
            echo "SQL 错误: " . $user_db->lastErrorMsg();
            exit();
        }
        $stmt->bindValue(':password', $admin_password_hash, SQLITE3_TEXT);
        $stmt->execute();

        echo "user.db 数据库初始化完成，管理员用户已添加。\n";
    } elseif ($input_code === $reset_submit_code) {
        // 如果 submit.db 文件已存在，先删除它
        if (file_exists('database2343/submit.db')) {
            unlink('database2343/submit.db'); // 删除数据库文件
            echo "原有的 submit.db 文件已被删除。\n";
        }

        // 创建或连接到新的 SQLite 数据库 submit.db
        $submit_db = new SQLite3('database2343/submit.db');

        // 创建记录表
        $query = "CREATE TABLE IF NOT EXISTS records (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            product TEXT NOT NULL,
            quantity REAL NOT NULL,
            production_date TEXT NOT NULL,
            submit_time TEXT NOT NULL,
            status TEXT NOT NULL,
            stage TEXT NOT NULL,
            review_comments TEXT,
            remarks TEXT
        )";
        $submit_db->exec($query);

        echo "submit.db 数据库初始化完成，记录表已添加。\n";
    } else {
        echo "无效的重置码。\n";
        exit();
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据库重置</title>
    <link rel="stylesheet" href="style.css"> <!-- 引入样式文件 -->
</head>
<body>
    <h1>数据库重置</h1>
    <form method="POST">
        <label for="reset_code">请输入重置码:</label>
        <input type="text" id="reset_code" name="reset_code" required>
        <button type="submit">重置数据库</button>
    </form>
</body>
</html>