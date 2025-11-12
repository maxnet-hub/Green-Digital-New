<?php
session_start();
require_once 'config.php';

// ตั้งค่าสำหรับ navbar
$base_url = '';
$current_page = 'articles';

// รับค่า category จาก URL
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// ดึงบทความที่ published และอยู่ในช่วงเวลาแสดงผล
$where_clause = "a.status = 'published'
                 AND NOW() >= a.published_start
                 AND (a.published_end IS NULL OR NOW() <= a.published_end)";

if (!empty($category_filter)) {
    $category_escaped = mysqli_real_escape_string($conn, $category_filter);
    $sql = "SELECT a.*, ad.full_name as author_name
            FROM articles a
            LEFT JOIN admins ad ON a.author_id = ad.admin_id
            WHERE $where_clause AND a.category = '$category_escaped'
            ORDER BY a.published_at DESC";
} else {
    $sql = "SELECT a.*, ad.full_name as author_name
            FROM articles a
            LEFT JOIN admins ad ON a.author_id = ad.admin_id
            WHERE $where_clause
            ORDER BY a.published_at DESC";
}

$articles_result = mysqli_query($conn, $sql);

// ดึง category ทั้งหมด (เฉพาะบทความที่แสดงผลได้)
$category_sql = "SELECT DISTINCT category
                 FROM articles
                 WHERE $where_clause AND category IS NOT NULL
                 ORDER BY category";
$categories = mysqli_query($conn, $category_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บทความและความรู้ - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .article-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
            transition: transform 0.3s;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0f0f0;
        }

        .article-body {
            padding: 20px;
        }

        .article-category {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            margin-bottom: 10px;
        }

        .article-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .article-excerpt {
            color: #666;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .article-meta {
            color: #999;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .article-meta i {
            margin-right: 5px;
        }

        .btn-read-more {
            background: #10b981;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-read-more:hover {
            background: #059669;
            color: white;
        }

        .category-filter {
            margin-bottom: 30px;
        }

        .category-btn {
            margin: 5px;
        }

        .no-articles {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container text-center">
            <h1>📚 บทความและความรู้</h1>
            <p>เรียนรู้เกี่ยวกับการรีไซเคิล การคัดแยกขยะ และการดูแลสิ่งแวดล้อม</p>
        </div>
    </section>

    <div class="container">
        <!-- Category Filter -->
        <div class="category-filter text-center">
            <a href="articles.php" class="btn btn-outline-success category-btn <?php echo empty($category_filter) ? 'active' : ''; ?>">
                ทั้งหมด
            </a>
            <?php if($categories && mysqli_num_rows($categories) > 0): ?>
                <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                    <a href="articles.php?category=<?php echo urlencode($cat['category']); ?>"
                       class="btn btn-outline-success category-btn <?php echo $category_filter == $cat['category'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- Articles Grid -->
        <div class="row">
            <?php if($articles_result && mysqli_num_rows($articles_result) > 0): ?>
                <?php while($article = mysqli_fetch_assoc($articles_result)): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="article-card">
                            <?php if(!empty($article['image_url'])): ?>
                                <img src="<?php echo $article['image_url']; ?>"
                                     alt="<?php echo $article['title']; ?>"
                                     class="article-image">
                            <?php else: ?>
                                <div class="article-image d-flex align-items-center justify-content-center bg-light">
                                    <span class="display-1">📄</span>
                                </div>
                            <?php endif; ?>

                            <div class="article-body">
                                <?php if(!empty($article['category'])): ?>
                                    <span class="article-category"><?php echo htmlspecialchars($article['category']); ?></span>
                                <?php endif; ?>

                                <h3 class="article-title">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </h3>

                                <div class="article-excerpt">
                                    <?php
                                    $content = strip_tags($article['content']);
                                    echo htmlspecialchars(mb_substr($content, 0, 150)) . '...';
                                    ?>
                                </div>

                                <div class="article-meta">
                                    📅 <?php echo date('d/m/Y', strtotime($article['published_at'])); ?>
                                    <?php if(!empty($article['author_name'])): ?>
                                        | ✍️ <?php echo htmlspecialchars($article['author_name']); ?>
                                    <?php endif; ?>
                                    | 👁️ <?php echo number_format($article['views']); ?>
                                </div>

                                <a href="article_detail.php?id=<?php echo $article['article_id']; ?>"
                                   class="btn-read-more">
                                    อ่านต่อ →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="no-articles">
                        <h3>📭 ยังไม่มีบทความ</h3>
                        <p>กำลังเตรียมเนื้อหาที่น่าสนใจให้คุณ</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
