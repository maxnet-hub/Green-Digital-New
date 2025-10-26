<?php
require_once '../config.php';

// ตรวจสอบว่าเป็นสมาชิก
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลสมาชิกและแต้มปัจจุบัน
$user_sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);
$current_points = $user['points'] ?? 0;

// ดึงประวัติการทำรายการแต้มทั้งหมด
$points_sql = "SELECT pt.*,
               b.booking_id, b.booking_date, b.pickup_address
               FROM point_transactions pt
               LEFT JOIN bookings b ON pt.booking_id = b.booking_id
               WHERE pt.user_id = '$user_id'
               ORDER BY pt.created_at DESC";
$points_transactions = mysqli_query($conn, $points_sql);

// คำนวณสถิติ
$total_earned = 0;
$total_redeemed = 0;
$total_transactions = mysqli_num_rows($points_transactions);

// Reset pointer เพื่อนับสถิติ
mysqli_data_seek($points_transactions, 0);
while ($pt = mysqli_fetch_assoc($points_transactions)) {
    if ($pt['transaction_type'] == 'earn' || $pt['points'] > 0) {
        $total_earned += abs($pt['points']);
    } elseif ($pt['transaction_type'] == 'redeem' || $pt['points'] < 0) {
        $total_redeemed += abs($pt['points']);
    }
}

// Reset pointer อีกครั้งเพื่อแสดงผล
mysqli_data_seek($points_transactions, 0);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แต้มสะสม - Green Digital</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .points-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .points-value {
            font-size: 4em;
            font-weight: bold;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .points-label {
            font-size: 1.2em;
            opacity: 0.9;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        .stats-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .transaction-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
            background: white;
        }
        .transaction-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .points-badge-positive {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.1em;
        }
        .points-badge-negative {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.1em;
        }
        .transaction-type-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .type-earn {
            background-color: #d4edda;
            color: #155724;
        }
        .type-redeem {
            background-color: #f8d7da;
            color: #721c24;
        }
        .type-adjustment {
            background-color: #fff3cd;
            color: #856404;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state-icon {
            font-size: 5em;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Points Hero Section -->
        <div class="points-hero">
            <div class="points-label">⭐ แต้มสะสมของคุณ</div>
            <div class="points-value"><?= number_format($current_points) ?></div>
            <div class="points-label">แต้ม</div>
            <div class="mt-3">
                <small style="opacity: 0.8;">💰 100 บาท = 1 แต้ม</small>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stat-value text-success">+<?= number_format($total_earned) ?></div>
                    <div class="stat-label">แต้มที่ได้รับทั้งหมด</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stat-value text-danger">-<?= number_format($total_redeemed) ?></div>
                    <div class="stat-label">แต้มที่ใช้ไปแล้ว</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stat-value text-primary"><?= number_format($total_transactions) ?></div>
                    <div class="stat-label">จำนวนรายการทั้งหมด</div>
                </div>
            </div>
        </div>

        <!-- Information Box -->
        <div class="info-box">
            <h6 class="mb-2">📋 วิธีการได้รับแต้ม</h6>
            <ul class="mb-0">
                <li>รับแต้มทุกครั้งที่ขายขยะรีไซเคิล (100 บาท = 1 แต้ม)</li>
                <li>แต้มจะได้รับเมื่อการจองเสร็จสิ้นและชำระเงินเรียบร้อยแล้ว</li>
                <li>สามารถนำแต้มไปแลกของรางวัลหรือส่วนลดได้ในอนาคต</li>
            </ul>
        </div>

        <!-- Transactions History -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3">📜 ประวัติการทำรายการแต้ม</h4>

                <?php if ($total_transactions > 0): ?>
                    <?php while ($pt = mysqli_fetch_assoc($points_transactions)): ?>
                        <div class="transaction-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-2">
                                        <?php
                                        $type_class = 'type-earn';
                                        $type_text = 'ได้รับแต้ม';
                                        $icon = '✅';

                                        if ($pt['transaction_type'] == 'redeem' || $pt['points'] < 0) {
                                            $type_class = 'type-redeem';
                                            $type_text = 'ใช้แต้ม';
                                            $icon = '🎁';
                                        } elseif ($pt['transaction_type'] == 'adjustment') {
                                            $type_class = 'type-adjustment';
                                            $type_text = 'ปรับปรุง';
                                            $icon = '⚙️';
                                        }
                                        ?>
                                        <span style="font-size: 1.5em; margin-right: 10px;"><?= $icon ?></span>
                                        <div>
                                            <span class="transaction-type-badge <?= $type_class ?>">
                                                <?= $type_text ?>
                                            </span>
                                        </div>
                                    </div>

                                    <h6 class="mb-2"><?= htmlspecialchars($pt['description'] ?? 'รายการแต้ม') ?></h6>

                                    <div class="text-muted small">
                                        <?php if ($pt['booking_id']): ?>
                                            <span>📦 การจองหมายเลข: #<?= str_pad($pt['booking_id'], 6, '0', STR_PAD_LEFT) ?></span>
                                            <br>
                                        <?php endif; ?>

                                        <?php if ($pt['amount'] > 0): ?>
                                            <span>💵 ยอดเงิน: <?= number_format($pt['amount'], 2) ?> บาท</span>
                                            <br>
                                        <?php endif; ?>

                                        <span>🕐 วันที่: <?= date('d/m/Y H:i น.', strtotime($pt['created_at'])) ?></span>
                                    </div>
                                </div>

                                <div class="col-md-4 text-end">
                                    <?php if ($pt['points'] > 0): ?>
                                        <div class="points-badge-positive">
                                            +<?= number_format($pt['points']) ?> แต้ม
                                        </div>
                                    <?php else: ?>
                                        <div class="points-badge-negative">
                                            <?= number_format($pt['points']) ?> แต้ม
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="empty-state-icon">⭐</div>
                        <h5>ยังไม่มีรายการแต้มสะสม</h5>
                        <p class="text-muted">เริ่มต้นรับแต้มได้ด้วยการขายขยะรีไซเคิลกับเรา</p>
                        <a href="booking_create.php" class="btn btn-primary mt-3">
                            📅 สร้างการจองใหม่
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
