-- ===================================================
-- SQL Script: เพิ่มระบบแต้มสะสม (Points System)
-- สำหรับแพลตฟอร์มรับซื้อขยะรีไซเคิล Green Digital
-- ===================================================

-- 1. เพิ่มฟิลด์ points ในตาราง users
-- ===================================================
ALTER TABLE users
ADD COLUMN points INT DEFAULT 0 COMMENT 'แต้มสะสมปัจจุบัน';

-- 2. สร้างตาราง point_transactions (ประวัติการทำรายการแต้ม)
-- ===================================================
CREATE TABLE IF NOT EXISTS point_transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'รหัสรายการแต้ม',
    user_id INT NOT NULL COMMENT 'รหัสผู้ใช้',
    booking_id INT NULL COMMENT 'รหัสการจอง (ถ้ามี)',
    points INT NOT NULL COMMENT 'จำนวนแต้ม (+ คือได้รับ, - คือใช้)',
    amount DECIMAL(10,2) NULL COMMENT 'ยอดเงินที่ใช้คำนวณ (ถ้ามี)',
    transaction_type ENUM('earn', 'redeem', 'adjustment') NOT NULL DEFAULT 'earn' COMMENT 'ประเภท: earn=ได้รับ, redeem=แลก, adjustment=ปรับปรุง',
    description TEXT NULL COMMENT 'คำอธิบายรายการ',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่ทำรายการ',

    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE SET NULL,

    -- Index
    INDEX idx_user_id (user_id),
    INDEX idx_booking_id (booking_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='ประวัติการทำรายการแต้มสะสม';

-- 3. อัพเดทข้อมูล users ที่มีอยู่ให้มีแต้มเป็น 0
-- ===================================================
UPDATE users SET points = 0 WHERE points IS NULL;

-- ===================================================
-- หมายเหตุ:
-- - อัตราการให้แต้ม: 100 บาท = 1 แต้ม
-- - ให้แต้มเฉพาะเมื่อ payment_status = 'paid'
-- - ตรวจสอบไม่ให้ซ้ำโดยเช็ค booking_id ในตาราง point_transactions
-- - ใช้ FLOOR(total_amount / 100) เพื่อปัดทศนิยมลง
-- ===================================================

-- ตัวอย่างการคำนวณแต้ม:
-- ยอดเงิน 1,250 บาท = FLOOR(1250/100) = 12 แต้ม
-- ยอดเงิน 999 บาท = FLOOR(999/100) = 9 แต้ม
-- ยอดเงิน 99 บาท = FLOOR(99/100) = 0 แต้ม
