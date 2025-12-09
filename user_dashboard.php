<?php

require_once __DIR__ . '/auth.php';

require_login();
$user = current_user();

// redirect admins/mods to their own dashboard
if (in_array($user['role'], ['admin', 'moderator'], true)) {
    header('Location: admin_dashboard.php');
    exit;
}

$pdo = get_db();
$q = trim($_GET['q'] ?? '');

// available items for all users (searchable)
$params = [];
$where = "WHERE status = 'available'";
if ($q !== '') {
    $where .= " AND (name LIKE :q OR location LIKE :q OR description LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}
$stmt = $pdo->prepare("SELECT * FROM items $where ORDER BY created_at DESC");
$stmt->execute($params);
$availableItems = $stmt->fetchAll();

// claims made by this user
$stmt = $pdo->prepare("SELECT * FROM items WHERE claimed_by_user_id = :uid ORDER BY updated_at DESC");
$stmt->execute([':uid' => $user['id']]);
$myClaims = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard | Lost &amp; Found Portal</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="shell">
  <section class="dashboard" data-screen="dashboard">
    <header>
      <div>
        <h1>User Dashboard</h1>
        <p id="user-label"><?= htmlspecialchars(strtoupper($user['role']) . ' · ' . $user['email'], ENT_QUOTES, 'UTF-8') ?></p>
      </div>
      <form method="get">
        <input id="search" type="search" name="q" placeholder="Search by item or location"
               value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
      </form>
      <a href="logout.php" class="ghost button-link">Logout</a>
    </header>

    <div class="dash-views">
      <div class="dash-view" id="user-view">
        <div class="column-head">
          <h3>Available Items</h3>
          <span class="badge" id="user-badge"><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <p class="hint">Browse items reported as found. Use search to narrow down by name or location.</p>
        <ul id="user-available">
          <?php if (!$availableItems): ?>
            <li><strong>No items match your search right now.</strong></li>
          <?php else: ?>
            <?php foreach ($availableItems as $item): ?>
              <li data-id="<?= (int)$item['id'] ?>" class="item-card">
                <?php if ($item['image_path']): ?>
                  <img src="<?= htmlspecialchars($item['image_path'], ENT_QUOTES, 'UTF-8') ?>" 
                       alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" 
                       class="item-image">
                <?php endif; ?>
                <div class="item-details">
                  <strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <span>Found at <?= htmlspecialchars($item['location'], ENT_QUOTES, 'UTF-8') ?></span>
                  <p><?= nl2br(htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                  <form method="post" action="claim_item.php">
                    <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                    <button type="submit" class="claim">Claim</button>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>

        <div class="column-head">
          <h4>My Claims</h4>
          <?php if ($myClaims): ?>
            <form method="post" action="unclaim_item.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to clear all your claims? This will make all items available again.');">
              <input type="hidden" name="action" value="clear_all_claims">
              <button type="submit" class="clear-all-btn">Clear All Claims</button>
            </form>
          <?php endif; ?>
        </div>
        <p class="hint">These items are claimed by you and pending staff verification.</p>
        <ul id="claims">
          <?php if (!$myClaims): ?>
            <li><strong>No claims yet.</strong></li>
          <?php else: ?>
            <?php foreach ($myClaims as $item): ?>
              <li class="item-card">
                <?php if ($item['image_path']): ?>
                  <img src="<?= htmlspecialchars($item['image_path'], ENT_QUOTES, 'UTF-8') ?>" 
                       alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" 
                       class="item-image">
                <?php endif; ?>
                <div class="item-details">
                  <strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <span>Found at <?= htmlspecialchars($item['location'], ENT_QUOTES, 'UTF-8') ?></span>
                  <p><?= nl2br(htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                  <small>Status: <?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></small>
                  <form method="post" action="unclaim_item.php" style="margin-top: .5rem;" onsubmit="return confirm('Are you sure you want to unclaim this item? It will become available for others to claim.');">
                    <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                    <button type="submit" class="delete-btn">Clear</button>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </section>
</main>
<script src="script.js"></script>
</body>
</html>


