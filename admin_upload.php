<?php 
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include('navbar.php');
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>上传数据</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        // JavaScript 校验文件类型
        function validateFile() {
            // 获取文件输入元素
            var fileInput = document.getElementById('dataFile');
            var filePath = fileInput.value;

            // 获取文件扩展名
            var allowedExtensions = /(\.xlsx)$/i;

            // 如果文件格式不符合要求
            if (!allowedExtensions.exec(filePath)) {
                alert('请上传有效的 .xlsx 文件！');
                fileInput.value = '';  // 清空文件选择框
                return false;
            }
            return true;
        }
    </script>
      
</head>
<body>
    <h1>上传数据文件</h1>

    <form action="admin_upload.php" method="POST" enctype="multipart/form-data" onsubmit="return validateFile()">
        <label for="dataFile">选择要上传的Excel文件 (data.xlsx):</label><br>
        <input type="file" name="dataFile" id="dataFile" accept=".xlsx" required><br><br>

        <label for="clearData">清空原有数据：</label>
        <input type="checkbox" name="clearData" id="clearData" checked><br><br>

        <button type="submit">上传并处理</button>
    </form>

    <p>上传文件将替换当前的 data.xlsx 文件，并备份原文件为 data.xlsx.bak。</p>
</body>
</html>

<?php
// 处理上传数据逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dataFile'])) {
    $uploadedFile = $_FILES['dataFile'];
    $uploadDir = 'uploads/';
    $backupFile = 'data.xlsx.bak';
    $newFile = 'data.xlsx';

    // 检查文件是否上传成功
    if ($uploadedFile['error'] == 0) {
        // 检查上传文件类型
        $fileExtension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        if ($fileExtension !== 'xlsx') {
            echo "<script>alert('上传的文件必须是xlsx格式！');</script>";
        } else {
            // 备份原文件
            if (file_exists($newFile)) {
                rename($newFile, $backupFile);
            }

            // 移动新文件到目标位置
            $newFilePath = $uploadDir . $uploadedFile['name'];
            if (move_uploaded_file($uploadedFile['tmp_name'], $newFilePath)) {
                // 获取是否清空原有数据的选项
                $clearData = isset($_POST['clearData']) && $_POST['clearData'] == 'on';

                // 处理文件
                include('process_data.php');
                $processResult = processExcelData($newFilePath, $clearData);  // 传递是否清空数据的标志

                // 如果文件处理成功
                if ($processResult) {
                    echo "<script>alert('文件上传并处理成功！');</script>";
                } else {
                    echo "<script>alert('文件处理失败，请重试！');</script>";
                }
            } else {
                echo "<script>alert('文件上传失败，请重试！');</script>";
            }
        }
    } else {
        echo "<script>alert('没有选择文件或上传失败！');</script>";
    }
}
?>
