<?php
session_start();
require_once 'config.php';

// ตั้งค่าสำหรับ navbar
$base_url = '';
$current_page = 'index';

// ดึงข้อมูลสถิติ
$total_users_sql = "SELECT COUNT(*) as total FROM users WHERE status = 'active'";
$total_users_result = mysqli_query($conn, $total_users_sql);
$total_users = $total_users_result ? mysqli_fetch_assoc($total_users_result)['total'] : 0;

$total_bookings_sql = "SELECT COUNT(*) as total FROM bookings WHERE status = 'completed'";
$total_bookings_result = mysqli_query($conn, $total_bookings_sql);
$total_bookings = $total_bookings_result ? mysqli_fetch_assoc($total_bookings_result)['total'] : 0;

$total_co2_sql = "SELECT COALESCE(SUM(co2_reduced), 0) as total FROM carbon_footprint";
$total_co2_result = mysqli_query($conn, $total_co2_sql);
$total_co2 = $total_co2_result ? mysqli_fetch_assoc($total_co2_result)['total'] : 0;

// ดึงประเภทขยะที่รับซื้อ
$recycle_types_sql = "SELECT rt.*, p.price_per_kg
                      FROM recycle_types rt
                      LEFT JOIN prices p ON rt.type_id = p.type_id
                      WHERE rt.status = 'active' AND p.is_current = 1
                      ORDER BY rt.category";
$recycle_types = mysqli_query($conn, $recycle_types_sql);

// ดึงรีวิวลูกค้า
$reviews_sql = "SELECT r.*, u.first_name, u.last_name
                FROM reviews r
                JOIN users u ON r.user_id = u.user_id
                WHERE r.status = 'approved' AND r.is_featured = 1
                ORDER BY r.created_at DESC
                LIMIT 3";
