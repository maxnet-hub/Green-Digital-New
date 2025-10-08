<?php
require_once '../../config.php';

// ตรวจสอบว่าเป็น Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลมา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $province = trim($_POST['province']);
    $district = trim($_POST['district']);
    $user_level = $_POST['user_level'];
    $status = $_POST['status'];

    // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
    if ($password !== $confirm_password) {
        header("Location: ../users.php?error=password_mismatch");
        exit();
    }

    // ตรวจสอบว่า email ซ้ำหรือไม่
    $check_sql = "SELECT user_id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        header("Location: ../users.php?error=email_exists");
        exit();
    }

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // เพิ่มสมาชิกใหม่
    $sql = "INSERT INTO users (email, password, first_name, last_name, phone, address, province, district, user_level, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $email, $hashed_password, $first_name, $last_name, $phone, $address, $province, $district, $user_level, $status);

    if ($stmt->execute()) {
        header("Location: ../users.php?success=added");
    } else {
        header("Location: ../users.php?error=failed");
    }

    $stmt->close();
    $check_stmt->close();
} else {
    header("Location: ../users.php");
}

$conn->close();
?>
