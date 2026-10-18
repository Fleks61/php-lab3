
<?php
session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

// Если пользователь уже вошёл или выбран автоматический вход,
// сразу перенаправляем его на страницу настроек.
tryRememberLogin($pdo);

if (isset($_SESSION['user_id'])) {
    header('Location: settings.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash
         FROM users
         WHERE email = ?'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];

        if ($remember) {
            rememberUser($pdo, $user);
        }

        header('Location: settings.php');
        exit;
    }

    $message = 'Неверный email или пароль.';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход</title>
</head>
<body>
    <h1>Вход</h1>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label>
            Email:
            <input
                type="email"
                name="email"
                required
                autocomplete="email"
            >
        </label>

        <br><br>

        <label>
            Пароль:
            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
            >
        </label>

        <br><br>

        <label>
            <input
                type="checkbox"
                name="remember"
                value="1"
            >
            Запомнить меня на этом устройстве
        </label>

        <br><br>

        <button type="submit">Войти</button>
    </form>

    <p>
        <a href="register.php">Создать аккаунт</a>
    </p>
</body>
</html>
