<?php
require_once '../config.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Query การแจ้งเตือนทั้งหมด
$sql = "SELECT * FROM notifications  ORDER BY created_at DESC";
$result = mysqli_query($conn,$sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การแจ้งเตือน - Green Digital Admin</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        .notification-card {
            border-left: 4px solid #6c757d;
        }
        .notification-card.type-booking { border-left-color: #0d6efd; }
        .notification-card.type-payment { border-left-color: #198754; }
        .notification-card.type-system { border-left-color: #6c757d; }
        .notification-card.type-promotion { border-left-color: #ffc107; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>🔔 การแจ้งเตือน</h3>
        </div>

        <!-- Notifications List -->
        <div class="row">
            <div class="col-12">
                <?php if($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($notif = mysqli_fetch_assoc($result)):
                        // กำหนด icon และ class ตาม type
                        $icon = '📢';
                        $type_class = 'type-system';
                        $type_name = 'ระบบ';
                        $badge_class = 'bg-secondary';

                        switch($notif['type']) {
                            case 'booking':
                                $icon = '🔵';
                                $type_class = 'type-booking';
                                $type_name = 'การจอง';
                                $badge_class = 'bg-primary';
                                break;
                            case 'payment':
                                $icon = '💚';
                                $type_class = 'type-payment';
                                $type_name = 'การชำระเงิน';
                                $badge_class = 'bg-success';
                                break;
                            case 'system':
                                $icon = '⚙️';
                                $type_class = 'type-system';
                                $type_name = 'ระบบ';
                                $badge_class = 'bg-secondary';
                                break;
                            case 'promotion':
                                $icon = '🎁';
                                $type_class = 'type-promotion';
                                $type_name = 'โปรโมชั่น';
                                $badge_class = 'bg-warning text-dark';
                                break;
                        }
                    ?>
                    <div class="card notification-card <?php echo $type_class; ?> mb-3">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="me-3 fs-1">
                                    <?php echo $icon; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($notif['title']); ?></h5>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $type_name; ?></span>
                                    </div>
                                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($notif['message'])); ?></p>
                                    <small class="text-muted">
                                        🕒 <?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <h5>ไม่มีการแจ้งเตือน</h5>
                        <p class="mb-0">ยังไม่มีการแจ้งเตือนในขณะนี้</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
