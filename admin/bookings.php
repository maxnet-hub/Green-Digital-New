<?php
require_once '../config.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

// ดึงรายการจองทั้งหมด
$where_conditions = ["1=1"];
$params = [];

// ตรวจสอบสิทธิ์: พนักงานเห็นเฉพาะงานที่ได้รับมอบหมาย
if ($_SESSION['role'] == 'staff') {
    $where_conditions[] = "b.assigned_to = ?";
    $params[] = $admin_id;
}

// Filter by status
if (isset($_GET['status']) && $_GET['status'] != '') {
    $where_conditions[] = "b.status = ?";
    $params[] = $_GET['status'];
}

// Filter by date
if (isset($_GET['date_from']) && $_GET['date_from'] != '') {
    $where_conditions[] = "b.booking_date >= ?";
    $params[] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && $_GET['date_to'] != '') {
    $where_conditions[] = "b.booking_date <= ?";
    $params[] = $_GET['date_to'];
}

// Filter by search
if (isset($_GET['search']) && $_GET['search'] != '') {
    $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.phone LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

$where_sql = implode(' AND ', $where_conditions);

// Build query with filters
$bookings_sql = "SELECT b.*,
                 u.first_name, u.last_name, u.phone, u.email,
                 a.full_name as assigned_name,
                 (SELECT SUM(quantity) FROM booking_items WHERE booking_id = b.booking_id) as total_weight,
                 (SELECT COUNT(*) FROM booking_items WHERE booking_id = b.booking_id) as items_count
                 FROM bookings b
                 JOIN users u ON b.user_id = u.user_id
                 LEFT JOIN admins a ON b.assigned_to = a.admin_id
                 WHERE $where_sql
                 ORDER BY b.booking_date DESC, b.booking_time DESC";

$stmt = $conn->prepare($bookings_sql);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$bookings = $stmt->get_result();

// นับจำนวนตามสถานะ
$status_counts = [];
if ($_SESSION['role'] == 'staff') {
    // พนักงานนับเฉพาะงานที่ได้รับมอบหมาย
    $status_sql = "SELECT status, COUNT(*) as count FROM bookings WHERE assigned_to = ? GROUP BY status";
    $stmt_status = $conn->prepare($status_sql);
    $stmt_status->bind_param('i', $admin_id);
    $stmt_status->execute();
    $status_result = $stmt_status->get_result();
} else {
    // แอดมินและเจ้าของร้านนับทั้งหมด
    $status_sql = "SELECT status, COUNT(*) as count FROM bookings GROUP BY status";
    $status_result = mysqli_query($conn, $status_sql);
}
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_counts[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการจอง - Green Digital Admin</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📅 จัดการการจอง</h2>
        </div>

        <!-- Status Summary -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <a href="?status=" class="text-decoration-none">
                    <div class="card text-center <?php echo !isset($_GET['status']) || $_GET['status'] == '' ? 'bg-primary text-white' : ''; ?>">
                        <div class="card-body">
                            <h3 class="mb-0"><?php echo number_format(array_sum($status_counts)); ?></h3>
                            <p class="mb-0">ทั้งหมด</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="?status=pending" class="text-decoration-none">
                    <div class="card text-center <?php echo isset($_GET['status']) && $_GET['status'] == 'pending' ? 'bg-warning text-dark' : ''; ?>">
                        <div class="card-body">
                            <h3 class="mb-0"><?php echo number_format($status_counts['pending'] ?? 0); ?></h3>
                            <p class="mb-0">รอดำเนินการ</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="?status=confirmed" class="text-decoration-none">
                    <div class="card text-center <?php echo isset($_GET['status']) && $_GET['status'] == 'confirmed' ? 'bg-info text-white' : ''; ?>">
                        <div class="card-body">
                            <h3 class="mb-0"><?php echo number_format($status_counts['confirmed'] ?? 0); ?></h3>
                            <p class="mb-0">ยืนยันแล้ว</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="?status=completed" class="text-decoration-none">
                    <div class="card text-center <?php echo isset($_GET['status']) && $_GET['status'] == 'completed' ? 'bg-success text-white' : ''; ?>">
                        <div class="card-body">
                            <h3 class="mb-0"><?php echo number_format($status_counts['completed'] ?? 0); ?></h3>
                            <p class="mb-0">เสร็จสิ้น</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="">ทุกสถานะ</option>
                            <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                            <option value="confirmed" <?php echo isset($_GET['status']) && $_GET['status'] == 'confirmed' ? 'selected' : ''; ?>>ยืนยันแล้ว</option>
                            <option value="completed" <?php echo isset($_GET['status']) && $_GET['status'] == 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                            <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">จากวันที่</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ถึงวันที่</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ค้นหา</label>
                        <input type="text" name="search" class="form-control" placeholder="ชื่อ, เบอร์โทร..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">🔍 ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="row">
            <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>
                <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="booking-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">
                                    <a href="booking_detail.php?id=<?php echo $booking['booking_id']; ?>" class="text-decoration-none">
                                        #<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?>
                                    </a>
                                </h5>
                                <small class="text-muted">
                                    📅 <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?>
                                    ⏰ <?php echo date('H:i', strtotime($booking['booking_time'])); ?> น.
                                </small>
                            </div>
                            <div>
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <span class="badge bg-warning status-badge">รอดำเนินการ</span>
                                <?php elseif ($booking['status'] == 'confirmed'): ?>
                                    <span class="badge bg-info status-badge">ยืนยันแล้ว</span>
                                <?php elseif ($booking['status'] == 'completed'): ?>
                                    <span class="badge bg-success status-badge">เสร็จสิ้น</span>
                                <?php else: ?>
                                    <span class="badge bg-danger status-badge">ยกเลิก</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-2">
                            <strong>👤 ลูกค้า:</strong> <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?><br>
                            <small class="text-muted">📞 <?php echo htmlspecialchars($booking['phone']); ?></small>
                        </div>

                        <div class="mb-2">
                            <strong>📍 ที่อยู่:</strong><br>
                            <small><?php echo nl2br(htmlspecialchars(substr($booking['pickup_address'], 0, 100))); ?><?php echo strlen($booking['pickup_address']) > 100 ? '...' : ''; ?></small>
                        </div>

                        <?php if ($booking['items_count']): ?>
                            <div class="mb-2">
                                <strong>♻️ รายการ:</strong> <?php echo $booking['items_count']; ?> รายการ
                                <?php if ($booking['total_weight']): ?>
                                    | <strong>⚖️</strong> <?php echo number_format($booking['total_weight'], 2); ?> kg
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($booking['estimated_price']): ?>
                            <div class="mb-3">
                                <strong>💰 ราคาประมาณ:</strong>
                                <span class="text-success fs-5"><?php echo number_format($booking['estimated_price'], 2); ?> ฿</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($booking['assigned_name']): ?>
                            <div class="mb-2">
                                <small class="text-muted">👨‍💼 ผู้รับผิดชอบ: <?php echo htmlspecialchars($booking['assigned_name']); ?></small>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2 mt-3">
                            <a href="booking_detail.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-primary flex-grow-1">
                                👁️ รายละเอียด
                            </a>
                            <?php if ($booking['status'] == 'pending'): ?>
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal<?php echo $booking['booking_id']; ?>">
                                    ✅ ยืนยัน
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Confirm Modal -->
                    <?php if ($booking['status'] == 'pending'): ?>
                    <div class="modal fade" id="confirmModal<?php echo $booking['booking_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">✅ ยืนยันการจอง</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>ยืนยันการจอง #<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></p>
                                    <p><strong>ลูกค้า:</strong> <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                                    <p><strong>วันที่:</strong> <?php echo date('d/m/Y H:i', strtotime($booking['booking_date'] . ' ' . $booking['booking_time'])); ?> น.</p>
                                </div>
                                <div class="modal-footer">
                                    <form method="POST" action="sql/booking_update_status.php" class="w-100">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                        <input type="hidden" name="status" value="confirmed">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                            <button type="submit" class="btn btn-success">✅ ยืนยันการจอง</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h5>ไม่พบข้อมูลการจอง</h5>
                        <p class="mb-0">ยังไม่มีการจองในระบบ หรือลองเปลี่ยนเงื่อนไขการค้นหา</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
