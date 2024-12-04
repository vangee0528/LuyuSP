<?php
date_default_timezone_set('Asia/Shanghai');

// 用于输出日志
function logMessage($message) {
    $logFile = __DIR__ . "logs/update_log.txt"; // 指定日志文件路径
    $logEntry = date('Y/m/d H:i:s') . " - " . $message . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// 处理下载并保存文件到服务器
function downloadFile($url, $savePath) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟随重定向
    $data = curl_exec($ch);

    if (curl_errno($ch)) {
        logMessage("下载失败: " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    // 将下载的数据写入指定文件
    file_put_contents($savePath, $data);
    logMessage("文件已保存至: " . $savePath);
    return true;
}

// 引入 PhpSpreadsheet 类库
require 'vendor/autoload.php'; // 确保你已经通过 Composer 安装了 PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

// 处理 Excel 文件并更新数据库
function processExcelData($filePath, $clearData) {
    try {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 获取数据的行数和列数
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // 连接数据库（假设你使用 SQLite）
        $db = new SQLite3('database2343/record.db');
        $db->exec('CREATE TABLE IF NOT EXISTS records (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            production_code TEXT,
            producer TEXT,
            date TEXT,
            product_name TEXT,
            model TEXT,
            unit_price REAL,
            quantity INTEGER
        )');

        // 如果需要清空原有数据
        if ($clearData) {
            $db->exec("DELETE FROM records");
        }

        // 获取列标题（假设第一行是标题）
        $columns = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $columns[$col] = $sheet->getCell($col . '1')->getValue(); // 第一行是列名
        }

        // 逐行读取数据并插入数据库
        for ($row = 2; $row <= $highestRow; $row++) {
            $data = [];

            foreach ($columns as $col => $colName) {
                // 获取列值并存储到关联数组中
                $data[$colName] = $sheet->getCell($col . $row)->getValue();
            }

            // 确保日期格式正确
            $date = date('Y-m-d', strtotime($data['日期']));

            // 插入数据到数据库
            $stmt = $db->prepare("INSERT INTO records (production_code, producer, date, product_name, model, unit_price, quantity)
                VALUES (:production_code, :producer, :date, :product_name, :model, :unit_price, :quantity)");
            $stmt->bindValue(':production_code', $data['生产编码'], SQLITE3_TEXT);
            $stmt->bindValue(':producer', $data['生产人'], SQLITE3_TEXT);
            $stmt->bindValue(':date', $date, SQLITE3_TEXT);
            $stmt->bindValue(':product_name', $data['产品名称'], SQLITE3_TEXT);
            $stmt->bindValue(':model', $data['规格型号'], SQLITE3_TEXT);
            $stmt->bindValue(':unit_price', $data['生产单价'], SQLITE3_FLOAT);
            $stmt->bindValue(':quantity', $data['生产数量'], SQLITE3_INTEGER);
            $stmt->execute();
        }

        // 关闭数据库连接
        $db->close();

        return true; // 数据处理成功
    } catch (Exception $e) {
        logMessage("数据处理失败: " . $e->getMessage());
        return false; // 数据处理失败
    }
}

// 处理更新请求
if (isset($_GET['action']) && $_GET['action'] === 'update') {
    header('Content-Type: application/json');
    
    $url = "https://getnewdata.1658775112.workers.dev/";  // 目标 URL
    $savePath = __DIR__ . "/data.xlsx";  // 在服务器上保存文件的路径
    
    // 下载文件到服务器
    if (downloadFile($url, $savePath)) {
        logMessage("文件下载成功");

        // 处理文件并更新数据库
        $clearData = true; // 如果需要清空原有数据，可以设置为 true
        if (processExcelData($savePath, $clearData)) {
            echo json_encode(['success' => true, 'message' => "文件更新成功，数据已保存到数据库。"]);
        } else {
            echo json_encode(['success' => false, 'message' => "数据处理失败。"]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "文件下载失败。"]);
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据更新工具</title>
    <link rel="stylesheet" href="assets/style.css"> <!-- 引入样式文件 -->

</head>
<body>
    <h1>数据更新工具</h1>
    <div class="alert">请不要频繁更新</div>
    <a href="index.php" class="btn-back">返回</a>
    <div id="log" class="log loading">正在开始更新，请稍候...</div>

    <script>
        function logMessage(message, type = 'loading') {
            const logDiv = document.getElementById('log');
            logDiv.innerHTML = ""; // 清空之前的内容
            logDiv.innerHTML += message + "\n\n"; // 在两条日志之间添加换行
            logDiv.className = type;
        }

        function startUpdate() {
            logMessage("正在更新，请稍候...", 'loading');
            fetch('daily_update.php?action=update')
                .then(response => response.json())
                .then(data => {
                    logMessage("更新完成：" + data.message, data.success ? 'success' : 'error');
                })
                .catch(error => {
                    logMessage("请求出错：" + error.message, 'error');
                });
        }

        // 自动开始更新
        startUpdate();
    </script>
</body>
</html>