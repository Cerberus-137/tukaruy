<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /blog');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get article
    $stmt = $pdo->prepare("
        SELECT a.*, u.first_name, u.last_name, u.email 
        FROM blog_articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.slug = ? AND a.status = 'published'
    ");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header('Location: /blog');
        exit;
    }
    
    // Increment view count
    $pdo->prepare("UPDATE blog_articles SET views = views + 1 WHERE id = ?")->execute([$article['id']]);
    
    // Get related articles
    $relatedStmt = $pdo->prepare("
        SELECT * FROM blog_articles 
        WHERE category = ? AND id != ? AND status = 'published'
        ORDER BY published_at DESC
        LIMIT 3
    ");
    $relatedStmt->execute([$article['category'], $article['id']]);
    $relatedArticles = $relatedStmt->fetchAll();
    
} catch (Exception $e) {
    error_log('Blog article error: ' . $e->getMessage());
    header('Location: /blog');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($article['excerpt'] ?: substr(strip_tags($article['content']), 0, 160)); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .article-content { line-height: 1.8; }
        .article-content h2 { font-size: 1.875rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; }
        .article-content h3 { font-size: 1.5rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .article-content p { margin-bottom: 1rem; color: #4b5563; }
        .article-content ul, .article-content ol { margin-left: 1.5rem; margin-bottom: 1rem; }
        .article-content li { margin-bottom: 0.5rem; color: #4b5563; }
        .article-content a { color: #8b5cf6; text-decoration: underline; }
        .article-content img { border-radius: 0.5rem; margin: 1.5rem 0; }
        .article-content code { background: #f3f4f6; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-family: monospace; }
        .article-content pre { background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 1.5rem 0; }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shipping-fast text-white"></i>
                    </div>
                    <span class="text-2xl font-bold gradient-text">Tukarkuy</span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="/" class="text-gray-600 hover:text-gray-900 font-medium transition">Home</a>
                    <a href="/blog" class="text-purple-600 font-bold">Blog</a>
                    <a href="/contact" class="text-gray-600 hover:text-gray-900 font-medium transition">Contact</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="/track" class="bg-gradient-to-r from-purple-500 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">Dashboard</a>
                    <?php else: ?>
                        <a href="/login" class="text-gray-700 font-semibold transition">Login</a>
                        <a href="/register" class="bg-gradient-to-r from-purple-500 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Article Header -->
    <section class="py-12 bg-gradient-to-r from-purple-600 to-blue-600 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="/blog" class="inline-flex items-center text-white/80 hover:text-white mb-6 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Blog
            </a>
            
            <div class="mb-4">
                <span class="bg-white/20 text-white text-sm font-semibold px-4 py-2 rounded-full">
                    <?php echo htmlspecialchars($article['category']); ?>
                </span>
            </div>
            
            <h1 class="text-4xl lg:text-5xl font-black mb-6"><?php echo htmlspecialchars($article['title']); ?></h1>
            
            <div class="flex items-center space-x-6 text-white/90">
                <div class="flex items-center">
                    <i class="fas fa-user mr-2"></i>
                    <span><?php echo htmlspecialchars($article['first_name'] . ' ' . $article['last_name']); ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar mr-2"></i>
                    <span><?php echo date('F d, Y', strtotime($article['published_at'])); ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-eye mr-2"></i>
                    <span><?php echo number_format($article['views']); ?> views</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Article Content -->
    <section class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                
                <?php if ($article['featured_image']): ?>
                <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-96 object-cover">
                <?php endif; ?>
                
                <div class="p-8 lg:p-12">
                    <div class="article-content prose prose-lg max-w-none">
                        <?php echo $article['content']; ?>
                    </div>
                    
                    <?php if ($article['tags']): ?>
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-500 mb-3">TAGS</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach (explode(',', $article['tags']) as $tag): ?>
                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
                                <?php echo htmlspecialchars(trim($tag)); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Articles -->
            <?php if (!empty($relatedArticles)): ?>
            <div class="mt-12">
                <h2 class="text-3xl font-bold mb-6">Related Articles</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($relatedArticles as $related): ?>
                    <article class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                        <?php if ($related['featured_image']): ?>
                        <div class="h-40 bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($related['featured_image']); ?>')"></div>
                        <?php else: ?>
                        <div class="h-40 bg-gradient-to-br from-purple-400 to-blue-500"></div>
                        <?php endif; ?>
                        <div class="p-5">
                            <h3 class="text-lg font-bold mb-2 hover:text-purple-600 transition">
                                <a href="/blog-article?slug=<?php echo urlencode($related['slug']); ?>">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </a>
                            </h3>
                            <p class="text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($related['published_at'])); ?>
                            </p>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-shipping-fast text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-white">Tukarkuy</span>
                    </div>
                    <p class="text-sm">Track packages easily across multiple carriers worldwide.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="/#pricing" class="hover:text-white transition">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/blog" class="hover:text-white transition">Blog</a></li>
                        <li><a href="/contact" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/privacy-policy" class="hover:text-white transition">Privacy</a></li>
                        <li><a href="/tos" class="hover:text-white transition">Terms</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-sm text-center">
                <p>&copy; 2026 Tukarkuy. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
