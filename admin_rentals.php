<?php
require_once 'config.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = '';

// Принудительный возврат книги
if (!empty($_GET['force_return'])) {
    $rental_id = (int)$_GET['force_return'];
    $rental_query = "SELECT * FROM rentals WHERE id = $rental_id AND status = 'active'";
    $rental_res = mysqli_query($link, $rental_query);
    $rental = mysqli_fetch_assoc($rental_res);
    
    if ($rental) {
        // Возвращаем книгу
        $update_rental = "UPDATE rentals SET status = 'returned' WHERE id = $rental_id";
        mysqli_query($link, $update_rental);
        
        // Увеличиваем количество
        $book_query = "SELECT stock FROM books WHERE id = {$rental['book_id']}";
        $book_res = mysqli_query($link, $book_query);
        $book = mysqli_fetch_assoc($book_res);
        $new_stock = $book['stock'] + 1;
        $update_book = "UPDATE books SET stock = $new_stock, status_id = 1 WHERE id = {$rental['book_id']}";
        mysqli_query($link, $update_book);
        
        $message = 'Книга принудительно возвращена!';
        $message_type = 'success';
    }
}

// Все аренды
$rentals_query = "SELECT r.*, u.login, b.title, b.author, b.price 
                  FROM rentals r 
                  JOIN users u ON r.user_id = u.id 
                  JOIN books b ON r.book_id = b.id 
                  ORDER BY r.created_at DESC";
$rentals_res = mysqli_query($link, $rentals_query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Управление арендами</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="admin.php" class="logo"><span>Админ-панель</span></a>
            <nav>
                <a href="admin.php">Статистика</a>
                <a href="admin_books.php">Управление книгами</a>
                <a href="admin_rentals.php" class="active">Управление арендами</a>
                <a href="index.php">Магазин</a>
                <a href="logout.php">Выход</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1>📌 Управление арендами</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3>Все аренды</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Книга</th>
                            <th>Пользователь</th>
                            <th>Дата аренды</th>
                            <th>Дата возврата</th>
                            <th>Срок</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($rentals_res) > 0): ?>
                            <?php while ($rental = mysqli_fetch_assoc($rentals_res)): 
                                $is_overdue = ($rental['status'] == 'active' && strtotime($rental['return_date']) < time());
                            ?>
                                <tr style="<?php echo $is_overdue ? 'background: #fdebd0;' : ''; ?>">
                                    <td><?php echo $rental['id']; ?></td>
                                    <td>
                                        <strong><?php echo $rental['title']; ?></strong>
                                        <br>
                                        <small><?php echo $rental['author']; ?></small>
                                    </td>
                                    <td><?php echo $rental['login']; ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($rental['rent_date'])); ?></td>
                                    <td>
                                        <?php echo date('d.m.Y', strtotime($rental['return_date'])); ?>
                                        <?php if ($is_overdue): ?>
                                            <br>
                                            <span style="color: red; font-weight: bold;">
                                                (Просрочено на <?php echo floor((time() - strtotime($rental['return_date'])) / (60 * 60 * 24)); ?> дн.)
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        switch ($rental['rent_type']) {
                                            case '2_weeks': echo '2 недели'; break;
                                            case '1_month': echo '1 месяц'; break;
                                            case '3_months': echo '3 месяца'; break;
                                        }
                                        ?>
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <?php if ($rental['status'] == 'active'): ?>
                                            <a href="admin_rentals.php?force_return=<?php echo $rental['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Принудительно вернуть книгу?')">
                                                Принудительный возврат
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #7f8c8d;">Возвращена</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 20px; color: #7f8c8d;">
                                    Нет аренд
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>