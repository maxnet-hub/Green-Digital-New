<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบสมาชิก - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card col-12 col-md-5 col-lg-4">
            <div class="login-header">
                <h1>🌿 Green Digital</h1>
                <p>เข้าสู่ระบบสมาชิก</p>
            </div>

            <div class="login-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>ผิดพลาด!</strong> อีเมล/เบอร์โทร หรือรหัสผ่านไม่ถูกต้อง
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
                    <div class="alert alert-success" role="alert">
                        <strong>สำเร็จ!</strong> สมัครสมาชิกเรียบร้อย กรุณาเข้าสู่ระบบ
                    </div>
                <?php endif; ?>

                <form action="sql/user_login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">อีเมล หรือ เบอร์โทร</label>
                        <input type="text" class="form-control form-control-lg" id="username" name="username" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 btn-gradient">เข้าสู่ระบบ</button>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-2">ยังไม่มีบัญชี? <a href="register.php" class="text-decoration-none">สมัครสมาชิก</a></p>
                    <a href="index.php" class="text-decoration-none">← กลับหน้าหลัก</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
