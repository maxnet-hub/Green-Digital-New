<?php
require_once '../config.php';

// ตรวจสอบว่าเป็น Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// ดึงข้อมูลธุรกรรม
$sql = "SELECT t.*, u.full_name as user_name, u.phone, p.promotion_name, p.code as promo_code
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.user_id
        LEFT JOIN promotions p ON t.promotion_id = p.promotion_id
        ORDER BY t.transaction_date DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการธุรกรรม - Green Digital</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>💳 จัดการธุรกรรม</h2>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php
                if($_GET['success'] == 'updated') echo 'อัปเดตสถานะธุรกรรมสำเร็จ';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>รหัส</th>
                                <th>วันที่</th>
                                <th>ผู้ใช้</th>
                                <th>ยอดรวม</th>
                                <th>ส่วนลด</th>
                                <th>ยอดสุทธิ</th>
                                <th>สถานะ</th>
                                <th>การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result && mysqli_num_rows($result) > 0): ?>
                                <?php while($trans = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $trans['transaction_id']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
                                        <td>
                                            <?php echo $trans['user_name']; ?><br>
                                            <small class="text-muted"><?php echo $trans['phone']; ?></small>
                                        </td>
                                        <td><?php echo number_format($trans['total_amount'], 2); ?> บาท</td>
                                        <td>
                                            <?php if($trans['discount_amount'] > 0): ?>
                                                <span class="text-success">-<?php echo number_format($trans['discount_amount'], 2); ?> บาท</span><br>
                                                <small class="text-muted"><?php echo $trans['promo_code']; ?></small>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo number_format($trans['final_amount'], 2); ?> บาท</strong></td>
                                        <td>
                                            <?php if($trans['status'] == 'completed'): ?>
                                                <span class="badge bg-success">สำเร็จ</span>
                                            <?php elseif($trans['status'] == 'pending'): ?>
                                                <span class="badge bg-warning">รอดำเนินการ</span>
                                            <?php elseif($trans['status'] == 'cancelled'): ?>
                                                <span class="badge bg-danger">ยกเลิก</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $trans['transaction_id']; ?>">ดู</button>
                                            <?php if($trans['status'] == 'pending'): ?>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $trans['transaction_id']; ?>">แก้ไข</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">ไม่มีข้อมูลธุรกรรม</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
    if($result && mysqli_num_rows($result) > 0):
    mysqli_data_seek($result, 0);
    while($trans = mysqli_fetch_assoc($result)):
    ?>
        <!-- Modal ดูธุรกรรม -->
        <div class="modal fade" id="viewModal<?php echo $trans['transaction_id']; ?>">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">รายละเอียดธุรกรรม #<?php echo $trans['transaction_id']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>ข้อมูลผู้ใช้</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <th style="width: 40%;">ชื่อ:</th>
                                        <td><?php echo $trans['user_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>เบอร์โทร:</th>
                                        <td><?php echo $trans['phone']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>ข้อมูลธุรกรรม</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <th style="width: 40%;">วันที่:</th>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($trans['transaction_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>สถานะ:</th>
                                        <td>
                                            <?php if($trans['status'] == 'completed'): ?>
                                                <span class="badge bg-success">สำเร็จ</span>
                                            <?php elseif($trans['status'] == 'pending'): ?>
                                                <span class="badge bg-warning">รอดำเนินการ</span>
                                            <?php elseif($trans['status'] == 'cancelled'): ?>
                                                <span class="badge bg-danger">ยกเลิก</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <h6>รายการขยะ</h6>
                        <?php
                        // ดึงรายละเอียดธุรกรรม
                        $detail_sql = "SELECT td.*, rt.type_name, rt.unit
                                      FROM transaction_details td
                                      LEFT JOIN recycle_types rt ON td.type_id = rt.type_id
                                      WHERE td.transaction_id = '".$trans['transaction_id']."'";
                        $detail_result = mysqli_query($conn, $detail_sql);
                        ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ประเภทขยะ</th>
                                    <th class="text-end">น้ำหนัก</th>
                                    <th class="text-end">ราคา/หน่วย</th>
                                    <th class="text-end">ยอดรวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($detail = mysqli_fetch_assoc($detail_result)): ?>
                                    <tr>
                                        <td><?php echo $detail['type_name']; ?></td>
                                        <td class="text-end"><?php echo number_format($detail['weight'], 2); ?> <?php echo $detail['unit']; ?></td>
                                        <td class="text-end"><?php echo number_format($detail['price_per_unit'], 2); ?> บาท</td>
                                        <td class="text-end"><?php echo number_format($detail['subtotal'], 2); ?> บาท</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <th>ยอดรวม:</th>
                                        <td class="text-end"><?php echo number_format($trans['total_amount'], 2); ?> บาท</td>
                                    </tr>
                                    <?php if($trans['discount_amount'] > 0): ?>
                                        <tr>
                                            <th>ส่วนลด (<?php echo $trans['promo_code']; ?>):</th>
                                            <td class="text-end text-success">-<?php echo number_format($trans['discount_amount'], 2); ?> บาท</td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="border-top">
                                        <th>ยอดสุทธิ:</th>
                                        <th class="text-end"><?php echo number_format($trans['final_amount'], 2); ?> บาท</th>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <?php if($trans['payment_slip']): ?>
                            <hr>
                            <h6>สลิปการโอนเงิน</h6>
                            <img src="../<?php echo $trans['payment_slip']; ?>" class="img-fluid" style="max-height: 400px;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal แก้ไขสถานะ -->
        <?php if($trans['status'] == 'pending'): ?>
        <div class="modal fade" id="editModal<?php echo $trans['transaction_id']; ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">แก้ไขสถานะธุรกรรม #<?php echo $trans['transaction_id']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="sql/transaction_update.php" method="POST">
                        <input type="hidden" name="transaction_id" value="<?php echo $trans['transaction_id']; ?>">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" <?php if($trans['status']=='pending') echo 'selected'; ?>>รอดำเนินการ</option>
                                    <option value="completed" <?php if($trans['status']=='completed') echo 'selected'; ?>>สำเร็จ</option>
                                    <option value="cancelled" <?php if($trans['status']=='cancelled') echo 'selected'; ?>>ยกเลิก</option>
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
        <?php endif; ?>
    <?php endwhile; endif; ?>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
