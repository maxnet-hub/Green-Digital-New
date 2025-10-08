<?php
require_once '../../config.php';

// ตรวจสอบว่า login และเป็น Super Admin
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'super_admin') {
    header('Location: ../../login.php');
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_POST['admin_id'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    // ถ้ามีการกรอกรหัสผ่านใหม่
    if (!empty($password)) {
        // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
        if ($password !== $confirm_password) {
            header('Location: ../admins.php?error=password_mismatch');
            exit();
        }

        // เข้ารหัสรหัสผ่านใหม่
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // UPDATE พร้อมรหัสผ่านใหม่
        $sql = "UPDATE admins SET full_name = ?, email = ?, role = ?, password = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $full_name, $email, $role, $hashed_password, $admin_id);
    } else {
        // UPDATE โดยไม่เปลี่ยนรหัสผ่าน
        $sql = "UPDATE admins SET full_name = ?, email = ?, role = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $full_name, $email, $role, $admin_id);
    }

    if ($stmt->execute()) {
        header('Location: ../admins.php?success=updated');
    } else {
        header('Location: ../admins.php?error=failed');
    }
    exit();
} else {
    header('Location: ../admins.php');
    exit();
}
?>
