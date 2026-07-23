<?php
$host = 'doguyomayas.beget.app';
$dbname = 'task2_db';
$user = 'task2_db';
$pass = 'LIm4k&uRlz7V';

$link = mysqli_connect($host, $user, $pass, $dbname);

if (!$link) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}

mysqli_set_charset($link, "utf8");

session_start();
?>