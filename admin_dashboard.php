<?php

require_once __DIR__ . '/auth.php';

require_role(['admin', 'moderator']);
$user = current_user();
$pdo = get_db();

$error = '';
$q = trim($_GET['q'] ?? '');

// handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imagePath = null;

    if ($name === '' || $location === '' || $description === '') {
        $error = 'Please fill in all item fields.';
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = uniqid('item_', true) . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = 'uploads/' . $fileName;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid image format. Allowed: JPG, PNG, GIF, WEBP.';
            }
        }
        
        if (!$error) {
            $stmt = $pdo->prepare(
                "INSERT INTO items (name, location, description, image_path, status, found_by_user_id, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 'available', ?, NOW(), NOW())"
            );
            $stmt->execute([$name, $location, $description, $imagePath, $user['id']]);
            header('Location: admin_dashboard.php');
            exit;
        }
    }
}

// list items with claimer information
$params = [];
$where = 'WHERE 1=1';
if ($q !== '') {
    $where .= " AND (i.name LIKE :q OR i.location LIKE :q OR i.description LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}
$stmt = $pdo->prepare("
    SELECT i.*, 
           u_claimer.name as claimer_name, 
           u_claimer.email as claimer_email
    FROM items i
    LEFT JOIN users u_claimer ON i.claimed_by_user_id = u_claimer.id
    $where 
    ORDER BY i.created_at DESC
");
$stmt->execute($params);
$items = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Lost &amp; Found Portal</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="shell">
  <section class="dashboard" data-screen="dashboard">
    <header>
      <div>
        <h1>Admin &amp; Moderator Dashboard</h1>
        <p id="user-label"><?= htmlspecialchars(strtoupper($user['role']) . ' · ' . $user['email'], ENT_QUOTES, 'UTF-8') ?></p>
      </div>
      <form method="get">
        <input id="search" type="search" name="q" placeholder="Search items"
               value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
      </form>
      <a href="logout.php" class="ghost button-link">Logout</a>
    </header>

    <div class="dash-views">
      <div class="dash-view" id="admin-view">
        <div class="column-head">
          <h3>Post New Found Item</h3>
          <span class="badge" id="admin-badge"><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <p class="hint">Capture new items handed in or reported by staff.</p>
        <?php if ($error): ?>
          <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" id="upload" enctype="multipart/form-data" class="upload-form">
          <div class="form-row">
            <input type="text" name="name" placeholder="Item name" required>
            <input type="text" name="location" placeholder="Found location" required>
          </div>
          <textarea name="description" placeholder="Description" required></textarea>
          <div class="file-upload-wrapper">
            <label for="image-upload" class="file-label">
              <span>Choose Image (Optional)</span>
              <input type="file" name="image" id="image-upload" accept="image/*">
            </label>
          </div>
          <button type="submit" class="upload-submit-btn">Upload Item</button>
        </form>
      </div>

      <div class="dash-view">
        <div class="column-head">
          <h3>All Items</h3>
          <?php if ($items): ?>
            <form method="post" action="delete_item.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to clear ALL items? This action cannot be undone.');">
              <input type="hidden" name="action" value="clear_all">
              <button type="submit" class="clear-all-btn">Clear All</button>
            </form>
          <?php endif; ?>
        </div>
        <p class="hint">Overview of all items, including claimed ones.</p>
        <ul id="admin-inventory">
          <?php if (!$items): ?>
            <li><strong>No items yet.</strong></li>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <li data-id="<?= (int)$item['id'] ?>" class="item-card">
                <?php if ($item['image_path']): ?>
                  <img src="<?= htmlspecialchars($item['image_path'], ENT_QUOTES, 'UTF-8') ?>" 
                       alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" 
                       class="item-image">
                <?php endif; ?>
                <div class="item-details">
                  <strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <span>Found at: <?= htmlspecialchars($item['location'], ENT_QUOTES, 'UTF-8') ?></span>
                  <p><?= nl2br(htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                  <small>Status: <?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></small>
                  <?php if ($item['status'] === 'claimed' && $item['claimer_name']): ?>
                    <small class="claimer-info">Claimed by: <?= htmlspecialchars($item['claimer_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($item['claimer_email'], ENT_QUOTES, 'UTF-8') ?>)</small>
                  <?php endif; ?>
                  <form method="post" action="delete_item.php" style="margin-top: .5rem;" onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.');">
                    <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                    <input type="hidden" name="action" value="delete">
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


