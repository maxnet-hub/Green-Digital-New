<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ Admin - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card col-12 col-md-5 col-lg-4">
            <div class="login-header">
                <h1>🌿 Green Digital</h1>
                <p>ระบบผู้ดูแล</p>
                <?php
                // echo password_hash(password: "admin", algo: PASSWORD_DEFAULT);
                ?>      
            </div>

            <div class="login-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>ผิดพลาด!</strong>
                        <?php
                        if ($_GET['error'] == 'suspended') {
                            echo 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ';
                        } else {
                            echo 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <form action="sql/login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" class="form-control form-control-lg" id="username" name="username" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 btn-gradient">เข้าสู่ระบบ</button>
                </form>

                <div class="text-center mt-3">
                    <a href="index.php" class="text-decoration-none">← กลับหน้าหลัก</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
