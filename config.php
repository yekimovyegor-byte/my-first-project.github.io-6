<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'autosalon_db');

// Создание подключения
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Проверка подключения
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Установка кодировки
mysqli_set_charset($conn, "utf8");

// Функция для проверки авторизации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Функция для проверки прав администратора
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Функция для перенаправления
function redirect($url) {
    header("Location: $url");
    exit();
}
?>