<?php
require_once 'config.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = '';

// Добавление книги
if (!empty($_POST['action']) && $_POST['action'] == 'add') {
    $title = mysqli_real_escape_string($link, $_POST['title']);
    $author = mysqli_real_escape_string($link, $_POST['author']);
    $category_id = (int)$_POST['category_id'];
    $year = (int)$_POST['year'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $status_id = (int)$_POST['status_id'];
    $description = mysqli_real_escape_string($link, $_POST['description']);
    
    $query = "INSERT INTO books (title, author, category_id, year, price, stock, status_id, description) 
              VALUES ('$title', '$author', $category_id, $year, $price, $stock, $status_id, '$description')";
    
    if (mysqli_query($link, $query)) {
        $message = 'Книга успешно добавлена!';
        $message_type = 'success';
    } else {
        $message = 'Ошибка при добавлении книги';
        $message_type = 'danger';
    }
}

// Редактирование книги
if (!empty($_POST['action']) && $_POST['action'] == 'edit') {
    $id = (int)$_POST['id'];
    $title = mysqli_real_escape_string($link, $_POST['title']);
    $author = mysqli_real_escape_string($link, $_POST['author']);
    $category_id = (int)$_POST['category_id'];
    $year = (int)$_POST['year'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $status_id = (int)$_POST['status_id'];
    $description = mysqli_real_escape_string($link, $_POST['description']);
    
    $query = "UPDATE books SET 
              title='$title', author='$author', category_id=$category_id, 
              year=$year, price=$price, stock=$stock, status_id=$status_id, description='$description'
              WHERE id=$id";
    
    if (mysqli_query($link, $query)) {
        $message = 'Книга успешно обновлена!';
        $message_type = 'success';
    } else {
        $message = 'Ошибка при обновлении книги';
        $message_type = 'danger';
    }
}

// Удаление книги
if (!empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM books WHERE id=$id";
    if (mysqli_query($link, $query)) {
        $message = 'Книга удалена!';
        $message_type = 'success';
    } else {
        $message = 'Ошибка при удалении';
        $message_type = 'danger';
    }
}

$books_query = "SELECT b.*, c.name as category_name, s.name as status_name 
                FROM books b 
                JOIN categories c ON b.category_id = c.id 
                JOIN book_statuses s ON b.status_id = s.id 
                ORDER BY b.id DESC";
$books_res = mysqli_query($link, $books_query);

$categories_res = mysqli_query($link, "SELECT * FROM categories ORDER BY name");
$statuses_res = mysqli_query($link, "SELECT * FROM book_statuses ORDER BY name");

// Для редактирования
$edit_book = null;
if (!empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = mysqli_query($link, "SELECT * FROM books WHERE id=$edit_id");
    $edit_book = mysqli_fetch_assoc($edit_query);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Управление книгами</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="admin.php" class="logo"><span>Админ-панель</span></a>
            <nav>
                <a href="admin.php">Статистика</a>
                <a href="admin_books.php" class="active">Управление книгами</a>
                <a href="admin_rentals.php">Управление арендами</a>
                <a href="index.php">Магазин</a>
                <a href="logout.php">Выход</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1>Управление книгами</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Форма добавления/редактирования -->
        <div class="card">
            <h3><?php echo $edit_book ? 'Редактировать книгу' : '➕ Добавить новую книгу'; ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_book ? 'edit' : 'add'; ?>">
                <?php if ($edit_book): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_book['id']; ?>">
                <?php endif; ?>
                
                <input type="text" name="title" placeholder="Название" value="<?php echo $edit_book ? $edit_book['title'] : ''; ?>" required>
                <input type="text" name="author" placeholder="Автор" value="<?php echo $edit_book ? $edit_book['author'] : ''; ?>" required>
                
                <select name="category_id" required>
                    <option value="">Выберите категорию</option>
                    <?php 
                    mysqli_data_seek($categories_res, 0);
                    while ($cat = mysqli_fetch_assoc($categories_res)): 
                    ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_book && $edit_book['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <input type="number" name="year" placeholder="Год" value="<?php echo $edit_book ? $edit_book['year'] : ''; ?>" required>
                <input type="number" name="price" placeholder="Цена" step="0.01" value="<?php echo $edit_book ? $edit_book['price'] : ''; ?>" required>
                <input type="number" name="stock" placeholder="Количество в наличии" value="<?php echo $edit_book ? $edit_book['stock'] : ''; ?>" required>
                
                <select name="status_id" required>
                    <option value="">Выберите статус</option>
                    <?php 
                    mysqli_data_seek($statuses_res, 0);
                    while ($status = mysqli_fetch_assoc($statuses_res)): 
                    ?>
                        <option value="<?php echo $status['id']; ?>" <?php echo ($edit_book && $edit_book['status_id'] == $status['id']) ? 'selected' : ''; ?>>
                            <?php echo $status['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <textarea name="description" placeholder="Описание книги"><?php echo $edit_book ? $edit_book['description'] : ''; ?></textarea>
                
                <button type="submit" class="btn"><?php echo $edit_book ? 'Обновить' : 'Добавить'; ?></button>
                <?php if ($edit_book): ?>
                    <a href="admin_books.php" class="btn btn-danger">Отмена</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Список книг -->
        <div class="card">
            <h3>Список книг (<?php echo mysqli_num_rows($books_res); ?>)</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Автор</th>
                            <th>Категория</th>
                            <th>Год</th>
                            <th>Цена</th>
                            <th>В наличии</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($books_res) > 0): ?>
                            <?php while ($book = mysqli_fetch_assoc($books_res)): ?>
                                <tr>
                                    <td><?php echo $book['id']; ?></td>
                                    <td><?php echo $book['title']; ?></td>
                                    <td><?php echo $book['author']; ?></td>
                                    <td><?php echo $book['category_name']; ?></td>
                                    <td><?php echo $book['year']; ?></td>
                                    <td><?php echo number_format($book['price'], 2); ?> ₽</td>
                                    <td><?php echo $book['stock']; ?></td>
                                    <td>
                                        <span class="status status-<?php echo strtolower($book['status_name']); ?>">
                                            <?php echo $book['status_name']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="admin_books.php?edit=<?php echo $book['id']; ?>" class="btn btn-sm btn-warning" title="Редактировать"></a>
                                        <a href="admin_books.php?delete=<?php echo $book['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить книгу «<?php echo $book['title']; ?>»?')" title="Удалить"></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 20px; color: #7f8c8d;">
                                    Нет книг в каталоге
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