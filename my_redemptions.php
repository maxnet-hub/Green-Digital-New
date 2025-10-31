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

// ดึงประวัติการแลกของรางวัล
$redemptions_sql = "SELECT rr.*, r.reward_name, r.category,
                    a.full_name as admin_name
                    FROM reward_redemptions rr
                    JOIN rewards r ON rr.reward_id = r.reward_id
                    LEFT JOIN admins a ON rr.redeemed_by = a.admin_id
                    WHERE rr.user_id = $user_id
                    ORDER BY rr.redemption_date DESC";
$redemptions = mysqli_query($conn, $redemptions_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการแลกของรางวัล - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .redemption-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .redemption-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .points-used {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>📋 ประวัติการแลกของรางวัล</h3>
            <a href="rewards.php" class="btn btn-primary">🎁 ดูของรางวัล</a>
        </div>

        <?php if(mysqli_num_rows($redemptions) > 0): ?>
            <?php while($rd = mysqli_fetch_assoc($redemptions)): ?>
                <div class="redemption-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5><?= htmlspecialchars($rd['reward_name']) ?></h5>
                            <div class="text-muted">
                                <small>
                                    📅 วันที่: <?= date('d/m/Y H:i น.', strtotime($rd['redemption_date'])) ?><br>
                                    📦 จำนวน: <?= $rd['quantity'] ?> ชิ้น<br>
                                    <?php if($rd['delivery_method'] == 'delivery'): ?>
                                        🚚 จัดส่ง: <?= htmlspecialchars($rd['delivery_address']) ?><br>
                                    <?php else: ?>
                                        🏪 รับเอง<br>
                                    <?php endif; ?>
                                    <?php if($rd['admin_name']): ?>
                                        👤 ดำเนินการโดย: <?= htmlspecialchars($rd['admin_name']) ?><br>
                                    <?php endif; ?>
                                    <?php if($rd['notes']): ?>
                                        📝 หมายเหตุ: <?= htmlspecialchars($rd['notes']) ?><br>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="points-used">-<?= number_format($rd['total_points']) ?> แต้ม</span>
                            <br><br>
                            <?php if($rd['status'] == 'completed'): ?>
                                <span class="badge bg-success">เสร็จสิ้น</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= $rd['status'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <h3 class="text-muted">📋</h3>
                <p class="text-muted">ยังไม่มีประวัติการแลกของรางวัล</p>
                <a href="rewards.php" class="btn btn-primary mt-3">ดูของรางวัล</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
