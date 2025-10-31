<?php
session_start();

// ตั้งค่าสำหรับ navbar
$base_url = '';
$current_page = 'register';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 0;
        }

        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            margin: 0 auto;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h2 {
            color: #10b981;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .register-header p {
            color: #666;
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .form-control:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25);
        }

        .btn-register {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 12px;
            font-size: 1.1em;
            font-weight: bold;
            transition: transform 0.2s;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #10b981;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .required {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="container">
            <div class="register-card">
                <div class="register-header">
                    <h2>🌱 สมัครสมาชิก</h2>
                    <p>Green Digital - แพลตฟอร์มรับซื้อขยะรีไซเคิลถึงที่</p>
                </div>

                <form action="sql/register_process.php" method="POST">
                    <div class="row">
                        <!-- Email -->
                        <div class="col-12 mb-3">
                            <label class="form-label">อีเมล <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
                        </div>

                        <!-- Password -->
                        <div class="col-12 mb-3">
                            <label class="form-label">รหัสผ่าน <span class="required">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน (อย่างน้อย 6 ตัวอักษร)" minlength="6" required>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-12 mb-3">
                            <label class="form-label">ยืนยันรหัสผ่าน <span class="required">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="กรอกรหัสผ่านอีกครั้ง" minlength="6" required>
                        </div>

                        <!-- First Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ชื่อจริง <span class="required">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="ชื่อ" required>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">นามสกุล <span class="required">*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="นามสกุล" required>
                        </div>

                        <!-- Phone -->
                        <div class="col-12 mb-3">
                            <label class="form-label">เบอร์โทรศัพท์ <span class="required">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="0812345678" pattern="[0-9]{10}" required>
                            <small class="text-muted">ตัวอย่าง: 0812345678 (10 หลัก)</small>
                        </div>

                        <!-- Address -->
                        <div class="col-12 mb-3">
                            <label class="form-label">ที่อยู่ <span class="required">*</span></label>
                            <textarea name="address" class="form-control" rows="3" placeholder="บ้านเลขที่ หมู่บ้าน ซอย ถนน" required></textarea>
                        </div>

                        <!-- Province -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">จังหวัด <span class="required">*</span></label>
                            <input type="text" name="province" class="form-control" placeholder="เช่น กรุงเทพมหานคร" required>
                        </div>

                        <!-- District -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">เขต/อำเภอ <span class="required">*</span></label>
                            <input type="text" name="district" class="form-control" placeholder="เช่น บางกะปิ" required>
                        </div>

                        <!-- Security Answer -->
                        <div class="col-12 mb-3">
                            <label class="form-label">เพื่อนสนิทของคุณคือใคร? <span class="required">*</span></label>
                            <input type="text" name="security_answer" class="form-control" placeholder="ใช้สำหรับกู้คืนรหัสผ่าน" required>
                            <small class="text-muted">⚠️ จำคำตอบให้ดี เพราะจะใช้ในการกู้คืนรหัสผ่าน (ตรงตัวพิมพ์เล็ก-ใหญ่)</small>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-success btn-register w-100">
                                ✅ สมัครสมาชิก
                            </button>
                        </div>
                    </div>
                </form>

                <div class="login-link">
                    <p>มีบัญชีอยู่แล้ว? <a href="user_login.php">เข้าสู่ระบบ</a></p>
                    <p><a href="index.php">← กลับหน้าแรก</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- แสดง Alert ถ้ามี Session -->
    <?php if(isset($_SESSION['success'])): ?>
        <script>
            alert("<?php echo $_SESSION['success']; ?>");
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <script>
            alert("<?php echo $_SESSION['error']; ?>");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <script src="js/bootstrap.bundle.min.js"></script>

    <!-- Validate Password Match -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            var password = document.querySelector('input[name="password"]').value;
            var confirm = document.querySelector('input[name="confirm_password"]').value;

            if(password !== confirm) {
                e.preventDefault();
                alert('รหัสผ่านไม่ตรงกัน กรุณาตรวจสอบอีกครั้ง');
            }
        });
    </script>
</body>
</html>
