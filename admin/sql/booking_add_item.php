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
    $type_id = isset($_POST['type_id']) ? intval($_POST['type_id']) : 0;
    $quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : 0;

    // เช็คค่า NULL
    if (empty($booking_id) || empty($type_id) || empty($quantity)) {
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

    // ดึงข้อมูล price_per_kg จาก prices และ co2_reduction จาก recycle_types
    $type_sql = "SELECT p.price_per_kg, rt.co2_reduction
                 FROM recycle_types rt
                 LEFT JOIN prices p ON rt.type_id = p.type_id AND p.is_current = TRUE
                 WHERE rt.type_id = ?";
    $stmt = $conn->prepare($type_sql);
    $stmt->bind_param('i', $type_id);
    $stmt->execute();
    $type_result = $stmt->get_result();

    if ($type_result->num_rows == 0) {
        $_SESSION['error'] = 'ไม่พบประเภทขยะนี้';
        header("Location: ../booking_detail.php?id=$booking_id");
        exit();
    }

    $type_data = $type_result->fetch_assoc();
    $price_per_kg = $type_data['price_per_kg'];

    // เช็คว่ามีราคาหรือไม่
    if (empty($price_per_kg) || $price_per_kg <= 0) {
        $_SESSION['error'] = 'ไม่พบราคารับซื้อสำหรับประเภทขยะนี้';
        header("Location: ../booking_detail.php?id=$booking_id");
        exit();
    }

    // คำนวณ subtotal
    $subtotal = $quantity * $price_per_kg;

    // เพิ่มข้อมูลลงในตาราง booking_items
    $insert_sql = "INSERT INTO booking_items (booking_id, type_id, quantity, price_per_kg, subtotal)
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param('iiddd', $booking_id, $type_id, $quantity, $price_per_kg, $subtotal);

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

        $_SESSION['success'] = 'เพิ่มรายการสำเร็จ';
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
