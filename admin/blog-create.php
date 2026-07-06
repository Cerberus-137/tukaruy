<?php
session_start();
require_once '../config.php';
require_once '../auth.php';

// Require admin login
requireAdmin();

$user = getCurrentUser();
$success = '';
$error = '';

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
    
    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Check if slug already exists
            $checkStmt = $pdo->prepare("SELECT id FROM blog_articles WHERE slug = ?");
            $checkStmt->execute([$slug]);
            if ($checkStmt->fetch()) {
                $error = 'Slug already exists. Please use a different slug.';
            } else {
                $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
                
                $stmt = $pdo->prepare("
                    INSERT INTO blog_articles (title, slug, excerpt, content, featured_image, category, tags, author_id, status, published_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $slug, $excerpt, $content, $featuredImage, $category, $tags, $user['id'], $status, $publishedAt]);
                
                $_SESSION['blog_success'] = 'Article created successfully';
                header('Location: /admin/blog');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Failed to create article: ' . $e->getMessage();
        }
    }
}

// Get categories
try {
    $pdo = getDBConnection();
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
    <title>Create Article - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
            </div>
            <a href="/admin/blog" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Blog
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="p-6">
        <div class="max-w-5xl mx-auto">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2">Create New Article</h1>
                <p class="text-gray-400">Write and publish a new blog article</p>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
            <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="" class="space-y-6">
                
                <!-- Title & Slug -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Title *</label>
                        <input 
                            type="text" 
                            name="title" 
                            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                            required
                            oninput="generateSlug(this.value)"
                        >
                    </div>
                    
                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Slug (URL)</label>
                        <input 
                            type="text" 
                            name="slug" 
                            id="slug-input"
                            value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition font-mono text-sm"
                            placeholder="auto-generated-from-title"
                        >
                        <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate</p>
                    </div>
                </div>

                <!-- Excerpt -->
                <div class="bg-gray-800 rounded-xl p-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-2">Excerpt (Short Description)</label>
                    <textarea 
                        name="excerpt" 
                        rows="3"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                        placeholder="Brief summary of the article..."
                    ><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">Displayed in article cards and meta descriptions</p>
                </div>

                <!-- Content -->
                <div class="bg-gray-800 rounded-xl p-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-2">Content *</label>
                    <textarea 
                        name="content" 
                        id="content-editor"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                        rows="20"
                    ><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                </div>

                <!-- Settings -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Category -->
                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Category</label>
                        <input 
                            type="text" 
                            name="category" 
                            list="categories-list"
                            value="<?php echo htmlspecialchars($_POST['category'] ?? 'General'); ?>"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                        >
                        <datalist id="categories-list">
                            <option value="General">
                            <option value="Tutorial">
                            <option value="News">
                            <option value="Tips & Tricks">
                            <option value="Case Study">
                            <option value="Updates">
                            <?php foreach ($existingCategories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Status</label>
                        <select 
                            name="status"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                        >
                            <option value="draft" <?php echo ($_POST['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($_POST['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo ($_POST['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>

                    <!-- Tags -->
                    <div class="bg-gray-800 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Tags</label>
                        <input 
                            type="text" 
                            name="tags" 
                            value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                            placeholder="tracking, shipping, tips"
                        >
                        <p class="mt-1 text-xs text-gray-500">Comma separated</p>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-gray-800 rounded-xl p-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-2">Featured Image URL</label>
                    <input 
                        type="url" 
                        name="featured_image" 
                        value="<?php echo htmlspecialchars($_POST['featured_image'] ?? ''); ?>"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition font-mono text-sm"
                        placeholder="https://example.com/image.jpg"
                    >
                    <p class="mt-1 text-xs text-gray-500">Direct URL to image (or upload to /uploads/blog/)</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-6">
                    <a href="/admin/blog" class="text-gray-400 hover:text-white transition">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <div class="space-x-3">
                        <button 
                            type="submit" 
                            name="status" 
                            value="draft"
                            class="bg-gray-700 hover:bg-gray-600 text-white font-semibold px-8 py-3 rounded-lg transition"
                        >
                            Save as Draft
                        </button>
                        <button 
                            type="submit" 
                            name="status" 
                            value="published"
                            class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-semibold px-8 py-3 rounded-lg transition"
                        >
                            <i class="fas fa-check mr-2"></i>Publish Article
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

    <script>
        // Initialize TinyMCE
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

        // Auto-generate slug from title
        function generateSlug(title) {
            const slug = title
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug-input').value = slug;
        }
    </script>

</body>
</html>
