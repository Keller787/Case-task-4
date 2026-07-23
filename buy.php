<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$book_id) {
    header('Location: index.php');
    exit;
}

// Получаем информацию о книге
$book_query = "SELECT * FROM books WHERE id = $book_id";
$book_res = mysqli_query($link, $book_query);
$book = mysqli_fetch_assoc($book_res);

if (!$book || $book['status_id'] != 1 || $book['stock'] < 1) {
    header('Location: index.php');
    exit;
}

// Оформляем покупку
$purchase_date = date('Y-m-d');
$query = "INSERT INTO purchases (user_id, book_id, purchase_date, amount) 
          VALUES ($user_id, $book_id, '$purchase_date', {$book['price']})";

if (mysqli_query($link, $query)) {
    // Уменьшаем количество книг в наличии
    $new_stock = $book['stock'] - 1;
    $new_status = ($new_stock > 0) ? 1 : 4; // 1 - в наличии, 4 - нет в наличии
    $update_query = "UPDATE books SET stock = $new_stock, status_id = $new_status WHERE id = $book_id";
    mysqli_query($link, $update_query);
    
    $success = true;
} else {
    $success = false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Покупка книги</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo"><span>Книжный магазин</span></a>
            <nav>
                <a href="index.php">Каталог</a>
                <a href="my_rentals.php">Мои аренды</a>
                <a href="logout.php">Выход</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 0 auto; text-align: center;">
            <?php if ($success): ?>
                <h2>Покупка оформлена!</h2>
                <div style="font-size: 48px; margin: 20px 0;"></div>
                <h3><?php echo $book['title']; ?></h3>
                <p>✍️ <?php echo $book['author']; ?></p>
                <p style="font-size: 24px; font-weight: bold; color: #27ae60; margin: 20px 0;">
                    <?php echo number_format($book['price'], 2); ?> ₽
                </p>
                <p style="color: #7f8c8d;">Дата покупки: <?php echo date('d.m.Y'); ?></p>
                <p style="margin-top: 20px;">
                    <a href="index.php" class="btn">Вернуться в каталог</a>
                </p>
            <?php else: ?>
                <h2>❌ Ошибка</h2>
                <p>Не удалось оформить покупку. Попробуйте позже.</p>
                <p style="margin-top: 20px;">
                    <a href="index.php" class="btn">Вернуться в каталог</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>