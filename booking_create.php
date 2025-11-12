<?php
require_once 'config.php';

// ตรวจสอบว่าเป็นสมาชิก
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลสมาชิก
$user_sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);

// ดึงประเภทขยะที่ใช้งาน
$recycle_types_sql = "SELECT rt.*, p.price_per_kg
                      FROM recycle_types rt
                      LEFT JOIN prices p ON rt.type_id = p.type_id AND p.is_current = 1
                      WHERE rt.status = 'active'
                      ORDER BY rt.category, rt.type_name";
$recycle_types = mysqli_query($conn, $recycle_types_sql);

// จัดกลุ่มตามหมวดหมู่
$types_by_category = [];
while ($type = mysqli_fetch_assoc($recycle_types)) {
    $types_by_category[$type['category']][] = $type;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างการจองใหม่ - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .recycle-type-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }
        .recycle-type-card:has(input[type="checkbox"]:checked) {
            border-color: #28a745;
            background-color: #e8f5e9;
        }
        .weight-input-wrapper {
            display: none;
            margin-top: 10px;
        }
        .recycle-type-card:has(input[type="checkbox"]:checked) .weight-input-wrapper {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Back Button -->
                <div class="mb-3">
                    <a href="bookings.php" class="btn btn-sm btn-outline-secondary">← กลับ</a>
                </div>

                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">➕ สร้างการจองรับขยะใหม่</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="sql/booking_create.php">
                            <!-- Date & Time -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">📅 วันที่รับ <span class="text-danger">*</span></label>
                                    <input type="date" name="booking_date" class="form-control" required
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                    <small class="text-muted">จองล่วงหน้าอย่างน้อย 1 วัน</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">⏰ เวลารับ <span class="text-danger">*</span></label>
                                    <select name="booking_time" class="form-select" required>
                                        <option value="">เลือกเวลา</option>
                                        <option value="09:00:00">09:00 - 10:00 น.</option>
                                        <option value="10:00:00">10:00 - 11:00 น.</option>
                                        <option value="11:00:00">11:00 - 12:00 น.</option>
                                        <option value="13:00:00">13:00 - 14:00 น.</option>
                                        <option value="14:00:00">14:00 - 15:00 น.</option>
                                        <option value="15:00:00">15:00 - 16:00 น.</option>
                                        <option value="16:00:00">16:00 - 17:00 น.</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="mb-3">
                                <label class="form-label">📍 ที่อยู่รับขยะ <span class="text-danger">*</span></label>
                                <textarea name="pickup_address" class="form-control" rows="3" required
                                          placeholder="กรอกที่อยู่สำหรับรับขยะ..."><?php echo htmlspecialchars($user['address']); ?></textarea>
                                <small class="text-muted">ใช้ที่อยู่จากโปรไฟล์ หรือกรอกที่อยู่อื่น</small>
                            </div>

                            <!-- Recycle Types Selection -->
                            <div class="mb-3">
                                <label class="form-label">♻️ เลือกประเภทขยะและระบุน้ำหนัก <span class="text-danger">*</span></label>
                                <p class="text-muted small">กรุณาเลือกอย่างน้อย 1 รายการ และระบุน้ำหนักโดยประมาณ</p>

                                <?php foreach ($types_by_category as $category => $types): ?>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-2">
                                            <?php
                                            $category_names = [
                                                'plastic' => '🥤 พลาสติก',
                                                'paper' => '📄 กระดาษ',
                                                'metal' => '🥫 โลหะ',
                                                'glass' => '🍾 แก้ว'
                                            ];
                                            echo $category_names[$category] ?? $category;
                                            ?>
                                        </h6>
                                        <?php foreach ($types as $type): ?>
                                            <div class="recycle-type-card">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           name="selected_types[]"
                                                           value="<?php echo $type['type_id']; ?>"
                                                           id="type_<?php echo $type['type_id']; ?>">
                                                    <label class="form-check-label w-100" for="type_<?php echo $type['type_id']; ?>">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($type['type_name']); ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($type['description']); ?></small>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge bg-success"><?php echo number_format($type['price_per_kg'], 2); ?> ฿/kg</span>
                                                                <br>
                                                                <small class="text-muted">CO2: <?php echo $type['co2_reduction']; ?> kg</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                                <!-- Weight Input (แสดงเมื่อติ๊กเลือก) -->
                                                <div class="weight-input-wrapper">
                                                    <label class="form-label small">น้ำหนักโดยประมาณ (kg)</label>
                                                    <input type="number"
                                                           name="weights[<?php echo $type['type_id']; ?>]"
                                                           class="form-control form-control-sm"
                                                           placeholder="ระบุน้ำหนัก..."
                                                           step="0.01"
                                                           min="0.01">
                                                    <small class="text-muted">ราคา: <?php echo number_format($type['price_per_kg'], 2); ?> บาท/กิโลกรัม</small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label class="form-label">📝 หมายเหตุเพิ่มเติม</label>
                                <textarea name="notes" class="form-control" rows="2"
                                          placeholder="ระบุรายละเอียดเพิ่มเติม เช่น จุดรับที่ง่ายต่อการเข้าถึง..."></textarea>
                            </div>

                            <div class="alert alert-info">
                                <strong>📌 หมายเหตุ:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>ราคาและน้ำหนักที่แสดงเป็นเพียงประมาณการเบื้องต้น</li>
                                    <li>ราคาจริงจะคำนวณจากน้ำหนักที่ชั่งจริง ณ วันรับของ</li>
                                    <li>เจ้าหน้าที่จะติดต่อกลับเพื่อยืนยันการนัดหมาย</li>
                                </ul>
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <a href="bookings.php" class="btn btn-secondary">ยกเลิก</a>
                                <button type="submit" class="btn btn-success">✅ ยืนยันการจอง</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
