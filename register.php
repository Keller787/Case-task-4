<?php
require_once 'config.php';

$error = '';
$success = '';

if (!empty($_POST['login']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    $login = mysqli_real_escape_string($link, $_POST['login']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = password_hash(mysqli_real_escape_string($link, $_POST['password']), PASSWORD_DEFAULT);
    
    $check = mysqli_query($link, "SELECT * FROM users WHERE login='$login' OR email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Такой логин или email уже существует';
    } else {
        $query = "INSERT INTO users (login, email, password) VALUES ('$login', '$email', '$password')";
        if (mysqli_query($link, $query)) {
            $success = 'Регистрация успешна! <a href="login.php">Войти</a>';
        } else {
            $error = 'Ошибка регистрации';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Регистрация - Книжный магазин</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 50px auto;">
            <h1>Книжный магазин</h1>
            <h2>Регистрация</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php else: ?>
                <form method="POST">
                    <input type="text" name="login" placeholder="Логин" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <button type="submit" class="btn" style="width: 100%;">Зарегистрироваться</button>
                </form>
                <p style="margin-top: 15px; text-align: center;">
                    Уже есть аккаунт? <a href="login.php">Войти</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>