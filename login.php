<?php

require_once __DIR__ . '/auth.php';

$pdo = get_db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid email or password.';
        } else {
            login_user($user);
            if (in_array($user['role'], ['admin', 'moderator'], true)) {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: user_dashboard.php');
            }
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Lost & Found</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="shell">
  <section class="panel">
    <h2>Login</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" class="form active">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
      <p class="hint">Sign up? <a href="register.php">Register here</a>.</p>
      <div class="oauth">
        <p class="hint">or</p>
        <a class="oauth-btn google" href="oauth_google.php" target="_blank" rel="noopener noreferrer">Continue with Google</a>
        <a class="oauth-btn facebook" href="oauth_facebook.php" target="_blank" rel="noopener noreferrer">Continue with Facebook</a>
      </div>
    </form>
  </section>
</main>
<script src="script.js"></script>
</body>
</html>


