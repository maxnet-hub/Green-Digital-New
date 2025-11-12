<?php
require_once 'config.php';

// ตรวจสอบว่าเป็นสมาชิก
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ดึงข้อมูลการจอง
$booking_sql = "SELECT b.*,
                (SELECT SUM(quantity) FROM booking_items WHERE booking_id = b.booking_id) as total_weight,
                (SELECT SUM(subtotal) FROM booking_items WHERE booking_id = b.booking_id) as calculated_total
                FROM bookings b
                WHERE b.booking_id = '$booking_id' AND b.user_id = '$user_id'";
$booking_result = mysqli_query($conn, $booking_sql);

if (!$booking_result || mysqli_num_rows($booking_result) == 0) {
    $_SESSION['error'] = 'ไม่พบการจองนี้';
    header("Location: bookings.php");
    exit();
}

$booking = mysqli_fetch_assoc($booking_result);

// ดึงรายการขยะในการจอง
$items_sql = "SELECT bi.*, rt.type_name, rt.category, rt.co2_reduction
              FROM booking_items bi
              JOIN recycle_types rt ON bi.type_id = rt.type_id
              WHERE bi.booking_id = '$booking_id'";
$items = mysqli_query($conn, $items_sql);

// คำนวณ CO2 ที่ช่วยลด
$total_co2 = 0;
if ($items) {
    mysqli_data_seek($items, 0);
    while ($item = mysqli_fetch_assoc($items)) {
        $total_co2 += $item['quantity'] * $item['co2_reduction'];
    }
    mysqli_data_seek($items, 0);
}

