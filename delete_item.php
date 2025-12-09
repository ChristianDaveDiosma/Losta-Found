<?php

require_once __DIR__ . '/auth.php';

require_role(['admin', 'moderator']);
$user = current_user();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $action = trim($_POST['action'] ?? '');

    if ($itemId > 0) {
        if ($action === 'delete') {
            // Get item to delete image file if exists
            $stmt = $pdo->prepare("SELECT image_path FROM items WHERE id = ? LIMIT 1");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch();
            
            // Delete the item
            $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
            $stmt->execute([$itemId]);
            
            // Delete associated image file if exists
            if ($item && $item['image_path'] && file_exists(__DIR__ . '/' . $item['image_path'])) {
                unlink(__DIR__ . '/' . $item['image_path']);
            }
        } elseif ($action === 'clear_all') {
            // Get all items to delete their images
            $stmt = $pdo->prepare("SELECT image_path FROM items");
            $stmt->execute();
            $allItems = $stmt->fetchAll();
            
            // Delete all items
            $pdo->exec("DELETE FROM items");
            
            // Delete all associated image files
            foreach ($allItems as $item) {
                if ($item['image_path'] && file_exists(__DIR__ . '/' . $item['image_path'])) {
                    unlink(__DIR__ . '/' . $item['image_path']);
                }
            }
        }
    }
}

header('Location: admin_dashboard.php');
exit;

