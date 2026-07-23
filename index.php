<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 0;

// фильтр и сортировка
$where = "WHERE 1=1";

if (!empty($_GET['category'])) {
    $category = (int)$_GET['category'];
    $where .= " AND b.category_id = $category";
}

if (!empty($_GET['author'])) {
    $author = mysqli_real_escape_string($link, $_GET['author']);
    $where .= " AND b.author LIKE '%$author%'";
}

if (!empty($_GET['year'])) {
    $year = (int)$_GET['year'];
    $where .= " AND b.year = $year";
}

// Сортировка
$order = "ORDER BY b.id DESC";
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'title': $order = "ORDER BY b.title ASC"; break;
        case 'price_asc': $order = "ORDER BY b.price ASC"; break;
        case 'price_desc': $order = "ORDER BY b.price DESC"; break;
        case 'year_asc': $order = "ORDER BY b.year ASC"; break;
        case 'year_desc': $order = "ORDER BY b.year DESC"; break;
        case 'author': $order = "ORDER BY b.author ASC"; break;
    }
}

$books_query = "SELECT b.*, c.name as category_name, s.name as status_name 
                FROM books b 
                JOIN categories c ON b.category_id = c.id 
                JOIN book_statuses s ON b.status_id = s.id 
                $where $order";
$books_res = mysqli_query($link, $books_query);

// Категории для фильтра
$categories_res = mysqli_query($link, "SELECT * FROM categories ORDER BY name");

// Мои активные аренды
$rentals_query = "SELECT r.*, b.title, b.author, b.id as book_id 
                  FROM rentals r 
                  JOIN books b ON r.book_id = b.id 
                  WHERE r.user_id = $user_id AND r.status = 'active'
                  ORDER BY r.return_date ASC";
$rentals_res = mysqli_query($link, $rentals_query);

// Мои покупки
$purchases_query = "SELECT p.*, b.title, b.author 
                    FROM purchases p 
                    JOIN books b ON p.book_id = b.id 
                    WHERE p.user_id = $user_id 
                    ORDER BY p.purchase_date DESC 
                    LIMIT 5";
