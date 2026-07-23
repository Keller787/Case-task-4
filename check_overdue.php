<?php
// Этот файл можно запускать каждый день, поставить как задачу
// или вызывать автоматически при входе в админку

require_once 'config.php';

// Находим все просроченные аренды
$overdue_query = "SELECT r.*, u.login, u.email, b.title 
                  FROM rentals r 
                  JOIN users u ON r.user_id = u.id 
                  JOIN books b ON r.book_id = b.id 
                  WHERE r.status = 'active' AND r.return_date < CURDATE()";
$overdue_res = mysqli_query($link, $overdue_query);

$notification_count = 0;

while ($overdue = mysqli_fetch_assoc($overdue_res)) {
    // Здесь можно отправить email-уведомление пользователю
    // или записать в таблицу уведомлений
    
    $days = floor((time() - strtotime($overdue['return_date'])) / (60 * 60 * 24));
    
    // Логируем уведомление
    $log = "Пользователю {$overdue['login']} ({$overdue['email']}) отправлено напоминание ";
    $log .= "о книге «{$overdue['title']}». Просрочка: $days дней.";
    
    // Можно добавить запись в отдельную таблицу notifications
    // или отправить email через mail()
    
    $notification_count++;
}

// Записываем время последней проверки
$log_file = "overdue_log.txt";
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Проверено. Найдено: $notification_count\n", FILE_APPEND);

// Если файл вызывается через браузер
if (isset($_SERVER['HTTP_HOST'])) {
    echo "Проверка выполнена. Найдено просрочек: $notification_count";
}
?>