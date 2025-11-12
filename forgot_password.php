<?php
session_start();
require_once 'config.php';

// ตั้งค่าสำหรับ navbar
$base_url = '';
$current_page = 'forgot_password';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .forgot-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 0;
        }

        .forgot-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .forgot-header h2 {
            color: #10b981;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .forgot-header p {
            color: #666;
        }

        .btn-verify {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 12px;
            font-size: 1.1em;
            font-weight: bold;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        .step-indicator {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .step-indicator strong {
            color: #10b981;
        }
    </style>
</head>
<body>
    <div class="forgot-wrapper">
        <div class="container">
            <div class="forgot-card">
                <div class="forgot-header">
                    <h2>🔐 ลืมรหัสผ่าน</h2>
                    <p>กู้คืนรหัสผ่านของคุณ</p>
                </div>

                <div class="step-indicator">
                    <strong>ขั้นตอนที่ 1:</strong> กรอกอีเมลหรือเบอร์โทรศัพท์
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php
                        if ($_GET['error'] == 'not_found') {
                            echo '❌ ไม่พบผู้ใช้นี้ในระบบ';
                        } elseif ($_GET['error'] == 'wrong_answer') {
                            echo '❌ คำตอบไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
                        } else {
                            echo '❌ เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (!isset($_SESSION['forgot_user_id'])): ?>
                    <!-- Step 1: กรอกอีเมลหรือเบอร์โทร -->
                    <form action="sql/forgot_password_step1.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">อีเมล หรือ เบอร์โทรศัพท์</label>
                            <input type="text" class="form-control form-control-lg" id="username" name="username" required autofocus>
                            <small class="text-muted">กรอกอีเมลหรือเบอร์โทรที่ใช้สมัครสมาชิก</small>
                        </div>

                        <button type="submit" class="btn btn-success btn-verify w-100">ถัดไป →</button>
                    </form>
                <?php else: ?>
                    <!-- Step 2: ตอบคำถามความปลอดภัย -->
                    <div class="step-indicator">
                        <strong>ขั้นตอนที่ 2:</strong> ตอบคำถามความปลอดภัย
                    </div>

                    <form action="sql/forgot_password_step2.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">คำถาม:</label>
                            <p class="form-control-plaintext bg-light p-2 rounded">เพื่อนสนิทของคุณคือใคร?</p>
                        </div>

                        <div class="mb-3">
                            <label for="security_answer" class="form-label">คำตอบของคุณ</label>
                            <input type="text" class="form-control form-control-lg" id="security_answer" name="security_answer" required autofocus>
                            <small class="text-muted">⚠️ ตรงตัวพิมพ์เล็ก-ใหญ่</small>
                        </div>

                        <button type="submit" class="btn btn-success btn-verify w-100">ตรวจสอบ ✓</button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="user_login.php" class="text-decoration-none">← กลับไปหน้าเข้าสู่ระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
