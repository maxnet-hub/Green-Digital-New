<?php
require_once '../config.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลสถิติ
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status='pending'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM transactions WHERE payment_status='paid'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Green Digital Admin</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="container mt-4">
        <h3 class="mb-4">📊 สถิติภาพรวม</h3>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card">
                    <h6>จำนวนสมาชิก</h6>
                    <div class="stat-number"><?php echo number_format($total_users); ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <h6>การจองทั้งหมด</h6>
                    <div class="stat-number info"><?php echo number_format($total_bookings); ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <h6>รอดำเนินการ</h6>
                    <div class="stat-number warning"><?php echo number_format($pending_bookings); ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <h6>รายได้รวม</h6>
                    <div class="stat-number secondary">฿<?php echo number_format($total_revenue, 2); ?></div>
                </div>
            </div>
        </div>

        <h3 class="mb-4">📋 เมนูจัดการ</h3>

        <!-- Menu Cards -->
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <a href="bookings.php" class="menu-card">
                    <div class="icon">📅</div>
                    <h5>จัดการการจอง</h5>
                    <?php if ($pending_bookings > 0): ?>
                        <span class="badge-notification"><?php echo $pending_bookings; ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="admins.php" class="menu-card">
                    <div class="icon">👨‍💼</div>
                    <h5>จัดการผู้ดูแล</h5>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="users.php" class="menu-card">
                    <div class="icon">👥</div>
                    <h5>จัดการสมาชิก</h5>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="recycle_types.php" class="menu-card">
                    <div class="icon">♻️</div>
                    <h5>ประเภทขยะ</h5>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="prices.php" class="menu-card">
                    <div class="icon">💰</div>
                    <h5>จัดการราคา</h5>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="transactions.php" class="menu-card">
                    <div class="icon">💳</div>
                    <h5>ธุรกรรม</h5>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="articles.php" class="menu-card">
                    <div class="icon">📚</div>
                    <h5>บทความ</h5>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="promotions.php" class="menu-card">
                    <div class="icon">🎁</div>
                    <h5>โปรโมชั่น</h5>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="reports.php" class="menu-card">
                    <div class="icon">📊</div>
                    <h5>รายงาน</h5>
                </a>
            </div>
        </div>
    </div>

    <script src="../css/bootstrap.bundle.min.js"></script>
</body>
</html>
