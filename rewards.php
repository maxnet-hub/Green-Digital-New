<?php
require_once 'config.php';

// ตรวจสอบว่าเป็นสมาชิก
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลสมาชิกและแต้มปัจจุบัน
$user_sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);
$current_points = $user['points'] ?? 0;

// กรองตามหมวดหมู่
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$where_clause = "status = 'active'";
if ($category_filter != 'all') {
    $where_clause .= " AND category = '$category_filter'";
}

// ดึงของรางวัลทั้งหมด
$rewards_sql = "SELECT * FROM rewards WHERE $where_clause ORDER BY points_required ASC";
$rewards = mysqli_query($conn, $rewards_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ของรางวัล - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .points-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .reward-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
            height: 100%;
        }
        .reward-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            transform: translateY(-5px);
        }
        .reward-image {
            height: 200px;
            background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4em;
        }
        .points-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .stock-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
        }
        .info-alert {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <!-- Points Hero -->
        <div class="points-hero">
            <h4>แต้มสะสมของคุณ</h4>
            <h1 class="display-4 mb-0"><?= number_format($current_points) ?> แต้ม</h1>
            <p class="mb-3">ดูรายการของรางวัลที่สามารถแลกได้</p>
            <a href="my_redemptions.php" class="btn btn-light btn-lg">
                📋 ดูประวัติการแลกของฉัน
            </a>
        </div>

        <!-- Info Alert -->
        <div class="alert info-alert">
            <h5 class="mb-2">💬 วิธีการแลกของรางวัล</h5>
            <p class="mb-0">
                กรุณาติดต่อเจ้าหน้าที่เพื่อแลกของรางวัล โดยแจ้งชื่อและเบอร์โทรศัพท์ของคุณ<br>
                เจ้าหน้าที่จะช่วยตรวจสอบแต้มและดำเนินการแลกของให้คุณ
            </p>
        </div>

        <!-- Category Filter -->
        <div class="mb-4">
            <div class="btn-group" role="group">
                <a href="rewards.php?category=all" class="btn <?= $category_filter == 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">ทั้งหมด</a>
                <a href="rewards.php?category=food" class="btn <?= $category_filter == 'food' ? 'btn-primary' : 'btn-outline-primary' ?>">อาหาร</a>
                <a href="rewards.php?category=product" class="btn <?= $category_filter == 'product' ? 'btn-primary' : 'btn-outline-primary' ?>">สินค้า</a>
                <a href="rewards.php?category=voucher" class="btn <?= $category_filter == 'voucher' ? 'btn-primary' : 'btn-outline-primary' ?>">คูปอง</a>
            </div>
        </div>

        <!-- Rewards Grid (Catalog View Only) -->
        <div class="row">
            <?php if(mysqli_num_rows($rewards) > 0): ?>
                <?php while($reward = mysqli_fetch_assoc($rewards)): ?>
                    <?php
                    $can_afford = $current_points >= $reward['points_required'];
                    $in_stock = $reward['stock_quantity'] == 0 || $reward['stock_quantity'] > 0;
                    $category_icon = [
                        'food' => '🍱',
                        'product' => '🎁',
                        'voucher' => '🎟️',
                        'discount' => '💰'
                    ];
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="reward-card">
                            <div class="position-relative">
                                <div class="reward-image">
                                    <?= $category_icon[$reward['category']] ?? '🎁' ?>
                                </div>
                                <span class="points-badge"><?= number_format($reward['points_required']) ?> แต้ม</span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($reward['reward_name']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($reward['description']) ?></p>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>
                                        <?php if($reward['stock_quantity'] == 0): ?>
                                            <span class="badge bg-success">มีให้บริการ</span>
                                        <?php elseif($reward['stock_quantity'] > 0): ?>
                                            <span class="badge bg-success">เหลือ <?= $reward['stock_quantity'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">หมดแล้ว</span>
                                        <?php endif; ?>
                                    </span>
                                    <span>
                                        <?php if($can_afford): ?>
                                            <span class="badge bg-primary">แต้มพอ</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">แต้มไม่พอ</span>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div class="alert alert-info py-2 mb-0">
                                    <small>💬 ติดต่อเจ้าหน้าที่เพื่อแลก</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h3 class="text-muted">🎁</h3>
                    <p class="text-muted">ยังไม่มีของรางวัลในหมวดหมู่นี้</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
