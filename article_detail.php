<?php
session_start();
require_once 'config.php';

// ตั้งค่าสำหรับ navbar
$base_url = '';
$current_page = 'articles';

// รับค่า article_id จาก URL
$article_id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($article_id) || !is_numeric($article_id)) {
    header("Location: articles.php");
    exit();
}

// เพิ่มจำนวน views
$update_views = "UPDATE articles SET views = views + 1 WHERE article_id = '$article_id'";
mysqli_query($conn, $update_views);

// ดึงข้อมูลบทความ
$sql = "SELECT a.*, ad.full_name as author_name
        FROM articles a
        LEFT JOIN admins ad ON a.author_id = ad.admin_id
        WHERE a.article_id = '$article_id' AND a.status = 'published'";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: articles.php");
    exit();
}

$article = mysqli_fetch_assoc($result);

// ดึงบทความที่เกี่ยวข้อง (category เดียวกัน)
$related_sql = "SELECT * FROM articles
                WHERE category = '{$article['category']}'
                AND article_id != '$article_id'
                AND status = 'published'
                ORDER BY published_at DESC
                LIMIT 3";
$related_articles = mysqli_query($conn, $related_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Green Digital</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .article-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
        }

        .article-category {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .article-title {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .article-meta {
            font-size: 1em;
            opacity: 0.9;
        }

        .article-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .article-content {
            font-size: 1.1em;
            line-height: 1.8;
            color: #333;
            margin-bottom: 40px;
        }

        .article-content p {
            margin-bottom: 20px;
        }

        .related-articles {
            background: #f8f9fa;
            padding: 40px 0;
            margin-top: 60px;
        }

        .related-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }

        .related-card:hover {
            transform: translateY(-5px);
        }

        .related-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #f0f0f0;
        }

        .related-body {
            padding: 15px;
        }

        .related-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .back-button {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Article Header -->
    <section class="article-header">
        <div class="container">
            <?php if(!empty($article['category'])): ?>
                <span class="article-category"><?php echo htmlspecialchars($article['category']); ?></span>
            <?php endif; ?>

            <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>

            <div class="article-meta">
                📅 <?php echo date('d F Y', strtotime($article['published_at'])); ?>
                <?php if(!empty($article['author_name'])): ?>
                    | ✍️ <?php echo htmlspecialchars($article['author_name']); ?>
                <?php endif; ?>
                | 👁️ <?php echo number_format($article['views']); ?> ครั้ง
            </div>
        </div>
    </section>

    <!-- Article Content -->
    <div class="container">
        <div class="back-button">
            <a href="articles.php" class="btn btn-outline-success">← กลับไปหน้าบทความ</a>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php if(!empty($article['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars(ltrim($article['image_url'], '/')); ?>"
                         alt="<?php echo htmlspecialchars($article['title']); ?>"
                         class="article-image">
                <?php endif; ?>

                <div class="article-content">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>

                <hr>

                <div class="text-center my-4">
                    <a href="articles.php" class="btn btn-success">อ่านบทความอื่นๆ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Articles -->
    <?php if($related_articles && mysqli_num_rows($related_articles) > 0): ?>
    <section class="related-articles">
        <div class="container">
            <h3 class="text-center mb-4">📌 บทความที่เกี่ยวข้อง</h3>

            <div class="row">
                <?php while($related = mysqli_fetch_assoc($related_articles)): ?>
                    <div class="col-md-4">
                        <a href="article_detail.php?id=<?php echo $related['article_id']; ?>" class="text-decoration-none">
                            <div class="related-card">
                                <?php if(!empty($related['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars(ltrim($related['image_url'], '/')); ?>"
                                         alt="<?php echo htmlspecialchars($related['title']); ?>"
                                         class="related-image">
                                <?php else: ?>
                                    <div class="related-image d-flex align-items-center justify-content-center bg-light">
                                        <span style="font-size: 2em;">📄</span>
                                    </div>
                                <?php endif; ?>

                                <div class="related-body">
                                    <h5 class="related-title"><?php echo htmlspecialchars($related['title']); ?></h5>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($related['published_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
