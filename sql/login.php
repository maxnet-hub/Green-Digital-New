<?php
require_once '../config.php';

// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ดึงข้อมูล admin จากฐานข้อมูล
    $sql = "SELECT admin_id, username, password, full_name, role FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $admin['password'])) {
            // บันทึกข้อมูลลง Session
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['full_name'] = $admin['full_name'];
            $_SESSION['role'] = $admin['role'];

            // ไปหน้า Dashboard
            header('Location: ../admin/dashboard.php');
            exit();
        } else {
            // รหัสผ่านผิด
            header('Location: ../login.php?error=1');
            exit();
        }
    } else {
        // ไม่พบ username
        header('Location: ../login.php?error=1');
        exit();
    }
} else {
    // ถ้าไม่ได้ส่ง POST ให้กลับหน้า login
    header('Location: ../login.php');
    exit();
}
?>
