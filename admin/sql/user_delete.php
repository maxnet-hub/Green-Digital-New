<?php
require_once '../../config.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

// ตรวจสอบว่ามีการส่ง ID มาหรือไม่
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // ลบสมาชิก (ข้อมูลที่เกี่ยวข้องจะถูกลบตาม ON DELETE CASCADE)
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header('Location: ../users.php?success=deleted');
    } else {
        header('Location: ../users.php?error=failed');
    }
    exit();
} else {
    header('Location: ../users.php');
    exit();
}
?>
