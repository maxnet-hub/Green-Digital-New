<?php
require_once '../config.php';

// ตรวจสอบว่าเป็น Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// ดึงข้อมูล Admin
$admin_sql = "SELECT * FROM admins WHERE admin_id = '$admin_id'";
$admin_result = mysqli_query($conn, $admin_sql);
$admin = mysqli_fetch_assoc($admin_result);

// ฟิลเตอร์
$where_conditions = [];
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

if (!empty($filter_status)) {
    $where_conditions[] = "rr.status = '$filter_status'";
}

if (!empty($filter_search)) {
    $where_conditions[] = "(CONCAT(u.first_name, ' ', u.last_name) LIKE '%$filter_search%' OR u.phone LIKE '%$filter_search%' OR r.reward_name LIKE '%$filter_search%')";
}

if (!empty($filter_date_from)) {
    $where_conditions[] = "DATE(rr.redemption_date) >= '$filter_date_from'";
}

if (!empty($filter_date_to)) {
    $where_conditions[] = "DATE(rr.redemption_date) <= '$filter_date_to'";
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// ดึงประวัติการแลกทั้งหมด
$redemptions_sql = "SELECT rr.*, r.reward_name, r.category,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name, u.phone as user_phone,
                    a.full_name as admin_name
                    FROM reward_redemptions rr
                    JOIN rewards r ON rr.reward_id = r.reward_id
                    JOIN users u ON rr.user_id = u.user_id
                    LEFT JOIN admins a ON rr.redeemed_by = a.admin_id
                    $where_clause
                    ORDER BY rr.redemption_date DESC";
$redemptions = mysqli_query($conn, $redemptions_sql);

// สรุปสถิติ
$stats_sql = "SELECT
              COUNT(*) as total_redemptions,
              SUM(total_points) as total_points_used,
              SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
              SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
              FROM reward_redemptions rr
              $where_clause";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการแลกของรางวัล - Green Digital Admin</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .stats-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }
        .stats-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }
        .stats-card p {
            margin: 0;
            opacity: 0.9;
        }
        .redemption-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>📋 ประวัติการแลกของรางวัล</h3>
            <a href="reward_redeem_for_user.php" class="btn btn-success">🎁 แลกของให้ลูกค้า</a>
        </div>

        <!-- สถิติ -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <p>รายการทั้งหมด</p>
                    <h3><?= number_format($stats['total_redemptions'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <p>แต้มที่ใช้ทั้งหมด</p>
                    <h3><?= number_format($stats['total_points_used'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <p>เสร็จสิ้น</p>
                    <h3><?= number_format($stats['completed_count'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <p>ยกเลิก</p>
                    <h3><?= number_format($stats['cancelled_count'] ?? 0) ?></h3>
                </div>
            </div>
        </div>

        <!-- ฟิลเตอร์ -->
        <div class="filter-section">
            <h5 class="mb-3">🔍 ค้นหา/ฟิลเตอร์</h5>
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">ค้นหา (ชื่อลูกค้า/เบอร์โทร/ของรางวัล)</label>
                        <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filter_search) ?>" placeholder="ค้นหา...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="completed" <?= $filter_status == 'completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                            <option value="cancelled" <?= $filter_status == 'cancelled' ? 'selected' : '' ?>>ยกเลิก</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">วันที่เริ่มต้น</label>
                        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filter_date_from) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">วันที่สิ้นสุด</label>
                        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filter_date_to) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">🔍 ค้นหา</button>
                            <a href="redemption_history.php" class="btn btn-secondary">ล้าง</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ตารางข้อมูล -->
        <div class="redemption-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>รหัส</th>
                            <th>วันที่/เวลา</th>
                            <th>ลูกค้า</th>
                            <th>ของรางวัล</th>
                            <th>จำนวน</th>
                            <th>แต้มที่ใช้</th>
                            <th>วิธีรับ</th>
                            <th>พนักงาน</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($redemptions) > 0): ?>
                            <?php while($rd = mysqli_fetch_assoc($redemptions)): ?>
                                <tr>
                                    <td>#<?= $rd['redemption_id'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($rd['redemption_date'])) ?></td>
                                    <td>
                                        <?= htmlspecialchars($rd['user_name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($rd['user_phone']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($rd['reward_name']) ?></td>
                                    <td><?= $rd['quantity'] ?> ชิ้น</td>
                                    <td><span class="badge bg-warning text-dark"><?= number_format($rd['total_points']) ?></span></td>
                                    <td>
                                        <?php if($rd['delivery_method'] == 'delivery'): ?>
                                            🚚 จัดส่ง
                                        <?php else: ?>
                                            🏪 รับเอง
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($rd['admin_name']): ?>
                                            <?= htmlspecialchars($rd['admin_name']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($rd['status'] == 'completed'): ?>
                                            <span class="badge bg-success">เสร็จสิ้น</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">ยกเลิก</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <p class="text-muted mb-0">ไม่พบข้อมูล</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3 text-muted">
            <small>รายการทั้งหมด: <?= mysqli_num_rows($redemptions) ?> รายการ</small>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
