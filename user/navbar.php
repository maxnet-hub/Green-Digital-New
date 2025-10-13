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
                    <a class="nav-link" href="bookings.php">📅 จองรับขยะ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="transactions.php">💳 ธุรกรรม</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="points.php">⭐ แต้มสะสม</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="articles.php">📰 บทความ</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">สวัสดี, <?php echo $_SESSION['full_name']; ?></span>
                <a href="profile.php" class="btn btn-light btn-sm me-2">โปรไฟล์</a>
                <a href="logout.php" class="btn btn-danger btn-sm">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</nav>
