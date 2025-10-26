<?php
session_start();
require_once '../../config.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

// เช็คว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // รับค่าจาก Form
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    $payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
    $total_weight = isset($_POST['total_weight']) ? floatval($_POST['total_weight']) : 0;
    $total_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // เช็คค่า NULL
    if (empty($booking_id) || empty($payment_method) || empty($payment_status)) {
        $_SESSION['error'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        header("Location: ../booking_payment.php?id=$booking_id");
        exit();
    }

    // Validate payment_method
    $allowed_methods = ['cash', 'bank_transfer', 'promptpay'];
    if (!in_array($payment_method, $allowed_methods)) {
        $_SESSION['error'] = 'วิธีการชำระเงินไม่ถูกต้อง';
        header("Location: ../booking_payment.php?id=$booking_id");
        exit();
    }

    // Validate payment_status
    $allowed_statuses = ['paid', 'pending', 'failed'];
    if (!in_array($payment_status, $allowed_statuses)) {
        $_SESSION['error'] = 'สถานะการชำระเงินไม่ถูกต้อง';
        header("Location: ../booking_payment.php?id=$booking_id");
        exit();
    }

    // ดึงข้อมูล booking
    $booking_sql = "SELECT * FROM bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($booking_sql);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $booking_result = $stmt->get_result();

    if ($booking_result->num_rows == 0) {
        $_SESSION['error'] = 'ไม่พบการจองนี้';
        header("Location: ../bookings.php");
        exit();
    }

    $booking = $booking_result->fetch_assoc();

    // เช็คว่าสถานะเป็น confirmed หรือไม่
    if ($booking['status'] != 'confirmed') {
        $_SESSION['error'] = 'ไม่สามารถชำระเงินได้ เนื่องจากสถานะไม่ใช่ "ยืนยันแล้ว"';
        header("Location: ../booking_detail.php?id=$booking_id");
        exit();
    }

    // เช็คว่ามี transaction อยู่แล้วหรือไม่
    $check_trans = "SELECT transaction_id FROM transactions WHERE booking_id = ? LIMIT 1";
    $stmt_check = $conn->prepare($check_trans);
    $stmt_check->bind_param('i', $booking_id);
    $stmt_check->execute();
    $trans_result = $stmt_check->get_result();

    if ($trans_result->num_rows > 0) {
        $_SESSION['error'] = 'มี transaction สำหรับการจองนี้อยู่แล้ว';
        header("Location: ../booking_detail.php?id=$booking_id");
        exit();
    }

    // เริ่ม Transaction
    $conn->begin_transaction();

    try {
        // 1. อัพเดทสถานะเป็น completed
        $update_booking = "UPDATE bookings SET status = 'completed', updated_at = NOW() WHERE booking_id = ?";
        $stmt_update = $conn->prepare($update_booking);
        $stmt_update->bind_param('i', $booking_id);
        $stmt_update->execute();

        // 2. สร้าง transaction
        $insert_trans = "INSERT INTO transactions (booking_id, user_id, total_weight, total_amount, payment_method, payment_status, payment_date, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt_insert = $conn->prepare($insert_trans);
        $stmt_insert->bind_param('iiddss', $booking_id, $booking['user_id'], $total_weight, $total_amount, $payment_method, $payment_status);
        $stmt_insert->execute();

        // 3. ระบบแต้มสะสม (Points System)
        // คำนวณแต้ม: 100 บาท = 1 แต้ม
        if ($payment_status == 'paid' && $total_amount > 0) {
            $points_earned = floor($total_amount / 100); // ปัดลง

            if ($points_earned > 0) {
                // เช็คว่าเคยให้แต้มสำหรับ booking_id นี้แล้วหรือยัง (ป้องกันการให้ซ้ำ)
                $check_points = "SELECT transaction_id FROM point_transactions WHERE booking_id = ? LIMIT 1";
                $stmt_check_points = $conn->prepare($check_points);
                $stmt_check_points->bind_param('i', $booking_id);
                $stmt_check_points->execute();
                $points_result = $stmt_check_points->get_result();

                if ($points_result->num_rows == 0) {
                    // อัพเดทแต้มในตาราง users
                    $update_points = "UPDATE users SET points = points + ? WHERE user_id = ?";
                    $stmt_update_points = $conn->prepare($update_points);
                    $stmt_update_points->bind_param('ii', $points_earned, $booking['user_id']);
                    $stmt_update_points->execute();

                    // บันทึกประวัติการได้แต้มในตาราง point_transactions
                    $points_description = "ได้รับแต้มจากการจองหมายเลข #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
                    $insert_points = "INSERT INTO point_transactions (user_id, booking_id, points, amount, transaction_type, description, created_at)
                                     VALUES (?, ?, ?, ?, 'earn', ?, NOW())";
                    $stmt_insert_points = $conn->prepare($insert_points);
                    $stmt_insert_points->bind_param('iiids', $booking['user_id'], $booking_id, $points_earned, $total_amount, $points_description);
                    $stmt_insert_points->execute();
                }
            }
        }

        // 4. สร้างการแจ้งเตือนให้ผู้ใช้
        $notification_title = 'การจองของคุณเสร็จสิ้นแล้ว';
        $notification_message = "การจองหมายเลข #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) .
                               " เสร็จสิ้นแล้ว ยอดชำระเงิน: " . number_format($total_amount, 2) . " บาท";

        // เพิ่มข้อความแต้มในการแจ้งเตือน (ถ้ามี)
        if ($payment_status == 'paid' && isset($points_earned) && $points_earned > 0) {
            $notification_message .= " | คุณได้รับ " . $points_earned . " แต้ม";
        }

        $notification_sql = "INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
                            VALUES (?, ?, ?, 'booking', FALSE, NOW())";
        $stmt_noti = $conn->prepare($notification_sql);
        $stmt_noti->bind_param('iss', $booking['user_id'], $notification_title, $notification_message);
        $stmt_noti->execute();

        // Commit Transaction
        $conn->commit();

        $_SESSION['success'] = 'ชำระเงินสำเร็จ! การจองเสร็จสิ้นแล้ว';

    } catch (Exception $e) {
        // Rollback ถ้ามีข้อผิดพลาด
        $conn->rollback();
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }

    // ปิดการเชื่อมต่อ
    $stmt->close();
    $conn->close();

    // Redirect กลับหน้า booking_detail
    header("Location: ../booking_detail.php?id=$booking_id");
    exit();
}

// ถ้าไม่ได้ส่งแบบ POST ให้กลับหน้าแรก
header("Location: ../bookings.php");
exit();
?>
