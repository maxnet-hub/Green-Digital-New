<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $base_url; ?>index.php">🌱 Green Digital</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>index.php">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'articles') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>articles.php">บทความ</a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>user/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>user/sql/logout.php">ออกจากระบบ</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'login') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>user_login.php">เข้าสู่ระบบ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-success px-3 ms-2" href="<?php echo $base_url; ?>user_register.php">สมัครสมาชิก</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
