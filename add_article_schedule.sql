-- เพิ่มฟิลด์สำหรับตั้งเวลาเปิด-ปิดการแสดงผลบทความ
-- วันที่สร้าง: 2025-10-28

USE green_digital;

-- เพิ่มฟิลด์ published_start และ published_end ในตาราง articles
ALTER TABLE articles
ADD COLUMN published_start DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'วันเวลาเริ่มแสดงบทความ',
ADD COLUMN published_end DATETIME NULL COMMENT 'วันเวลาสิ้นสุดการแสดง (NULL = ไม่มีวันหมดอายุ)';

-- อัปเดตบทความที่มีอยู่แล้วให้มี published_start = published_at (ถ้ามี) หรือ created_at
UPDATE articles
SET published_start = COALESCE(published_at, created_at)
WHERE published_start IS NULL;

-- สร้าง index เพื่อเพิ่มประสิทธิภาพการ query
ALTER TABLE articles
ADD INDEX idx_published_start (published_start),
ADD INDEX idx_published_end (published_end);
