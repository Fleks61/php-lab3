
<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $background = $_POST['background_color'] ?? '';
    $text = $_POST['text_color'] ?? '';

    if (preg_match('/^#[0-9a-fA-F]{6}$/', $background)
        && preg_match('/^#[0-9a-fA-F]{6}$/', $text)) {

        $stmt = $pdo->prepare(
            'UPDATE user_settings
             SET background_color = ?, text_color = ?
             WHERE user_id = ?'
        );
        $stmt->execute([$background, $text, $userId]);

        setcookie('background_color', $background, [
            'expires' => time() + 86400 * 30,
            'path' => '/',
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => false
        ]);

        setcookie('text_color', $text, [
            'expires' => time() + 86400 * 30,
            'path' => '/',
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => false
        ]);

        $message = 'Настройки сохранены.';
    } else {
        $message = 'Выбери корректные цвета.';
    }
}

$stmt = $pdo->prepare(
    'SELECT background_color, text_color
     FROM user_settings WHERE user_id = ?'
);
$stmt->execute([$userId]);
$settings = $stmt->fetch();

$background = $settings['background_color'] ?? '#ffffff';
$text = $settings['text_color'] ?? '#000000';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Настройки</title>
</head>
<body style="background-color: <?= htmlspecialchars($background) ?>;
             color: <?= htmlspecialchars($text) ?>;">
    <h1>Привет, <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>!</h1>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

    <h2>Цвета сайта</h2>
    <form method="post">
        <label>
            Цвет фона:
            <input type="color" name="background_color"
                   value="<?= htmlspecialchars($background) ?>">
        </label>
        <br><br>

        <label>
            Цвет текста:
            <input type="color" name="text_color"
                   value="<?= htmlspecialchars($text) ?>">
        </label>
        <br><br>

        <button type="submit">Сохранить настройки</button>
    </form>

    <p><a href="logout.php">Выйти из аккаунта</a></p>
</body>
</html>
