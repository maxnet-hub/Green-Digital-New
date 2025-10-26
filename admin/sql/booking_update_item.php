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
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : 0;

    // เช็คค่า NULL
    if (empty($booking_id) || empty($item_id) || empty($quantity)) {
        $_SESSION['error'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        header("Location: ../booking_detail.php?id=$booking_id");
        exit();
    }

    // เช็คว่า quantity มากกว่า 0
    if ($quantity <= 0) {
        $_SESSION['error'] = 'น้ำหนักต้องมากกว่า 0';
        header("Location: ../booking_detail.php?id=$booking_id");
        exit();
    }

    // ดึง price_per_kg จาก booking_items
    $check_sql = "SELECT price_per_kg FROM booking_items WHERE item_id = ? AND booking_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param('ii', $item_id, $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['error'] = 'ไม่พบรายการนี้';
        header("Location: ../booking_detail.php?id=$booking_id");
        exit();
    }

    $item_data = $result->fetch_assoc();
    $price_per_kg = $item_data['price_per_kg'];

    // คำนวณ subtotal
    $subtotal = $quantity * $price_per_kg;

    // อัพเดทข้อมูลในตาราง booking_items
    $update_sql = "UPDATE booking_items SET quantity = ?, subtotal = ? WHERE item_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ddi', $quantity, $subtotal, $item_id);

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
            $new_total = $total_data['total_amount'];

            // อัพเดท transaction
            $update_trans = "UPDATE transactions SET total_amount = ? WHERE booking_id = ?";
            $stmt_update_trans = $conn->prepare($update_trans);
            $stmt_update_trans->bind_param('di', $new_total, $booking_id);
            $stmt_update_trans->execute();
        }

        $_SESSION['success'] = 'แก้ไขรายการสำเร็จ';
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $conn->error;
    }

    // ปิดการเชื่อมต่อ
    $stmt->close();
    $conn->close();

    // Redirect กลับหน้าเดิม
    header("Location: ../booking_detail.php?id=$booking_id");
    exit();
}

// ถ้าไม่ได้ส่งแบบ POST ให้กลับหน้าแรก
header("Location: ../bookings.php");
exit();
?>
