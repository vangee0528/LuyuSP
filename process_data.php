<?php
// 引入 PhpSpreadsheet 类库
require 'vendor/autoload.php'; // 确保你已经通过 Composer 安装了 PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

function processExcelData($filePath, $clearData) {
    // 读取 Excel 文件
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
        return false; // 数据处理失败
    }
}
?>