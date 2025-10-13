<?php
require_once '../config.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Query บทความทั้งหมด
$sql = "SELECT a.*, ad.full_name as author_name
        FROM articles a
        LEFT JOIN admins ad ON a.author_id = ad.admin_id
        ORDER BY a.created_at DESC";
$result = $conn->query($sql);

// ตรวจสอบว่า query สำเร็จหรือไม่
if (!$result) {
    die("Error in query: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการบทความ - Green Digital</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <!-- Alert Messages -->
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong>สำเร็จ!</strong>
                <?php
                    if($_GET['success'] == 'added') echo 'เพิ่มบทความสำเร็จ!';
                    if($_GET['success'] == 'updated') echo 'แก้ไขบทความสำเร็จ!';
                    if($_GET['success'] == 'deleted') echo 'ลบบทความสำเร็จ!';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>เกิดข้อผิดพลาด!</strong>
                <?php
                    if($_GET['error'] == 'failed') echo 'ไม่สามารถดำเนินการได้ กรุณาลองใหม่อีกครั้ง';
                    if($_GET['error'] == 'upload_failed') echo 'อัปโหลดรูปภาพไม่สำเร็จ';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>📚 จัดการบทความ</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addArticleModal">
                ➕ เพิ่มบทความใหม่
            </button>
        </div>

        <!-- Articles Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="80">รูปภาพ</th>
                                <th>หัวข้อ</th>
                                <th>หมวดหมู่</th>
                                <th>ผู้เขียน</th>
                                <th width="120">สถานะ</th>
                                <th width="120">วันที่สร้าง</th>
                                <th width="150" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                                <?php while($article = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if($article['image_url']): ?>
                                                <img src="<?php echo $article['image_url']; ?>" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">📄</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $article['category']; ?></span>
                                        </td>
                                        <td><?php echo $article['author_name']; ?></td>
                                        <td>
                                            <?php if($article['status'] == 'published'): ?>
                                                <span class="badge bg-success">เผยแพร่แล้ว</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">แบบร่าง</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $article['article_id']; ?>">👁️</button>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $article['article_id']; ?>">✏️</button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteArticle(<?php echo $article['article_id']; ?>)">🗑️</button>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $article['article_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">📖 รายละเอียดบทความ</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php if($article['image_url']): ?>
                                                        <img src="<?php echo $article['image_url']; ?>" class="img-fluid mb-3">
                                                    <?php endif; ?>
                                                    <h4><?php echo htmlspecialchars($article['title']); ?></h4>
                                                    <p class="text-muted">
                                                        <small>
                                                            หมวดหมู่: <?php echo $article['category']; ?> |
                                                            ผู้เขียน: <?php echo $article['author_name']; ?> |
                                                            <?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?>
                                                        </small>
                                                    </p>
                                                    <hr>
                                                    <div><?php echo nl2br(htmlspecialchars($article['content'])); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $article['article_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="sql/article_edit.php" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="article_id" value="<?php echo $article['article_id']; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">✏️ แก้ไขบทความ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">หัวข้อบทความ <span class="text-danger">*</span></label>
                                                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($article['title']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                                            <select name="category" class="form-select" required>
                                                                <option value="การรีไซเคิล" <?php if($article['category']=='การรีไซเคิล') echo 'selected'; ?>>การรีไซเคิล</option>
                                                                <option value="สิ่งแวดล้อม" <?php if($article['category']=='สิ่งแวดล้อม') echo 'selected'; ?>>สิ่งแวดล้อม</option>
                                                                <option value="การลดขยะ" <?php if($article['category']=='การลดขยะ') echo 'selected'; ?>>การลดขยะ</option>
                                                                <option value="เทคโนโลยีสีเขียว" <?php if($article['category']=='เทคโนโลยีสีเขียว') echo 'selected'; ?>>เทคโนโลยีสีเขียว</option>
                                                                <option value="อื่นๆ" <?php if($article['category']=='อื่นๆ') echo 'selected'; ?>>อื่นๆ</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">เนื้อหา <span class="text-danger">*</span></label>
                                                            <textarea name="content" class="form-control" rows="8" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">รูปภาพ (ไม่เลือกหากไม่ต้องการเปลี่ยน)</label>
                                                            <?php if($article['image_url']): ?>
                                                                <div class="mb-2">
                                                                    <img src="<?php echo $article['image_url']; ?>" class="img-thumbnail" style="max-height: 150px;">
                                                                </div>
                                                            <?php endif; ?>
                                                            <input type="file" name="image" class="form-control" accept="image/*">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="draft" <?php if($article['status']=='draft') echo 'selected'; ?>>แบบร่าง</option>
                                                                <option value="published" <?php if($article['status']=='published') echo 'selected'; ?>>เผยแพร่</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <button type="submit" class="btn btn-primary">บันทึก</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">ยังไม่มีบทความ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Article Modal -->
    <div class="modal fade" id="addArticleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="sql/article_add.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">➕ เพิ่มบทความใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">หัวข้อบทความ <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="">-- เลือกหมวดหมู่ --</option>
                                <option value="การรีไซเคิล">การรีไซเคิล</option>
                                <option value="สิ่งแวดล้อม">สิ่งแวดล้อม</option>
                                <option value="การลดขยะ">การลดขยะ</option>
                                <option value="เทคโนโลยีสีเขียว">เทคโนโลยีสีเขียว</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เนื้อหา <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="8" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">รูปภาพ</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">รองรับ JPG, PNG, GIF (ขนาดไม่เกิน 5MB)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="draft">แบบร่าง</option>
                                <option value="published">เผยแพร่</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteArticle(id) {
            if(confirm('ต้องการลบบทความนี้หรือไม่?')) {
                window.location.href = 'sql/article_delete.php?id=' + id;
            }
        }
    </script>
</body>
</html>
