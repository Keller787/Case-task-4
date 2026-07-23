<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$rental_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$rental_id) {
    header('Location: my_rentals.php');
    exit;
}

// Проверяем, что аренда принадлежит пользователю
$rental_query = "SELECT * FROM rentals WHERE id = $rental_id AND user_id = $user_id AND status = 'active'";
$rental_res = mysqli_query($link, $rental_query);
$rental = mysqli_fetch_assoc($rental_res);

if (!$rental) {
    header('Location: my_rentals.php');
    exit;
}

// Возвращаем книгу
$update_rental = "UPDATE rentals SET status = 'returned' WHERE id = $rental_id";
mysqli_query($link, $update_rental);

// Увеличиваем количество книг в наличии
$book_query = "SELECT stock FROM books WHERE id = {$rental['book_id']}";
$book_res = mysqli_query($link, $book_query);
$book = mysqli_fetch_assoc($book_res);
$new_stock = $book['stock'] + 1;
$update_book = "UPDATE books SET stock = $new_stock, status_id = 1 WHERE id = {$rental['book_id']}";
mysqli_query($link, $update_book);

header('Location: my_rentals.php?returned=1');
exit;
?>