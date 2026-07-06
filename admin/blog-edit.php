<?php
session_start();
require_once '../config.php';
require_once '../auth.php';

// Require admin login
requireAdmin();

$user = getCurrentUser();
$success = '';
$error = '';
$articleId = $_GET['id'] ?? 0;

// Get article data
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM blog_articles WHERE id = ?");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header('Location: /admin/blog');
        exit;
    }
} catch (Exception $e) {
    header('Location: /admin/blog');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? 'General';
    $tags = trim($_POST['tags'] ?? '');
    $featuredImage = trim($_POST['featured_image'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required';
    } else {
        try {
            // Check if slug already exists (excluding current article)
            $checkStmt = $pdo->prepare("SELECT id FROM blog_articles WHERE slug = ? AND id != ?");
            $checkStmt->execute([$slug, $articleId]);
            if ($checkStmt->fetch()) {
                $error = 'Slug already exists. Please use a different slug.';
            } else {
                // Update published_at if status changed to published
                $publishedAt = $article['published_at'];
                if ($status === 'published' && $article['status'] !== 'published') {
                    $publishedAt = date('Y-m-d H:i:s');
                }
                
                $stmt = $pdo->prepare("
                    UPDATE blog_articles 
                    SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?, 
                        category = ?, tags = ?, status = ?, published_at = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $slug, $excerpt, $content, $featuredImage, $category, $tags, $status, $publishedAt, $articleId]);
                
                $_SESSION['blog_success'] = 'Article updated successfully';
                header('Location: /admin/blog');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Failed to update article: ' . $e->getMessage();
        }
    }
}

// Get categories
try {
    $categoriesStmt = $pdo->query("SELECT DISTINCT category FROM blog_articles ORDER BY category");
    $existingCategories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $existingCategories = [];
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">
    
    <nav class="bg-gray-800 border-b border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <a href="/admin" class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-sm"></i>
                    </div>
                    <span class="text-xl font-bold">Admin Panel</span>
                </a>
            </div>
            <a href="/admin/blog" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Blog
            </a>
        </div>
    </nav>

    <div class="p-6">
        <div class="max-w-5xl mx-auto">
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2">Edit Article</h1>
                <p class="text-gray-400">Update article content and settings</p>
            </div>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Title *</label>
                        <input 
                            type="text" 
                            name="title" 
                            value="<?php echo htmlspecialchars($article['title']); ?>"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                            required
                        >
                    </div>
                    
                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Slug (URL)</label>
                        <input 
                            type="text" 
                            name="slug" 
                            value="<?php echo htmlspecialchars($article['slug']); ?>"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition font-mono text-sm"
                            required
                        >
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl p-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-2">Excerpt</label>
                    <textarea 
                        name="excerpt" 
                        rows="3"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                    ><?php echo htmlspecialchars($article['excerpt']); ?></textarea>
                </div>

                <div class="bg-gray-800 rounded-xl p-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-2">Content *</label>
                    <textarea 
                        name="content" 
                        id="content-editor"
                        class="w-full"
                        rows="20"
                    ><?php echo htmlspecialchars($article['content']); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Category</label>
                        <input 
                            type="text" 
                            name="category" 
                            list="categories-list"
                            value="<?php echo htmlspecialchars($article['category']); ?>"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                        >
                        <datalist id="categories-list">
                            <?php foreach ($existingCategories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Status</label>
                        <select 
                            name="status"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                        >
                            <option value="draft" <?php echo $article['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $article['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $article['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>

                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Tags</label>
                        <input 
                            type="text" 
                            name="tags" 
                            value="<?php echo htmlspecialchars($article['tags']); ?>"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                        >
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl p-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-2">Featured Image URL</label>
                    <input 
                        type="url" 
                        name="featured_image" 
                        value="<?php echo htmlspecialchars($article['featured_image']); ?>"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition font-mono text-sm"
                    >
                </div>

                <div class="flex items-center justify-between pt-6">
                    <a href="/admin/blog" class="text-gray-400 hover:text-white transition">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button 
                        type="submit"
                        class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-semibold px-8 py-3 rounded-lg transition"
                    >
                        <i class="fas fa-save mr-2"></i>Update Article
                    </button>
                </div>

            </form>

        </div>
    </div>

    <script>
        tinymce.init({
            selector: '#content-editor',
            height: 500,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image | code | help',
            content_style: 'body { font-family: Inter, sans-serif; font-size: 16px; line-height: 1.6; }',
            skin: 'oxide-dark',
            content_css: 'dark'
        });
    </script>

</body>
</html>
