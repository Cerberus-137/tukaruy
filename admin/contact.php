<?php
session_start();
require_once '../config.php';
require_once '../auth.php';

// Require admin login
requireAdmin();

$user = getCurrentUser();
$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $submissionId = $_POST['submission_id'] ?? 0;
    $action = $_POST['action'];
    
    try {
        $pdo = getDBConnection();
        
        if ($action === 'mark_read') {
            $stmt = $pdo->prepare("UPDATE contact_submissions SET status = 'read' WHERE id = ?");
            $stmt->execute([$submissionId]);
            $success = 'Marked as read';
        } elseif ($action === 'mark_replied') {
            $stmt = $pdo->prepare("UPDATE contact_submissions SET status = 'replied' WHERE id = ?");
            $stmt->execute([$submissionId]);
            $success = 'Marked as replied';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM contact_submissions WHERE id = ?");
            $stmt->execute([$submissionId]);
            $success = 'Submission deleted';
        }
    } catch (Exception $e) {
        $error = 'Action failed: ' . $e->getMessage();
    }
}

// Get all submissions
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT * FROM contact_submissions 
        ORDER BY 
            CASE status
                WHEN 'new' THEN 1
                WHEN 'read' THEN 2
                WHEN 'replied' THEN 3
                WHEN 'archived' THEN 4
            END,
            created_at DESC
    ");
    $submissions = $stmt->fetchAll();
    
    // Get stats
    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
            SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
            SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count
        FROM contact_submissions
    ");
    $stats = $statsStmt->fetch();
} catch (Exception $e) {
    error_log('Admin contact error: ' . $e->getMessage());
    $submissions = [];
    $stats = ['total' => 0, 'new_count' => 0, 'read_count' => 0, 'replied_count' => 0];
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Submissions - Admin</title>
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
                    <a href="/admin/blog" class="text-gray-400 hover:text-white transition">Blog</a>
                    <a href="/admin/contact" class="text-white font-medium">Contact</a>
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
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2">Contact Submissions</h1>
                <p class="text-gray-400">Manage customer inquiries and messages</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-blue-200 text-sm font-medium">Total</span>
                        <i class="fas fa-inbox text-blue-200 text-xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['total']); ?></p>
                </div>
                
                <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-yellow-200 text-sm font-medium">New</span>
                        <i class="fas fa-envelope text-yellow-200 text-xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['new_count']); ?></p>
                </div>
                
                <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-purple-200 text-sm font-medium">Read</span>
                        <i class="fas fa-eye text-purple-200 text-xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['read_count']); ?></p>
                </div>
                
                <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-green-200 text-sm font-medium">Replied</span>
                        <i class="fas fa-check-circle text-green-200 text-xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['replied_count']); ?></p>
                </div>
            </div>

            <!-- Messages -->
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

            <!-- Submissions Table -->
            <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Subject</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <p>No contact submissions yet</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($submissions as $submission): ?>
                        <tr class="hover:bg-gray-750 transition">
                            <td class="px-6 py-4 text-sm text-gray-400">
                                <?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 font-medium">
                                <?php echo htmlspecialchars($submission['name']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>" class="text-blue-400 hover:text-blue-300">
                                    <?php echo htmlspecialchars($submission['email']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <button onclick="showMessage(<?php echo $submission['id']; ?>)" class="text-purple-400 hover:text-purple-300 font-medium">
                                    <?php echo htmlspecialchars($submission['subject'] ?: 'View Message'); ?>
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusColors = [
                                    'new' => 'bg-yellow-500/20 text-yellow-400',
                                    'read' => 'bg-blue-500/20 text-blue-400',
                                    'replied' => 'bg-green-500/20 text-green-400',
                                    'archived' => 'bg-gray-500/20 text-gray-400'
                                ];
                                $colorClass = $statusColors[$submission['status']] ?? 'bg-gray-500/20 text-gray-400';
                                ?>
                                <span class="<?php echo $colorClass; ?> text-xs font-semibold px-2 py-1 rounded capitalize">
                                    <?php echo htmlspecialchars($submission['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <?php if ($submission['status'] === 'new'): ?>
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                        <button type="submit" class="text-blue-400 hover:text-blue-300 px-3 py-1 rounded transition" title="Mark as Read">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($submission['status'] !== 'replied'): ?>
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="action" value="mark_replied">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                        <button type="submit" class="text-green-400 hover:text-green-300 px-3 py-1 rounded transition" title="Mark as Replied">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Delete this submission?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-300 px-3 py-1 rounded transition" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="message-<?php echo $submission['id']; ?>" class="hidden">
                            <td colspan="6" class="px-6 py-6 bg-gray-750">
                                <div class="max-w-4xl">
                                    <h4 class="font-bold text-lg mb-2"><?php echo htmlspecialchars($submission['subject'] ?: 'No Subject'); ?></h4>
                                    <div class="bg-gray-800 rounded-lg p-4 mb-4">
                                        <p class="text-gray-300 whitespace-pre-wrap"><?php echo htmlspecialchars($submission['message']); ?></p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <span class="mr-4"><i class="fas fa-globe mr-1"></i>IP: <?php echo htmlspecialchars($submission['ip_address']); ?></span>
                                        <span><i class="fas fa-desktop mr-1"></i><?php echo htmlspecialchars(substr($submission['user_agent'], 0, 50)); ?>...</span>
                                    </div>
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

    <script>
        function showMessage(id) {
            const row = document.getElementById('message-' + id);
            if (row.classList.contains('hidden')) {
                // Hide all other messages
                document.querySelectorAll('[id^="message-"]').forEach(el => el.classList.add('hidden'));
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        }
    </script>

</body>
</html>
