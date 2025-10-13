<?php
require_once '../../config.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_id'];
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['status'];

    // Validate status
    $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        $_SESSION['error'] = 'สถานะไม่ถูกต้อง';
        header("Location: ../booking_detail.php?id=$booking_id");
        exit();
    }

    // ดึงข้อมูลการจองปัจจุบัน
    $check_sql = "SELECT * FROM bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['error'] = 'ไม่พบการจองนี้';
        header("Location: ../bookings.php");
        exit();
    }

    $booking = $result->fetch_assoc();
    $old_status = $booking['status'];

    // ห้ามเปลี่ยนสถานะถ้าถูกยกเลิกแล้ว
    if ($old_status == 'cancelled' && $new_status != 'cancelled') {
        $_SESSION['error'] = 'ไม่สามารถเปลี่ยนสถานะของการจองที่ถูกยกเลิกแล้ว';
        header("Location: ../booking_detail.php?id=$booking_id");
        exit();
    }

    // อัพเดทสถานะ
    $update_sql = "UPDATE bookings SET status = ?, updated_at = NOW() WHERE booking_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $new_status, $booking_id);

    if ($stmt->execute()) {
        // สร้างการแจ้งเตือนให้ผู้ใช้
        $status_text = [
            'pending' => 'รอดำเนินการ',
            'confirmed' => 'ยืนยันแล้ว',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก'
        ];

        $notification_title = 'อัพเดทสถานะการจอง';
        $notification_message = "การจองหมายเลข #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) .
                               " ได้เปลี่ยนสถานะเป็น: " . $status_text[$new_status];

        $notification_sql = "INSERT INTO notifications (user_id, title, message, type, is_read)
                            VALUES (?, ?, ?, 'booking', FALSE)";
        $stmt = $conn->prepare($notification_sql);
        $stmt->bind_param('iss', $booking['user_id'], $notification_title, $notification_message);
        $stmt->execute();

        $_SESSION['success'] = 'อัพเดทสถานะสำเร็จ';
    } else {
        $_SESSION['error'] = 'ไม่สามารถอัพเดทสถานะได้';
    }

    // Redirect
    if (isset($_POST['return_to']) && $_POST['return_to'] == 'list') {
        header("Location: ../bookings.php");
    } else {
        header("Location: ../booking_detail.php?id=$booking_id");
    }
    exit();
} else {
    header("Location: ../bookings.php");
    exit();
}
?>
