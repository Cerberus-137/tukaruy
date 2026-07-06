<?php
session_start();
require_once '../config.php';
require_once '../auth.php';

// Require admin login
requireAdmin();

$user = getCurrentUser();
$success = '';
$error = '';

// Handle delete article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $articleId = $_POST['article_id'] ?? 0;
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM blog_articles WHERE id = ?");
        $stmt->execute([$articleId]);
        $success = 'Article deleted successfully';
    } catch (Exception $e) {
        $error = 'Failed to delete article: ' . $e->getMessage();
    }
}

// Get all articles
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT a.*, u.first_name, u.last_name 
        FROM blog_articles a
        LEFT JOIN users u ON a.author_id = u.id
        ORDER BY created_at DESC
    ");
    $articles = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Admin blog error: ' . $e->getMessage());
    $articles = [];
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">
    
    <!-- Top Navigation -->
    <nav class="bg-gray-800 border-b border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <a href="/admin" class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-sm"></i>
                    </div>
                    <span class="text-xl font-bold">Admin Panel</span>
                </a>
                <div class="flex items-center space-x-6 text-sm">
                    <a href="/admin" class="text-gray-400 hover:text-white transition">Dashboard</a>
                    <a href="/admin/users" class="text-gray-400 hover:text-white transition">Users</a>
                    <a href="/admin/packages" class="text-gray-400 hover:text-white transition">Packages</a>
                    <a href="/admin/payment-methods" class="text-gray-400 hover:text-white transition">Payments</a>
                    <a href="/admin/blog" class="text-white font-medium">Blog</a>
                    <a href="/admin/contact" class="text-gray-400 hover:text-white transition">Contact</a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-400"><?php echo htmlspecialchars($user['first_name']); ?></span>
                <a href="/logout" class="text-red-400 hover:text-red-300"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="p-6">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Blog Management</h1>
                    <p class="text-gray-400">Manage blog articles and content</p>
                </div>
                <a href="/admin/blog-create" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Create New Article
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
            <div class="mb-6 bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <!-- Articles Table -->
            <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Title</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Author</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Views</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Published</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <p>No articles yet. Create your first article!</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                        <tr class="hover:bg-gray-750 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <?php if ($article['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="" class="w-12 h-12 rounded-lg object-cover">
                                    <?php else: ?>
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-image text-white"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($article['title']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($article['slug']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-purple-500/20 text-purple-400 text-xs font-semibold px-2 py-1 rounded">
                                    <?php echo htmlspecialchars($article['category']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php echo htmlspecialchars($article['first_name'] . ' ' . $article['last_name']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusColors = [
                                    'draft' => 'bg-yellow-500/20 text-yellow-400',
                                    'published' => 'bg-green-500/20 text-green-400',
                                    'archived' => 'bg-gray-500/20 text-gray-400'
                                ];
                                $colorClass = $statusColors[$article['status']] ?? 'bg-gray-500/20 text-gray-400';
                                ?>
                                <span class="<?php echo $colorClass; ?> text-xs font-semibold px-2 py-1 rounded capitalize">
                                    <?php echo htmlspecialchars($article['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <i class="fas fa-eye text-gray-500 mr-1"></i><?php echo number_format($article['views']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                <?php echo $article['published_at'] ? date('M d, Y', strtotime($article['published_at'])) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="/blog-article?slug=<?php echo urlencode($article['slug']); ?>" target="_blank" class="text-blue-400 hover:text-blue-300 px-3 py-1 rounded transition" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/blog-edit?id=<?php echo $article['id']; ?>" class="text-purple-400 hover:text-purple-300 px-3 py-1 rounded transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-300 px-3 py-1 rounded transition" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>
