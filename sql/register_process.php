<?php
require_once '../config.php';

// ตรวจสอบว่ามีการส่งข้อมูลมา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // ตรวจสอบรหัสผ่าน
    if ($password != $confirm_password) {
        header("Location: ../register.php?error=password_mismatch");
        exit();
    }

    // แยกชื่อและนามสกุล
    $name_parts = explode(' ', $full_name, 2);
    $first_name = $name_parts[0];
    $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

    // ตรวจสอบ email และ phone ซ้ำ
    $check_sql = "SELECT * FROM users WHERE email = '$email' OR phone = '$phone'";
    $check_result = mysqli_query($conn, $check_sql);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        header("Location: ../register.php?error=duplicate");
        exit();
    }

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // เพิ่มสมาชิก
    $sql = "INSERT INTO users (password, first_name, last_name, phone, email, address)
            VALUES ('$hashed_password', '$first_name', '$last_name', '$phone', '$email', '$address')";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        // ดึง user_id ที่เพิ่งสร้าง
        $user_id = mysqli_insert_id($conn);

        // เข้าสู่ระบบอัตโนมัติ
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $email;
        $_SESSION['full_name'] = $full_name;

        header("Location: ../user/dashboard.php");
    } else {
        header("Location: ../register.php?error=failed");
    }
} else {
    header("Location: ../register.php");
}
?>
