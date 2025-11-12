<?php
session_start();

// ตรวจสอบว่า verify แล้วหรือยัง
if (!isset($_SESSION['reset_verified']) || !isset($_SESSION['forgot_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

// ตั้งค่าสำหรับ navbar
$base_url = '';
$current_page = 'reset_password';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งรหัสผ่านใหม่ - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .reset-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 0;
        }

        .reset-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .reset-header h2 {
            color: #10b981;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .reset-header p {
            color: #666;
        }

        .btn-reset {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 12px;
            font-size: 1.1em;
            font-weight: bold;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        .success-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .success-box strong {
            color: #065f46;
        }

        .user-info {
            background: #f0fdf4;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="reset-wrapper">
        <div class="container">
            <div class="reset-card">
                <div class="reset-header">
                    <h2>✅ ยืนยันตัวตนสำเร็จ</h2>
                    <p>ตั้งรหัสผ่านใหม่</p>
                </div>

                <div class="user-info">
                    <small class="text-muted">บัญชี:</small><br>
                    <strong><?php echo htmlspecialchars($_SESSION['forgot_name'] ?? 'ผู้ใช้'); ?></strong><br>
                    <small><?php echo htmlspecialchars($_SESSION['forgot_email'] ?? ''); ?></small>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php
                        if ($_GET['error'] == 'password_mismatch') {
                            echo '❌ รหัสผ่านไม่ตรงกัน';
                        } elseif ($_GET['error'] == 'password_short') {
                            echo '❌ รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
                        } else {
                            echo '❌ เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <form action="sql/reset_password_process.php" method="POST" id="resetForm">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" class="form-control form-control-lg" id="new_password" name="new_password" minlength="6" required autofocus>
                        <small class="text-muted">อย่างน้อย 6 ตัวอักษร</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" minlength="6" required>
                    </div>

                    <button type="submit" class="btn btn-success btn-reset w-100">บันทึกรหัสผ่านใหม่ ✓</button>
                </form>

                <div class="text-center mt-3">
                    <a href="user_login.php" class="text-decoration-none">← กลับไปหน้าเข้าสู่ระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            var password = document.getElementById('new_password').value;
            var confirm = document.getElementById('confirm_password').value;

            if(password !== confirm) {
                e.preventDefault();
                alert('รหัสผ่านไม่ตรงกัน กรุณาตรวจสอบอีกครั้ง');
            }

            if(password.length < 6) {
                e.preventDefault();
                alert('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
            }
        });
    </script>
</body>
</html>