$purchases_res = mysqli_query($link, $purchases_query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Книжный магазин</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo"><span>Книжный магазин</span></a>
            <nav>
                <a href="index.php" class="active">Каталог</a>
                <a href="my_rentals.php">Мои аренды</a>
                <a href="index.php?my_purchases=1">Мои покупки</a>
                <?php if ($is_admin): ?>
                    <a href="admin.php">Админ</a>
                <?php endif; ?>
                <a href="logout.php">Выход</a>
            </nav>
        </div>
    </header>
    
    <div class="container mb-20">
        <h1>Каталог книг</h1>
        
        <!-- Мои активные аренды -->
        <?php if (mysqli_num_rows($rentals_res) > 0): ?>
            <div class="card" style="border-left: 4px solid #f39c12;">
                <h3>📌 Мои активные аренды</h3>
                <?php while ($rental = mysqli_fetch_assoc($rentals_res)): 
                    $is_overdue = strtotime($rental['return_date']) < time();
                ?>
                    <div class="rental-item" style="<?php echo $is_overdue ? 'background: #fdebd0;' : ''; ?>">
                        <div class="rental-info">
                            <strong><?php echo $rental['title']; ?></strong>
                            <div class="meta">Автор: <?php echo $rental['author']; ?></div>
                            <div class="meta">
                                Возврат: <?php echo date('d.m.Y', strtotime($rental['return_date'])); ?>
                                <?php if ($is_overdue): ?>
                                    <span style="color: red; font-weight: bold;"> (ПРОСРОЧЕНО!)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="return.php?id=<?php echo $rental['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Вернуть книгу?')">Вернуть</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        
        <!-- Мои покупки -->
        <?php if (!empty($_GET['my_purchases']) && mysqli_num_rows($purchases_res) > 0): ?>
            <div class="card">
                <h3>Мои покупки</h3>
                <?php while ($purchase = mysqli_fetch_assoc($purchases_res)): ?>
                    <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        <strong><?php echo $purchase['title']; ?></strong> - <?php echo $purchase['author']; ?>
                        <br>
                        <small>Куплена: <?php echo date('d.m.Y', strtotime($purchase['purchase_date'])); ?></small>
                        <span style="float: right; font-weight: bold; color: #27ae60;"><?php echo number_format($purchase['amount'], 2); ?> ₽</span>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php elseif (!empty($_GET['my_purchases']) && mysqli_num_rows($purchases_res) == 0): ?>
            <div class="card">
                <h3>Мои покупки</h3>
                <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                    <strong>У вас пока нет купленных книг.</strong>
                    <br>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Фильтры -->
        <div class="card">
            <h3>Фильтры и сортировка</h3>
            <form method="GET" class="filter-bar">
                <select name="category">
                    <option value="">Все категории</option>
                    <?php 
                    mysqli_data_seek($categories_res, 0);
                    while ($cat = mysqli_fetch_assoc($categories_res)): 
                    ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (!empty($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <input type="text" name="author" placeholder="Автор" value="<?php echo !empty($_GET['author']) ? $_GET['author'] : ''; ?>">
                <input type="number" name="year" placeholder="Год" value="<?php echo !empty($_GET['year']) ? $_GET['year'] : ''; ?>">
                
                <select name="sort">
                    <option value="">Сортировка</option>
                    <option value="title" <?php echo (!empty($_GET['sort']) && $_GET['sort'] == 'title') ? 'selected' : ''; ?>>По названию</option>
                    <option value="author" <?php echo (!empty($_GET['sort']) && $_GET['sort'] == 'author') ? 'selected' : ''; ?>>По автору</option>
                    <option value="price_asc" <?php echo (!empty($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Цена ↑</option>
                    <option value="price_desc" <?php echo (!empty($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Цена ↓</option>
                    <option value="year_asc" <?php echo (!empty($_GET['sort']) && $_GET['sort'] == 'year_asc') ? 'selected' : ''; ?>>Год ↑</option>
                    <option value="year_desc" <?php echo (!empty($_GET['sort']) && $_GET['sort'] == 'year_desc') ? 'selected' : ''; ?>>Год ↓</option>
                </select>
                
                <button type="submit" class="btn">Применить</button>
                <a href="index.php" class="btn btn-danger">Сбросить</a>
            </form>
        </div>
        
        <!-- Список книг -->
        <div class="books-grid">
            <?php if (mysqli_num_rows($books_res) > 0): ?>
                <?php while ($book = mysqli_fetch_assoc($books_res)): ?>
                    <div class="book-card">
                        <h3><?php echo $book['title']; ?></h3>
                        <div class="author">✍️ <?php echo $book['author']; ?></div>
                        <div class="category"><?php echo $book['category_name']; ?></div>
                        <div class="year"><?php echo $book['year']; ?> год</div>
                        <div class="price"><?php echo number_format($book['price'], 2); ?> ₽</div>
                        <div class="stock">В наличии: <?php echo $book['stock']; ?> шт.</div>
                        <span class="status status-<?php echo strtolower($book['status_name']); ?>">
                            <?php echo $book['status_name']; ?>
                        </span>
                        <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 5px;">
                            <?php if ($book['status_id'] == 1 && $book['stock'] > 0): ?>
                                <a href="rent.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-success">Арендовать</a>
                                <a href="buy.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('Купить книгу за <?php echo number_format($book['price'], 2); ?> ₽?')">Купить</a>
                            <?php else: ?>
                                <span style="color: #e74c3c; font-size: 13px;"> Недоступна</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; padding: 50px 0; color: #7f8c8d;">
                    Книги не найдены. Попробуйте изменить фильтры.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>