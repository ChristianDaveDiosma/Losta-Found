<?php

require_once __DIR__ . '/auth.php';

require_login();
$user = current_user();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = (int)($_POST['item_id'] ?? 0);

    if ($itemId > 0) {
        // only allow claiming items that are currently available
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND status = 'available' LIMIT 1");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();

        if ($item) {
            $stmt = $pdo->prepare(
                "UPDATE items 
                 SET status = 'claimed', claimed_by_user_id = ?, updated_at = NOW()
                 WHERE id = ?"
            );
            $stmt->execute([$user['id'], $itemId]);
        }
    }
}

header('Location: user_dashboard.php');
exit;


