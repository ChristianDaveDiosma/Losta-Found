<?php

require_once __DIR__ . '/auth.php';

$pdo = get_db();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($role, ['user', 'moderator'], true)) {
        $error = 'Invalid role selected.';
    } else {
        // check if email already used
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hash, $role]);
            $success = 'Account created. You can now login.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Lost & Found</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="shell">
  <section class="panel">
    <h2>Register</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php elseif ($success): ?>
      <p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" class="form active">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <select name="role" required>
        <option value="">Role</option>
        <option value="user">User</option>
        <option value="moderator">Moderator</option>
      </select>
      <button type="submit">Create Account</button>
      <p class="hint">Already have an account? <a href="login.php">Login instead</a>.</p>
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


