<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Find or create a local user account for an OAuth profile.
 * - provider: 'google' or 'facebook'
 * - providerId: unique ID from the provider
 * - name, email: profile info from provider
 */
function find_or_create_oauth_user(string $provider, string $providerId, string $name, string $email): array
{
    $pdo = get_db();

    // Try match existing user by provider id
    $column = $provider === 'google' ? 'google_id' : 'facebook_id';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE $column = ? LIMIT 1");
    $stmt->execute([$providerId]);
    $user = $stmt->fetch();

    if ($user) {
        return $user;
    }

    // Or match by email and attach provider id
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET $column = ? WHERE id = ?");
        $stmt->execute([$providerId, $user['id']]);
        $user[$column] = $providerId;
        return $user;
    }

    // Else create a new user (default to 'user' role)
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, role, $column, created_at)
         VALUES (?, ?, '', 'user', ?, NOW())"
    );
    $stmt->execute([$name, $email, $providerId]);

    $id = (int)$pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}


