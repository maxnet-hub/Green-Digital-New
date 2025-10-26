-- SQL สำหรับอัพเดทตาราง admins ให้รองรับ role ใหม่
-- ใช้คำสั่งนี้ถ้าฐานข้อมูลสร้างไปแล้ว
-- Role: admin (แอดมิน), owner (เจ้าของร้าน), staff (พนักงาน)

USE green_digital;

-- แก้ไข ENUM ให้รองรับ role ใหม่
ALTER TABLE admins
MODIFY COLUMN role ENUM('admin', 'owner', 'staff') DEFAULT 'staff';

-- ตรวจสอบผลลัพธ์
SELECT 'Update complete! Admin table now supports new roles: admin, owner, staff.' AS message;
