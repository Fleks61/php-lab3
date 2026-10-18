
<?php
session_start();
require_once __DIR__ . '/config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($password) < 8) {
        $message = 'Укажи имя, корректный email и пароль не короче 8 символов.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password_hash)
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            ]);

            $userId = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare(
                'INSERT INTO user_settings (user_id)
                 VALUES (?)'
            );
            $stmt->execute([$userId]);

            $pdo->commit();

            $message = 'Регистрация успешна! Теперь можно войти на сайте.';
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($e->getCode() === '23000') {
                $message = 'Такое имя пользователя или email уже заняты.';
            } else {
                $message = 'Не удалось выполнить регистрацию.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
</head>
<body>
    <h1>Регистрация</h1>

    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

    <form method="post">
        <label>
            Имя пользователя:
            <input name="username" required maxlength="50">
        </label>
        <br><br>

        <label>
            Email:
            <input type="email" name="email" required maxlength="255">
        </label>
        <br><br>

        <label>
            Пароль (минимум 8 символов):
            <input type="password" name="password" required minlength="8">
        </label>
        <br><br>

        <button type="submit">Зарегистрироваться</button>
    </form>

    <p><a href="login.php">Уже есть аккаунт? Войти</a></p>
</body>
</html>
