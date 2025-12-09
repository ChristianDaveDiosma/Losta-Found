<?php

require_once __DIR__ . '/auth.php';

require_login();
$user = current_user();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $action = trim($_POST['action'] ?? '');

    if ($action === 'clear_all_claims') {
        // Clear all claims by this user
        $stmt = $pdo->prepare(
            "UPDATE items 
             SET status = 'available', claimed_by_user_id = NULL, updated_at = NOW()
             WHERE claimed_by_user_id = ?"
        );
        $stmt->execute([$user['id']]);
    } elseif ($itemId > 0) {
        // Only allow users to unclaim items they claimed
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND claimed_by_user_id = ? LIMIT 1");
        $stmt->execute([$itemId, $user['id']]);
        $item = $stmt->fetch();

        if ($item) {
            $stmt = $pdo->prepare(
                "UPDATE items 
                 SET status = 'available', claimed_by_user_id = NULL, updated_at = NOW()
                 WHERE id = ?"
            );
            $stmt->execute([$itemId]);
        }
    }
}

header('Location: user_dashboard.php');
exit;