// ดึงข้อมูล transaction ถ้ามี
$transaction = null;
if ($booking['status'] == 'completed') {
    $trans_sql = "SELECT * FROM transactions WHERE booking_id = '$booking_id' LIMIT 1";
    $trans_result = mysqli_query($conn, $trans_sql);
    if ($trans_result && mysqli_num_rows($trans_result) > 0) {
        $transaction = mysqli_fetch_assoc($trans_result);
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการจอง #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?> - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .detail-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .status-timeline {
            position: relative;
            padding-left: 30px;
        }
        .status-timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #6c757d;
        }
        .timeline-item.active::before {
            background: #28a745;
        }
        .item-detail {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="bookings.php" class="btn btn-sm btn-outline-secondary">← กลับ</a>
        </div>

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2>การจอง #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></h2>
                        <p class="text-muted mb-0">
                            สร้างเมื่อ: <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?> น.
                        </p>
                    </div>
                    <div>
                        <?php if ($booking['status'] == 'pending'): ?>
                            <span class="badge bg-warning fs-5">รอดำเนินการ</span>
                        <?php elseif ($booking['status'] == 'confirmed'): ?>
                            <span class="badge bg-info fs-5">ยืนยันแล้ว</span>
                        <?php elseif ($booking['status'] == 'completed'): ?>
                            <span class="badge bg-success fs-5">เสร็จสิ้น</span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-5">ยกเลิก</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                <!-- Booking Info -->
                <div class="detail-card">
                    <h5 class="mb-3">📋 ข้อมูลการจอง</h5>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>📅 วันที่รับ:</strong><br>
                            <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?>
                        </div>
                        <div class="col-6">
                            <strong>⏰ เวลา:</strong><br>
                            <?php echo date('H:i', strtotime($booking['booking_time'])); ?> น.
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>📍 ที่อยู่รับขยะ:</strong><br>
                        <?php echo nl2br(htmlspecialchars($booking['pickup_address'])); ?>
                    </div>
                    <?php if ($booking['notes']): ?>
                        <div class="mb-0">
                            <strong>📝 หมายเหตุ:</strong><br>
                            <?php echo nl2br(htmlspecialchars($booking['notes'])); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Items -->
                <div class="detail-card">
                    <h5 class="mb-3">♻️ รายการขยะ</h5>
                    <?php if ($items && mysqli_num_rows($items) > 0): ?>
                        <?php
                        $total_subtotal = 0;
                        while ($item = mysqli_fetch_assoc($items)):
                            $total_subtotal += $item['subtotal'];
                        ?>
                            <div class="item-detail">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['type_name']); ?></h6>
                                        <span class="badge bg-secondary text-uppercase"><?php echo $item['category']; ?></span>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success"><?php echo number_format($item['price_per_kg'], 2); ?> ฿/kg</span>
                                    </div>
                                </div>
                                <div class="row text-center mt-2">
                                    <div class="col-4">
                                        <small class="text-muted">น้ำหนัก</small><br>
                                        <strong><?php echo number_format($item['quantity'], 2); ?> kg</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">ราคารวม</small><br>
                                        <strong class="text-success"><?php echo number_format($item['subtotal'], 2); ?> ฿</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">CO2 ลดได้</small><br>
                                        <strong class="text-info"><?php echo number_format($item['quantity'] * $item['co2_reduction'], 2); ?> kg</strong>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <!-- Summary -->
                        <div class="mt-3 pt-3 border-top">
                            <div class="row">
                                <div class="col-6">
                                    <h6>น้ำหนักรวม:</h6>
                                    <h4><?php echo number_format($booking['total_weight'], 2); ?> kg</h4>
                                </div>
                                <div class="col-6 text-end">
                                    <h6>ราคารวม:</h6>
                                    <h4 class="text-success"><?php echo number_format($total_subtotal, 2); ?> ฿</h4>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <span class="badge bg-info fs-6">🌱 ช่วยลด CO2: <?php echo number_format($total_co2, 2); ?> kg</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">ไม่พบรายการขยะ</p>
                    <?php endif; ?>
                </div>

                <!-- Transaction Info (if completed) -->
                <?php if ($transaction): ?>
                    <div class="detail-card">
                        <h5 class="mb-3">💳 ข้อมูลธุรกรรม</h5>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <strong>วันที่ทำธุรกรรม:</strong><br>
                                <?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?> น.
                            </div>
                            <div class="col-6 mb-2">
                                <strong>วิธีการชำระเงิน:</strong><br>
                                <?php
                                $payment_methods = [
                                    'cash' => 'เงินสด',
                                    'bank_transfer' => 'โอนเงิน',
                                    'promptpay' => 'พร้อมเพย์'
                                ];
                                echo $payment_methods[$transaction['payment_method']] ?? $transaction['payment_method'];
                                ?>
                            </div>
                            <div class="col-6 mb-2">
                                <strong>สถานะการชำระเงิน:</strong><br>
                                <?php if ($transaction['payment_status'] == 'paid'): ?>
                                    <span class="badge bg-success">ชำระแล้ว</span>
                                <?php elseif ($transaction['payment_status'] == 'pending'): ?>
                                    <span class="badge bg-warning">รอชำระ</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ล้มเหลว</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-6 mb-2">
                                <strong>จำนวนเงิน:</strong><br>
                                <span class="text-success fs-5"><?php echo number_format($transaction['total_amount'], 2); ?> ฿</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Status Timeline -->
                <div class="detail-card">
                    <h5 class="mb-3">📊 สถานะการดำเนินการ</h5>
                    <div class="status-timeline">
                        <div class="timeline-item <?php echo in_array($booking['status'], ['pending', 'confirmed', 'completed']) ? 'active' : ''; ?>">
                            <strong>รอดำเนินการ</strong><br>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></small>
                        </div>
                        <div class="timeline-item <?php echo in_array($booking['status'], ['confirmed', 'completed']) ? 'active' : ''; ?>">
                            <strong>ยืนยันแล้ว</strong><br>
                            <small class="text-muted">
                                <?php
                                if ($booking['status'] == 'confirmed' || $booking['status'] == 'completed') {
                                    echo date('d/m/Y H:i', strtotime($booking['updated_at']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </small>
                        </div>
                        <div class="timeline-item <?php echo $booking['status'] == 'completed' ? 'active' : ''; ?>">
                            <strong>เสร็จสิ้น</strong><br>
                            <small class="text-muted">
                                <?php
                                if ($booking['status'] == 'completed') {
                                    echo date('d/m/Y H:i', strtotime($booking['updated_at']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                    <div class="detail-card">
                        <h5 class="mb-3">⚙️ การจัดการ</h5>
                        <a href="booking_cancel_confirm.php?id=<?php echo $booking_id; ?>" class="btn btn-danger w-100">
                            ❌ ยกเลิกการจอง
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Environmental Impact -->
                <?php if ($booking['status'] == 'completed' && $total_co2 > 0): ?>
                    <div class="detail-card bg-success text-white">
                        <h5 class="mb-3">🌍 ผลกระทบต่อสิ่งแวดล้อม</h5>
                        <div class="text-center">
                            <h2 class="mb-2"><?php echo number_format($total_co2, 2); ?> kg</h2>
                            <p class="mb-2">CO2 ที่ช่วยลดได้</p>
                            <small>เทียบเท่าการปลูกต้นไม้ <?php echo number_format($total_co2 / 21.77, 1); ?> ต้น</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