$reviews = mysqli_query($conn, $reviews_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green Digital - แพลตฟอร์มรับซื้อขยะรีไซเคิลถึงที่</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3.5em;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .hero-section p {
            font-size: 1.3em;
            margin-bottom: 30px;
        }

        .hero-buttons .btn {
            margin: 10px;
            padding: 15px 40px;
            font-size: 1.1em;
            border-radius: 50px;
        }

        /* Features Section */
        .features-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .feature-card {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            color: #10b981;
            margin-bottom: 15px;
        }

        /* How It Works Section */
        .how-it-works-section {
            padding: 80px 0;
        }

        .step-card {
            text-align: center;
            padding: 30px;
            position: relative;
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            font-weight: bold;
            margin: 0 auto 20px;
        }

        .step-card h4 {
            color: #059669;
            margin-bottom: 15px;
        }

        /* Recycle Types Section */
        .recycle-types-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .recycle-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .recycle-card:hover {
            transform: scale(1.05);
        }

        .recycle-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .price-tag {
            background: #10b981;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 10px;
            font-weight: bold;
        }

        /* Statistics Section */
        .stats-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .stat-box {
            text-align: center;
            padding: 30px;
        }

        .stat-number {
            font-size: 3em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.2em;
        }

        /* Reviews Section */
        .reviews-section {
            padding: 80px 0;
        }

        .review-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .stars {
            color: #fbbf24;
            font-size: 1.2em;
            margin-bottom: 15px;
        }

        .reviewer-name {
            color: #059669;
            font-weight: bold;
            margin-top: 15px;
        }

        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 2.5em;
            color: #059669;
            margin-bottom: 15px;
        }

        .section-title p {
            font-size: 1.2em;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <h1>♻️ รับซื้อขยะรีไซเคิลถึงที่</h1>
            <p>สะดวก รวดเร็ว ราคายุติธรรม พร้อมช่วยโลกไปด้วยกัน</p>
            <p>แค่จองผ่านแพลตฟอร์ม เราไปรับถึงบ้าน ชั่งหน้างาน โอนเงินทันที!</p>
            <div class="hero-buttons">
                <a href="user_register.php" class="btn btn-success btn-lg">เริ่มต้นใช้งาน</a>
                <a href="#how-it-works" class="btn btn-outline-light btn-lg">เรียนรู้เพิ่มเติม</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-title">
                <h2>ทำไมต้อง Green Digital?</h2>
                <p>คุณสมบัติเด่นที่ทำให้เราแตกต่าง</p>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon">🚚</div>
                        <h3>รับซื้อถึงที่</h3>
                        <p>ไม่ต้องเสียเวลาขนของไปขายเอง เราไปรับถึงบ้านคุณ ภายใน 24-48 ชั่วโมง</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon">💰</div>
                        <h3>ราคายุติธรรม</h3>
                        <p>ราคาโปร่งใส อัพเดทตามตลาดแบบเรียลไทม์ ชั่งหน้างาน โอนเงินทันที</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon">🌱</div>
                        <h3>ช่วยสิ่งแวดล้อม</h3>
                        <p>ติดตามปริมาณ CO2 ที่คุณช่วยลดได้ พร้อม Dashboard แสดงผลกระทบเชิงบวก</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon">⭐</div>
                        <h3>สะสมแต้ม</h3>
                        <p>Gamification สนุก สะสมแต้มทุกการขาย รับสิทธิพิเศษ ระดับ Bronze, Silver, Gold</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>วิธีใช้งาน</h2>
                <p>ขั้นตอนง่ายๆ เพียง 4 ขั้นตอน</p>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>สมัครสมาชิก</h4>
                        <p>ลงทะเบียนฟรี ใช้เวลาไม่ถึง 2 นาที</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>จองรับซื้อ</h4>
                        <p>เลือกวันเวลา บอกประเภทและน้ำหนักโดยประมาณ</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>เราไปรับถึงบ้าน</h4>
                        <p>ชั่งน้ำหนักหน้างาน ตรวจสอบคุณภาพ</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4>รับเงินทันที</h4>
                        <p>โอนเงินเข้าบัญชี รับแต้มสะสม ช่วยโลกไปด้วยกัน!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recycle Types Section -->
    <section class="recycle-types-section" id="recycle-types">
        <div class="container">
            <div class="section-title">
                <h2>ประเภทขยะที่รับซื้อ</h2>
                <p>ราคาอัพเดทตามตลาดแบบเรียลไทม์</p>
            </div>

            <div class="row">
                <?php if($recycle_types && mysqli_num_rows($recycle_types) > 0): ?>
                    <?php while($type = mysqli_fetch_assoc($recycle_types)): ?>
                        <div class="col-md-4 col-lg-3">
                            <div class="recycle-card">
                                <div class="recycle-icon">
                                    <?php
                                    // กำหนดไอคอนตามประเภท
                                    $icon = '♻️';
                                    if($type['category'] == 'plastic') $icon = '🍾';
                                    elseif($type['category'] == 'paper') $icon = '📄';
                                    elseif($type['category'] == 'metal') $icon = '🥫';
                                    elseif($type['category'] == 'glass') $icon = '🍶';
                                    echo $icon;
                                    ?>
                                </div>
                                <h5><?php echo htmlspecialchars($type['type_name']); ?></h5>
                                <p class="text-muted small"><?php echo htmlspecialchars($type['description']); ?></p>
                                <div class="price-tag">
                                    <?php echo number_format($type['price_per_kg'], 2); ?> ฿/kg
                                </div>
                                <p class="text-success small mt-2">
                                    🌱 ลด CO2: <?php echo number_format($type['co2_reduction'], 2); ?> kg
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">ยังไม่มีข้อมูลประเภทขยะ</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="section-title">
                <h2 style="color: white;">ผลกระทบที่เราสร้างร่วมกัน</h2>
                <p style="color: rgba(255,255,255,0.9);">ตัวเลขที่พิสูจน์ว่าเราช่วยโลกได้จริง</p>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo number_format($total_users); ?>+</div>
                        <div class="stat-label">สมาชิกที่ไว้วางใจ</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo number_format($total_bookings); ?>+</div>
                        <div class="stat-label">การรับซื้อที่สำเร็จ</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo number_format($total_co2, 2); ?></div>
                        <div class="stat-label">kg CO2 ที่ช่วยลดได้</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <?php if($reviews && mysqli_num_rows($reviews) > 0): ?>
    <section class="reviews-section">
        <div class="container">
            <div class="section-title">
                <h2>รีวิวจากลูกค้า</h2>
                <p>ความพึงพอใจจากผู้ใช้งานจริง</p>
            </div>

            <div class="row">
                <?php while($review = mysqli_fetch_assoc($reviews)): ?>
                    <div class="col-md-4">
                        <div class="review-card">
                            <div class="stars">
                                <?php for($i = 0; $i < $review['rating']; $i++): ?>
                                    ⭐
                                <?php endfor; ?>
                            </div>
                            <p><?php echo htmlspecialchars($review['comment']); ?></p>
                            <div class="reviewer-name">
                                - <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>พร้อมเริ่มต้นช่วยโลกแล้วหรือยัง?</h2>
            <p class="mb-4">ขายขยะรีไซเคิล รับเงิน และช่วยลด CO2 ไปพร้อมๆ กัน</p>
            <a href="user_register.php" class="btn btn-success btn-lg px-5">สมัครสมาชิกฟรี</a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
