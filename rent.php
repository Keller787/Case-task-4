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

$error = '';
$success = '';

// Обработка аренды
if (!empty($_POST['rent_type'])) {
    $rent_type = $_POST['rent_type'];
    $rent_date = date('Y-m-d');
    
    // Определяем дату возврата
    switch ($rent_type) {
        case '2_weeks':
            $return_date = date('Y-m-d', strtotime('+14 days'));
            $period = '2 недели';
            break;
        case '1_month':
            $return_date = date('Y-m-d', strtotime('+1 month'));
            $period = '1 месяц';
            break;
        case '3_months':
            $return_date = date('Y-m-d', strtotime('+3 months'));
            $period = '3 месяца';
            break;
        default:
            $error = 'Неверный срок аренды';
            break;
    }
    
    if (empty($error)) {
        // Создаем аренду
        $query = "INSERT INTO rentals (user_id, book_id, rent_date, return_date, rent_type) 
                  VALUES ($user_id, $book_id, '$rent_date', '$return_date', '$rent_type')";
        
        if (mysqli_query($link, $query)) {
            // Уменьшаем количество книг в наличии
            $new_stock = $book['stock'] - 1;
            $new_status = ($new_stock > 0) ? 1 : 2; // 1 - в наличии, 2 - арендована
            $update_query = "UPDATE books SET stock = $new_stock, status_id = $new_status WHERE id = $book_id";
            mysqli_query($link, $update_query);
            
            $success = "✅ Книга успешно арендована на $period!";
        } else {
            $error = 'Ошибка при оформлении аренды';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Аренда книги</title>
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
        <div class="card" style="max-width: 500px; margin: 0 auto;">
            <h2>Аренда книги</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <p><a href="index.php" class="btn">Вернуться в каталог</a></p>
            <?php else: ?>
                <div style="margin-bottom: 20px;">
                    <h3><?php echo $book['title']; ?></h3>
                    <p>✍<?php echo $book['author']; ?></p>
                    <p><?php echo number_format($book['price'], 2); ?> ₽</p>
                    <p>В наличии: <?php echo $book['stock']; ?> шт.</p>
                </div>
                
                <form method="POST">
                    <label style="font-weight: bold;">Выберите срок аренды:</label>
                    <div style="margin: 15px 0;">
                        <label style="display: block; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin: 5px 0;">
                            <input type="radio" name="rent_type" value="2_weeks" checked>
                            2 недели
                        </label>
                        <label style="display: block; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin: 5px 0;">
                            <input type="radio" name="rent_type" value="1_month">
                            1 месяц
                        </label>
                        <label style="display: block; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin: 5px 0;">
                            <input type="radio" name="rent_type" value="3_months">
                            3 месяца
                        </label>
                    </div>
                    
                    <button type="submit" class="btn" style="width: 100%;">Оформить аренду</button>
                </form>
                
                <p style="margin-top: 15px;">
                    <a href="index.php">← Вернуться в каталог</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>