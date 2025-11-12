<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ Admin - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            overflow-x: hidden;
            position: relative;
            animation: gradientShift 10s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% {
                background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            }
            50% {
                background: linear-gradient(135deg, #38ef7d 0%, #11998e 100%);
            }
        }

        /* Floating Trash Animation */
        .floating-trash {
            position: fixed;
            font-size: 3.5rem;
            animation: floatUpDown 8s infinite ease-in-out;
            z-index: 9999;
            pointer-events: none;
            filter: drop-shadow(2px 4px 6px rgba(0,0,0,0.2));
        }

        @keyframes floatUpDown {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            25% {
                transform: translateY(-30px) rotate(5deg);
            }
            50% {
                transform: translateY(-60px) rotate(-5deg);
            }
            75% {
                transform: translateY(-30px) rotate(3deg);
            }
        }

        .floating-trash:nth-child(1) { left: 8%; top: 15%; animation-duration: 6s; }
        .floating-trash:nth-child(2) { left: 18%; top: 65%; animation-duration: 7s; animation-delay: -1s; }
        .floating-trash:nth-child(3) { left: 28%; top: 35%; animation-duration: 8s; animation-delay: -2s; }
        .floating-trash:nth-child(4) { right: 25%; top: 55%; animation-duration: 7.5s; animation-delay: -3s; }
        .floating-trash:nth-child(5) { right: 15%; top: 25%; animation-duration: 6.5s; animation-delay: -1.5s; }
        .floating-trash:nth-child(6) { right: 8%; top: 75%; animation-duration: 7s; animation-delay: -2.5s; }
        .floating-trash:nth-child(7) { left: 5%; top: 45%; animation-duration: 8s; animation-delay: -4s; }
        .floating-trash:nth-child(8) { right: 20%; top: 10%; animation-duration: 6.5s; animation-delay: -3.5s; }
        .floating-trash:nth-child(9) { left: 15%; top: 80%; animation-duration: 7.5s; animation-delay: -2s; }
        .floating-trash:nth-child(10) { right: 10%; top: 50%; animation-duration: 7s; animation-delay: -1s; }

        .login-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 50px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25), 0 0 100px rgba(56, 239, 125, 0.3);
            animation: slideIn 0.8s ease-out, cardFloat 6s ease-in-out infinite;
            border: 2px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(56, 239, 125, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        @keyframes cardFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
            position: relative;
            z-index: 1;
        }

        .login-header h1 {
            font-size: 3rem;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 50%, #0f9b8e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            animation: titlePulse 3s ease-in-out infinite, titleGlow 2s ease-in-out infinite;
            font-weight: 800;
            text-shadow: 0 0 30px rgba(56, 239, 125, 0.5);
            letter-spacing: 2px;
        }

        @keyframes titlePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }

        @keyframes titleGlow {
            0%, 100% {
                filter: brightness(1) drop-shadow(0 0 10px rgba(56, 239, 125, 0.3));
            }
            50% {
                filter: brightness(1.2) drop-shadow(0 0 20px rgba(56, 239, 125, 0.6));
            }
        }

        .login-header p {
            color: #666;
            font-size: 1.1rem;
            margin: 0;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 15px 20px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
        }

        .form-control:focus {
            border-color: #38ef7d;
            box-shadow: 0 0 0 4px rgba(56, 239, 125, 0.15), 0 10px 30px rgba(56, 239, 125, 0.2);
            transform: translateY(-3px) scale(1.02);
            background: rgba(255, 255, 255, 1);
        }

        .form-control:hover {
            border-color: #11998e;
            transform: translateY(-2px);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 15px;
            padding: 15px;
            font-size: 1.2rem;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .btn-gradient:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 15px 40px rgba(56, 239, 125, 0.5), 0 0 50px rgba(17, 153, 142, 0.3);
            background: linear-gradient(135deg, #38ef7d 0%, #11998e 100%);
        }

        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-gradient:hover::before {
            width: 400px;
            height: 400px;
        }

        .btn-gradient:active {
            transform: translateY(-2px) scale(0.98);
        }

        .alert {
            border-radius: 10px;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        a {
            color: #11998e;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            transition: width 0.3s ease;
        }

        a:hover {
            color: #38ef7d;
            transform: translateX(-5px);
            display: inline-block;
        }

        a:hover::after {
            width: 100%;
        }

        .form-label {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-body {
            position: relative;
            z-index: 1;
        }

        /* Particles Background */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: particleFloat 15s infinite ease-in-out;
        }

        @keyframes particleFloat {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 0.5;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) translateX(50px);
                opacity: 0;
            }
        }

        .particle:nth-child(1) { left: 10%; width: 4px; height: 4px; animation-delay: 0s; animation-duration: 12s; }
        .particle:nth-child(2) { left: 25%; width: 6px; height: 6px; animation-delay: 2s; animation-duration: 15s; }
        .particle:nth-child(3) { left: 40%; width: 3px; height: 3px; animation-delay: 4s; animation-duration: 10s; }
        .particle:nth-child(4) { left: 55%; width: 5px; height: 5px; animation-delay: 1s; animation-duration: 13s; }
        .particle:nth-child(5) { left: 70%; width: 4px; height: 4px; animation-delay: 3s; animation-duration: 14s; }
        .particle:nth-child(6) { left: 85%; width: 6px; height: 6px; animation-delay: 5s; animation-duration: 11s; }
        .particle:nth-child(7) { left: 15%; width: 3px; height: 3px; animation-delay: 6s; animation-duration: 16s; }
        .particle:nth-child(8) { left: 60%; width: 5px; height: 5px; animation-delay: 7s; animation-duration: 12s; }
    </style>
</head>
<body>
    <!-- Particles Background -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Floating Trash Elements -->
    <div class="floating-trash">♻️</div>
    <div class="floating-trash">🗑️</div>
    <div class="floating-trash">🥫</div>
    <div class="floating-trash">🧃</div>
    <div class="floating-trash">📦</div>
    <div class="floating-trash">🍃</div>
    <div class="floating-trash">♻️</div>
    <div class="floating-trash">🌱</div>
    <div class="floating-trash">🧴</div>
    <div class="floating-trash">🥤</div>

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
                        <strong>ผิดพลาด!</strong> ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง
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
