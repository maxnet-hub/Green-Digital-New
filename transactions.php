<?php
require_once 'config.php';

// ตรวจสอบว่าเป็นสมาชิก
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลสมาชิก
$user_sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);

// ดึงข้อมูลธุรกรรมทั้งหมดของผู้ใช้
$transactions_sql = "SELECT t.*,
                     b.booking_id, b.booking_date, b.pickup_address,
                     (SELECT SUM(quantity) FROM booking_items WHERE booking_id = t.booking_id) as items_count
                     FROM transactions t
                     LEFT JOIN bookings b ON t.booking_id = b.booking_id
                     WHERE t.user_id = '$user_id'
                     ORDER BY t.created_at DESC";
$transactions = mysqli_query($conn, $transactions_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติธุรกรรม - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .transaction-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .transaction-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .amount-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2>💳 ประวัติธุรกรรม</h2>
                <p class="text-muted">รายการชำระเงินทั้งหมดของคุณ</p>
            </div>
        </div>

        <!-- Stats Summary -->
        <?php
        // คำนวณสถิติ
        mysqli_data_seek($transactions, 0);
        $total_transactions = mysqli_num_rows($transactions);
        $total_income = 0;
        $total_weight_all = 0;

        while ($t = mysqli_fetch_assoc($transactions)) {
            if ($t['payment_status'] == 'paid') {
                $total_income += $t['total_amount'];
                $total_weight_all += $t['total_weight'];
            }
        }
        mysqli_data_seek($transactions, 0);
        ?>

        <div class="stats-card">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo number_format($total_transactions); ?></div>
                        <div class="stat-label">ธุรกรรมทั้งหมด</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-value text-success"><?php echo number_format($total_income, 2); ?> ฿</div>
                        <div class="stat-label">รายได้รวม</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-value text-info"><?php echo number_format($total_weight_all, 2); ?> kg</div>
                        <div class="stat-label">น้ำหนักรวม</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions List -->
        <div class="row">
            <div class="col-12">
                <?php if (mysqli_num_rows($transactions) > 0): ?>
                    <?php while ($trans = mysqli_fetch_assoc($transactions)): ?>
                        <div class="transaction-card">
                            <div class="row align-items-center">
                                <!-- Left: Transaction Info -->
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1">
                                                การจอง #<?php echo str_pad($trans['booking_id'], 6, '0', STR_PAD_LEFT); ?>
                                            </h5>
                                            <small class="text-muted">
                                                ธุรกรรม #<?php echo $trans['transaction_id']; ?> •
                                                <?php echo date('d/m/Y H:i น.', strtotime($trans['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <?php if ($trans['payment_status'] == 'paid'): ?>
                                                <span class="badge bg-success">ชำระแล้ว</span>
                                            <?php elseif ($trans['payment_status'] == 'pending'): ?>
                                                <span class="badge bg-warning">รอชำระ</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">ล้มเหลว</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <small class="text-muted">วิธีการชำระเงิน</small><br>
                                            <?php
                                            $payment_methods = [
                                                'cash' => '💵 เงินสด',
                                                'bank_transfer' => '🏦 โอนเงิน',
                                                'promptpay' => '📱 พร้อมเพย์'
                                            ];
                                            echo $payment_methods[$trans['payment_method']] ?? $trans['payment_method'];
                                            ?>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">น้ำหนักทั้งหมด</small><br>
                                            <strong><?php echo number_format($trans['total_weight'], 2); ?> kg</strong>
                                        </div>
                                    </div>

                                    <?php if ($trans['payment_date']): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                วันที่ชำระ: <?php echo date('d/m/Y H:i น.', strtotime($trans['payment_date'])); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Right: Amount & Action -->
                                <div class="col-md-4">
                                    <div class="amount-box mb-3">
                                        <div class="fs-6 opacity-75">ยอดเงิน</div>
                                        <div class="fs-1 fw-bold">
                                            <?php echo number_format($trans['total_amount'], 2); ?> ฿
                                        </div>
                                    </div>
                                    <a href="booking_detail.php?id=<?php echo $trans['booking_id']; ?>"
                                       class="btn btn-outline-primary w-100">
                                        ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="display-1 opacity-25">💳</div>
                        <h4 class="text-muted mt-3">ยังไม่มีธุรกรรม</h4>
                        <p class="text-muted">เมื่อคุณทำการขายขยะรีไซเคิลกับเรา<br>ประวัติธุรกรรมจะแสดงที่นี่</p>
                        <a href="booking_create.php" class="btn btn-primary mt-3">
                            📝 จองรับซื้อขยะ
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Box -->
        <div class="alert alert-info mt-4">
            <strong>ℹ️ หมายเหตุ:</strong>
            <ul class="mb-0 mt-2">
                <li>ธุรกรรมจะถูกสร้างเมื่อพนักงานยืนยันการชำระเงินเสร็จสิ้น</li>
                <li>คุณสามารถดูรายละเอียดการจองและรายการขยะได้ที่หน้ารายละเอียด</li>
                <li>หากพบปัญหาเกี่ยวกับธุรกรรม กรุณาติดต่อเจ้าหน้าที่</li>
            </ul>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
