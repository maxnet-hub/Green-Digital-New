<?php
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบผู้ใช้ (ใช้ email หรือ phone)
    $sql = "SELECT * FROM users WHERE email = '$username' OR phone = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            // เข้าสู่ระบบสำเร็จ
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['email'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];

            header("Location: ../user/dashboard.php");
            exit();
        }
    }

    // ล็อกอินไม่สำเร็จ
    header("Location: ../user_login.php?error=1");
    exit();
}

header("Location: ../user_login.php");
?>
