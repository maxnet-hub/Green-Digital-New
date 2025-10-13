<?php
require_once '../../config.php';

// ตรวจสอบว่าเป็น Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลมา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type_name = $_POST['type_name'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // เพิ่มประเภทขยะ
    $sql = "INSERT INTO recycle_types (type_name, description, status)
            VALUES ('$type_name', '$description', '$status')";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: ../recycle_types.php?success=added");
    } else {
        header("Location: ../recycle_types.php?error=failed");
    }
} else {
    header("Location: ../recycle_types.php");
}
?>
