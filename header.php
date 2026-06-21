<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Автосалон</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image" href="../assets/logo .jpg">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="../index.php" class="logo">Автосалон</a>
                <ul class="nav-menu">
                    <li><a href="../index.php">Главная</a></li>
                    <li><a href="../catalog.php">Каталог</a></li>
                    <li><a href="../contacts.php">Контакты</a></li>
                   <?php if (isLoggedIn()): ?>
    <?php if (isAdmin()): ?>
        <li><a href="../admin/index.php">Админ панель</a></li>
    <?php endif; ?>
    <li><a href="../profile.php">Профиль</a></li>
    <li><a href="../logout.php">Выйти (<?php echo $_SESSION['user_name']; ?>)</a></li>
<?php else: ?>
    <li><a href="../register.php">Регистрация</a></li>
    <li><a href="../login.php">Вход</a></li>
<?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main>