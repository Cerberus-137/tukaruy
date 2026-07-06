<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Get category filter
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

try {
    $pdo = getDBConnection();
    
    // Build query based on filters
    $where = ["status = 'published'"];
    $params = [];
    
    if ($category !== 'all') {
        $where[] = "category = ?";
        $params[] = $category;
    }
    
    if (!empty($search)) {
        $where[] = "(title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_articles WHERE {$whereClause}");
    $countStmt->execute($params);
    $totalArticles = $countStmt->fetchColumn();
    $totalPages = ceil($totalArticles / $perPage);
    
    // Get articles
    $stmt = $pdo->prepare("
        SELECT a.*, u.first_name, u.last_name 
        FROM blog_articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE {$whereClause}
        ORDER BY published_at DESC
        LIMIT ? OFFSET ?
    ");
    $params[] = $perPage;
    $params[] = $offset;
    $stmt->execute($params);
    $articles = $stmt->fetchAll();
    
    // Get categories with counts
    $categoriesStmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM blog_articles 
        WHERE status = 'published' 
        GROUP BY category
    ");
    $categories = $categoriesStmt->fetchAll();
    
} catch (Exception $e) {
    error_log('Blog error: ' . $e->getMessage());
    $articles = [];
    $categories = [];
    $totalPages = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .article-card { transition: all 0.3s ease; }
        .article-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
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

    <!-- Hero Section -->
    <section class="py-16 bg-gradient-to-r from-purple-600 to-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl font-black mb-4">Blog & Resources</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Tips, tutorials, dan update terbaru tentang tracking pengiriman dan e-commerce</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                
                <!-- Sidebar -->
                <aside class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">
                        
                        <!-- Search -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="font-bold text-lg mb-4 flex items-center">
                                <i class="fas fa-search text-purple-500 mr-2"></i>
                                Search
                            </h3>
                            <form method="GET" action="">
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        name="search" 
                                        value="<?php echo htmlspecialchars($search); ?>"
                                        placeholder="Search articles..." 
                                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    >
                                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-purple-600 hover:text-purple-700">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Categories -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="font-bold text-lg mb-4 flex items-center">
                                <i class="fas fa-folder text-purple-500 mr-2"></i>
                                Categories
                            </h3>
                            <ul class="space-y-2">
                                <li>
                                    <a href="?category=all" class="flex items-center justify-between py-2 px-3 rounded-lg transition <?php echo $category === 'all' ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100'; ?>">
                                        <span>All Articles</span>
                                        <span class="text-sm text-gray-500"><?php echo $totalArticles; ?></span>
                                    </a>
                                </li>
                                <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a href="?category=<?php echo urlencode($cat['category']); ?>" class="flex items-center justify-between py-2 px-3 rounded-lg transition <?php echo $category === $cat['category'] ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100'; ?>">
                                        <span><?php echo htmlspecialchars($cat['category']); ?></span>
                                        <span class="text-sm text-gray-500"><?php echo $cat['count']; ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- CTA -->
                        <div class="bg-gradient-to-br from-purple-600 to-blue-600 rounded-xl shadow-lg p-6 text-white text-center">
                            <i class="fas fa-rocket text-4xl mb-3 opacity-80"></i>
                            <h3 class="font-bold text-xl mb-2">Ready to Start?</h3>
                            <p class="text-sm opacity-90 mb-4">Join thousands of users tracking packages worldwide</p>
                            <a href="/register" class="block bg-white text-purple-600 font-bold py-2 px-4 rounded-lg hover:bg-gray-100 transition">
                                Get Started Free
                            </a>
                        </div>

                    </div>
                </aside>

                <!-- Articles Grid -->
                <div class="lg:col-span-3">
                    
                    <?php if (empty($articles)): ?>
                    <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-700 mb-2">No Articles Found</h3>
                        <p class="text-gray-500">Try adjusting your search or filter criteria</p>
                    </div>
                    <?php else: ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                        <?php foreach ($articles as $article): ?>
                        <article class="article-card bg-white rounded-xl shadow-lg overflow-hidden">
                            <?php if ($article['featured_image']): ?>
                            <div class="h-48 bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($article['featured_image']); ?>')"></div>
                            <?php else: ?>
                            <div class="h-48 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center">
                                <i class="fas fa-image text-white text-4xl opacity-50"></i>
                            </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full">
                                        <?php echo htmlspecialchars($article['category']); ?>
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-eye mr-1"></i><?php echo number_format($article['views']); ?>
                                    </span>
                                </div>
                                
                                <h3 class="text-xl font-bold mb-2 hover:text-purple-600 transition">
                                    <a href="/blog-article?slug=<?php echo urlencode($article['slug']); ?>">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h3>
                                
                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                    <?php echo htmlspecialchars($article['excerpt'] ?: substr(strip_tags($article['content']), 0, 150) . '...'); ?>
                                </p>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">
                                        <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($article['first_name'] . ' ' . $article['last_name']); ?>
                                    </span>
                                    <span class="text-gray-400">
                                        <?php echo date('M d, Y', strtotime($article['published_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="flex justify-center items-center space-x-2">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>" class="px-4 py-2 rounded-lg transition <?php echo $i === $page ? 'bg-purple-600 text-white font-bold' : 'bg-white border border-gray-300 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12 mt-16">
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
                        <li><a href="/track" class="hover:text-white transition">Tracking</a></li>
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
