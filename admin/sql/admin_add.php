<?php
require_once '../../config.php';

// ตรวจสอบว่า login และเป็น Super Admin
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'super_admin') {
    header('Location: ../../login.php');
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    // ตรวจสอบรหัสผ่านตรงกันหรือไม่
    if ($password !== $confirm_password) {
        header('Location: ../admins.php?error=password_mismatch');
        exit();
    }

    // ตรวจสอบว่า Username ซ้ำหรือไม่
    $check_sql = "SELECT admin_id FROM admins WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        header('Location: ../admins.php?error=username_exists');
        exit();
    }

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // INSERT ข้อมูล Admin ใหม่
    $sql = "INSERT INTO admins (username, password, full_name, email, role, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $hashed_password, $full_name, $email, $role);

    if ($stmt->execute()) {
        header('Location: ../admins.php?success=added');
    } else {
        header('Location: ../admins.php?error=failed');
    }
    exit();
} else {
    header('Location: ../admins.php');
    exit();
}
?>
