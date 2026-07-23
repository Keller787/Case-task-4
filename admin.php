<?php
require_once 'config.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

// Статистика
$stats = [];

$stats['books'] = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as count FROM books"))['count'];
$stats['users'] = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as count FROM users"))['count'];
$stats['rentals'] = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as count FROM rentals WHERE status='active'"))['count'];
$stats['overdue'] = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as count FROM rentals WHERE status='active' AND return_date < CURDATE()"))['count'];
$stats['purchases'] = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as count FROM purchases"))['count'];

// Просроченные аренды
$overdue_query = "SELECT r.*, u.login, b.title 
                  FROM rentals r 
                  JOIN users u ON r.user_id = u.id 
                  JOIN books b ON r.book_id = b.id 
                  WHERE r.status='active' AND r.return_date < CURDATE()
                  ORDER BY r.return_date ASC";
$overdue_res = mysqli_query($link, $overdue_query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="admin.php" class="logo"><span>Админ-панель</span></a>
            <nav>
                <a href="admin.php" class="active">Статистика</a>
                <a href="admin_books.php">Управление книгами</a>
                <a href="admin_rentals.php">Управление арендами</a>
                <a href="index.php">Магазин</a>
                <a href="logout.php">Выход</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1>Панель администратора</h1>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card stat-blue">
                <div class="number"><?php echo $stats['books']; ?></div>
                <div class="label">Книг в каталоге</div>
            </div>
            <div class="stat-card stat-green">
                <div class="number"><?php echo $stats['users']; ?></div>
                <div class="label">Пользователей</div>
            </div>
            <div class="stat-card stat-orange">
                <div class="number"><?php echo $stats['rentals']; ?></div>
                <div class="label">Активных аренд</div>
            </div>
            <div class="stat-card stat-red">
                <div class="number"><?php echo $stats['overdue']; ?></div>
                <div class="label">Просроченных</div>
            </div>
            <div class="stat-card stat-blue">
                <div class="number"><?php echo $stats['purchases']; ?></div>
                <div class="label">Совершено покупок</div>
            </div>
        </div>
        
        <!-- Просроченные аренды -->
        <div class="card">
            <h3>Просроченные аренды</h3>
            <?php if (mysqli_num_rows($overdue_res) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Книга</th>
                            <th>Пользователь</th>
                            <th>Дата аренды</th>
                            <th>Должен вернуть</th>
                            <th>Просрочено дней</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($overdue_res)): 
                            $days = floor((time() - strtotime($row['return_date'])) / (60 * 60 * 24));
                        ?>
                            <tr style="background: #fdebd0;">
                                <td><?php echo $row['title']; ?></td>
                                <td><?php echo $row['login']; ?></td>
                                <td><?php echo date('d.m.Y', strtotime($row['rent_date'])); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($row['return_date'])); ?></td>
                                <td style="color: red; font-weight: bold;"><?php echo $days; ?> дн.</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #27ae60;">Все аренды в срок!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>