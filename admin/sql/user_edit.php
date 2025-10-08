<?php
require_once '../../config.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $province = trim($_POST['province']);
    $district = trim($_POST['district']);
    $user_level = $_POST['user_level'];
    $total_points = intval($_POST['total_points']);
    $status = $_POST['status'];

    // UPDATE ข้อมูลสมาชิก
    $sql = "UPDATE users
            SET first_name = ?,
                last_name = ?,
                phone = ?,
                address = ?,
                province = ?,
                district = ?,
                user_level = ?,
                total_points = ?,
                status = ?,
                updated_at = NOW()
            WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi",
        $first_name,
        $last_name,
        $phone,
        $address,
        $province,
        $district,
        $user_level,
        $total_points,
        $status,
        $user_id
    );

    if ($stmt->execute()) {
        header('Location: ../users.php?success=updated');
    } else {
        header('Location: ../users.php?error=failed');
    }
    exit();
} else {
    header('Location: ../users.php');
    exit();
}
?>
