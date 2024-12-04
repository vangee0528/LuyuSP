<nav class="navbar">
    <ul class="navbar-list">
        <li><a href="index.php">首页</a></li>
        <li><a href="register.php">注册</a></li>
        <li><a href="login.php">登录</a></li>
      

        <?php if (isset($_SESSION['role'])): ?>
            <!-- 登录后显示用户 Dashboard 链接 -->
            <?php if ($_SESSION['role'] == 'user'): ?>
                <li><a href="user_dashboard.php">主页</a></li>
            <?php elseif ($_SESSION['role'] == 'admin'): ?>
                <li><a href="admin_dashboard.php">管理</a></li>
            <?php endif; ?>
            <!-- 退出登录 -->
            <li><a href="logout.php">退出</a></li>
        <?php endif; ?>
    </ul>
</nav>