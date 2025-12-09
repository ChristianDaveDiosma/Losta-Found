<?php

require_once __DIR__ . '/auth.php';

$user = current_user();

if ($user) {
    if (in_array($user['role'], ['admin', 'moderator'], true)) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}

header('Location: login.php');
exit;


