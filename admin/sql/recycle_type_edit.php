<?php
require_once '../../config.php';

// ตรวจสอบว่าเป็น Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลมา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type_id = $_POST['type_id'];
    $type_name = $_POST['type_name'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // อัปเดตประเภทขยะ
    $sql = "UPDATE recycle_types
            SET type_name = '$type_name', description = '$description', status = '$status'
            WHERE type_id = '$type_id'";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: ../recycle_types.php?success=updated");
    } else {
        header("Location: ../recycle_types.php?error=failed");
    }
} else {
    header("Location: ../recycle_types.php");
}
?>
