<?php
session_start();
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit();
}

// 获取用户输入的生产人和日期
$producer = $_POST['producer'];
$date = $_POST['date']; // 格式为 YYYY-MM

// 打开数据库连接
$db = new SQLite3('database2343/record.db');

// 查询数据库
$stmt = $db->prepare("SELECT * FROM records WHERE 生产人 = :producer AND strftime('%Y-%m', 日期) = :date");
$stmt->bindValue(':producer', $producer, SQLITE3_TEXT);
$stmt->bindValue(':date', $date, SQLITE3_TEXT);
$result = $stmt->execute();

// 获取查询结果
$records = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $records[] = $row;
}

if (count($records) > 0) {
    // 如果有记录，显示结果
    echo "<h2>查询结果</h2>";
    echo "<table border='1'>
            <thead>
                <tr>
                    <th>生产编码</th>
                    <th>生产人</th>
                    <th>日期</th>
                    <th>产品名称</th>
                    <th>规格型号</th>
                    <th>生产单价</th>
                    <th>生产数量</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($records as $record) {
        echo "<tr>
                <td>{$record['生产编码']}</td>
                <td>{$record['生产人']}</td>
                <td>{$record['日期']}</td>
                <td>{$record['产品名称']}</td>
                <td>{$record['规格型号']}</td>
                <td>{$record['生产单价']}</td>
                <td>{$record['生产数量']}</td>
              </tr>";
    }
    
    echo "</tbody></table>";
} else {
    // 如果没有找到记录，提示信息
    header('Location: query_data_form.php?status=no_results');
    exit();
}
?>
