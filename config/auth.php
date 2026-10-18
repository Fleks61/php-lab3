
<?php

function rememberUser(PDO $pdo, array $user): void
{
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare(
        'UPDATE users SET remember_token_hash = ? WHERE id = ?'
    );
    $stmt->execute([$tokenHash, $user['id']]);

    setcookie('remember_token', $token, [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']),
        'samesite' => 'Lax'
    ]);
}

function tryRememberLogin(PDO $pdo): void
{
    if (isset($_SESSION['user_id']) || empty($_COOKIE['remember_token'])) {
        return;
    }

    $token = $_COOKIE['remember_token'];

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return;
    }

    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare(
        'SELECT id, username FROM users WHERE remember_token_hash = ?'
    );
    $stmt->execute([$tokenHash]);
    $user = $stmt->fetch();

    if (!$user) {
        return;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
}
