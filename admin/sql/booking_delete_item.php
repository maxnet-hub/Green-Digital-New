<?php
session_start();
require_once '../../config.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

// รับค่า booking_id และ item_id จาก GET
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;

// เช็คค่า NULL
if (empty($booking_id) || empty($item_id)) {
    $_SESSION['error'] = 'ข้อมูลไม่ครบถ้วน';
    if ($booking_id > 0) {
        header("Location: ../booking_detail.php?id=$booking_id");
    } else {
        header("Location: ../bookings.php");
    }
    exit();
}

// เช็คว่ามีรายการนี้อยู่จริง
$check_sql = "SELECT item_id FROM booking_items WHERE item_id = ? AND booking_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param('ii', $item_id, $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'ไม่พบรายการนี้';
    header("Location: ../booking_detail.php?id=$booking_id");
    exit();
}

// ลบข้อมูลจากตาราง booking_items
$delete_sql = "DELETE FROM booking_items WHERE item_id = ?";
$stmt = $conn->prepare($delete_sql);
$stmt->bind_param('i', $item_id);

if ($stmt->execute()) {
    // อัพเดท transactions ถ้ามี
    $trans_check = "SELECT transaction_id FROM transactions WHERE booking_id = ? LIMIT 1";
    $stmt_trans = $conn->prepare($trans_check);
    $stmt_trans->bind_param('i', $booking_id);
    $stmt_trans->execute();
    $trans_result = $stmt_trans->get_result();

    if ($trans_result->num_rows > 0) {
        // คำนวณยอดรวมใหม่
        $total_sql = "SELECT SUM(subtotal) as total_amount FROM booking_items WHERE booking_id = ?";
        $stmt_total = $conn->prepare($total_sql);
        $stmt_total->bind_param('i', $booking_id);
        $stmt_total->execute();
        $total_result = $stmt_total->get_result();
        $total_data = $total_result->fetch_assoc();
        $new_total = $total_data['total_amount'] ? $total_data['total_amount'] : 0;

        // อัพเดท transaction
        $update_trans = "UPDATE transactions SET total_amount = ? WHERE booking_id = ?";
        $stmt_update_trans = $conn->prepare($update_trans);
        $stmt_update_trans->bind_param('di', $new_total, $booking_id);
        $stmt_update_trans->execute();
    }

    $_SESSION['success'] = 'ลบรายการสำเร็จ';
} else {
    $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $conn->error;
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();

// Redirect กลับหน้าเดิม
header("Location: ../booking_detail.php?id=$booking_id");
exit();
?>
