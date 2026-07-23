<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Все аренды пользователя
$rentals_query = "SELECT r.*, b.title, b.author, b.price 
                  FROM rentals r 
                  JOIN books b ON r.book_id = b.id 
                  WHERE r.user_id = $user_id 
                  ORDER BY r.created_at DESC";
$rentals_res = mysqli_query($link, $rentals_query);

$returned = isset($_GET['returned']) ? 1 : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Мои аренды</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo"><span>Книжный магазин</span></a>
            <nav>
                <a href="index.php">Каталог</a>
                <a href="my_rentals.php" class="active">Мои аренды</a>
                <a href="logout.php">Выход</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1>📌 Мои аренды</h1>
        
        <?php if ($returned): ?>
            <div class="alert alert-success">Книга успешно возвращена!</div>
        <?php endif; ?>
        
        <div class="card">
            <?php if (mysqli_num_rows($rentals_res) > 0): ?>
                <?php while ($rental = mysqli_fetch_assoc($rentals_res)): 
                    $is_overdue = ($rental['status'] == 'active' && strtotime($rental['return_date']) < time());
                ?>
                    <div class="rental-item" style="<?php echo $is_overdue ? 'background: #fdebd0; border-radius: 5px;' : ''; ?>">
                        <div class="rental-info">
                            <strong><?php echo $rental['title']; ?></strong>
                            <div class="meta">✍️ <?php echo $rental['author']; ?></div>
                            <div class="meta">
                                Арендована: <?php echo date('d.m.Y', strtotime($rental['rent_date'])); ?>
                            </div>
                            <div class="meta">
                                Возврат: <?php echo date('d.m.Y', strtotime($rental['return_date'])); ?>
                            </div>
                            <div class="meta">
                                <?php echo number_format($rental['price'], 2); ?> ₽
                            </div>
                            <div>
                                <span style="display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 12px;
                                    <?php 
                                    if ($rental['status'] == 'returned') {
                                        echo 'background: #d5f5e3; color: #27ae60;';
                                    } elseif ($is_overdue) {
                                        echo 'background: #fadbd8; color: #e74c3c;';
                                    } else {
                                        echo 'background: #fdebd0; color: #e67e22;';
                                    }
                                    ?>">
                                    <?php 
                                    if ($rental['status'] == 'returned') {
                                        echo 'Возвращена';
                                    } elseif ($is_overdue) {
                                        echo 'ПРОСРОЧЕНА!';
                                    } else {
                                        echo 'Активна';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php if ($rental['status'] == 'active'): ?>
                            <a href="return.php?id=<?php echo $rental['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Вернуть книгу?')">Вернуть</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 30px 0; color: #7f8c8d;">
                    У вас пока нет арендованных книг.
                    <br>
                    <a href="index.php" class="btn" style="margin-top: 10px;">Перейти в каталог</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>