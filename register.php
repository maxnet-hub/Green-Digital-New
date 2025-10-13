<?php
require_once 'config.php';

// ถ้าล็อกอินอยู่แล้วให้ redirect
if (isset($_SESSION['user_id'])) {
    header("Location: user/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card col-12 col-md-6 col-lg-5">
            <div class="login-header">
                <h1>🌿 Green Digital</h1>
                <p>สมัครสมาชิก</p>
            </div>

            <div class="login-body">
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>ผิดพลาด!</strong>
                        <?php
                        if($_GET['error'] == 'duplicate') echo 'อีเมลหรือเบอร์โทรนี้มีในระบบแล้ว';
                        elseif($_GET['error'] == 'password_mismatch') echo 'รหัสผ่านไม่ตรงกัน';
                        else echo 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
                        ?>
                    </div>
                <?php endif; ?>

                <form action="sql/register_process.php" method="POST">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="mb-4">
                        <label for="address" class="form-label">ที่อยู่</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 btn-gradient">สมัครสมาชิก</button>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">มีบัญชีอยู่แล้ว? <a href="user_login.php" class="text-decoration-none">เข้าสู่ระบบ</a></p>
                    <a href="index.php" class="text-decoration-none">← กลับหน้าหลัก</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
