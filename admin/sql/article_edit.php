<?php
require_once '../../config.php';

// ตรวจสอบว่าเป็น Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลมา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $article_id = $_POST['article_id'];
    $title = $_POST['title'];
    $category = $_POST['category'];
    $content = $_POST['content'];
    $status = $_POST['status'];

    // ดึงข้อมูลบทความเดิม
    $check_sql = "SELECT image_url FROM articles WHERE article_id = '$article_id'";
    $check_result = mysqli_query($conn, $check_sql);
    $old_article = mysqli_fetch_assoc($check_result);
    $image_url = $old_article['image_url'];

    // จัดการอัปโหลดรูปภาพใหม่
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES['image']['size'];

        // ตรวจสอบไฟล์
        if (in_array($filetype, $allowed) && $filesize <= 5242880) { // 5MB
            // ลบรูปเก่า
            if ($image_url && file_exists('../../' . $image_url)) {
                unlink('../../' . $image_url);
            }

            // สร้างชื่อไฟล์ใหม่
            $newname = 'article_' . time() . '_' . uniqid() . '.' . $filetype;
            $upload_path = '../../uploads/articles/';

            // สร้างโฟลเดอร์ถ้ายังไม่มี
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            // อัปโหลดไฟล์
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $newname)) {
                $image_url = '/uploads/articles/' . $newname;
            }
        }
    }

    // อัปเดตบทความ
    $sql = "UPDATE articles
            SET title = '$title', category = '$category', content = '$content', image_url = '$image_url', status = '$status', updated_at = NOW()
            WHERE article_id = '$article_id'";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: ../articles.php?success=updated");
    } else {
        header("Location: ../articles.php?error=failed");
    }
} else {
    header("Location: ../articles.php");
}
?>
