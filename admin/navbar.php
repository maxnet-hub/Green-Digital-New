<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark dashboard-header sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">🌿 Green Digital</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">📊 Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookings.php">📅 จัดการการจอง</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">👥 จัดการสมาชิก</a>
                </li>
                <?php if ($_SESSION['role'] === 'super_admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="admins.php">👨‍💼 จัดการผู้ดูแล</a>
                </li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        ♻️ จัดการข้อมูล
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="recycle_types.php">ประเภทขยะ</a></li>
                        <li><a class="dropdown-item" href="prices.php">จัดการราคา</a></li>
                        <li><a class="dropdown-item" href="articles.php">บทความ</a></li>
                        <li><a class="dropdown-item" href="promotions.php">โปรโมชั่น</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="transactions.php">💳 ธุรกรรม</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">📊 รายงาน</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">สวัสดี, <?php echo $_SESSION['full_name']; ?></span>
                <a href="logout.php" class="btn btn-light btn-sm">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</nav>
