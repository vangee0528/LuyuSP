<?php
session_start();
session_destroy(); // 销毁会话
header('Location: login.php'); // 跳转到登录页面
exit();
?>
