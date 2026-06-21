<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="container">
    
<!-- Hero-секция с фоновым изображением -->
<section class="hero" style="background-image: url('assets/bg.jpg');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">Найди свой идеальный автомобиль</h1>
        <p class="hero-subtitle">Более 500 моделей в наличии. Кредит 0% на первые 3 месяца.</p>
        <a href="catalog.php" class="btn btn-primary">Перейти в каталог</a>
    </div>
</section>

    <section class="features">
        <h2>Почему выбирают нас</h2>
        <div class="features-grid">
            <div class="feature">
                <h3>Широкий выбор</h3>
                <p>Более 1000 автомобилей в наличии</p>
            </div>
            <div class="feature">
                <h3>Гарантия качества</h3>
                <p>Все автомобили проверены</p>
            </div>
            <div class="feature">
                <h3>Кредитование</h3>
                <p>Помощь в оформлении кредита</p>
            </div>
        </div>
    </section>
</div>

<!-- Популярные автомобили (динамическая выборка из БД) -->
<section class="popular-cars">
    <div class="container">
        <h2 class="section-title">Популярные модели</h2>
        <div class="cars-slider">
            <?php
            // Подключение к БД для выборки последних 4 авто
            
            $result = $conn->query("SELECT * FROM cars ORDER BY id DESC LIMIT 4");
            while ($car = $result->fetch_assoc()) {
                echo '<div class="car-card">
                        <img src="' . htmlspecialchars($car['image']) . '" alt="' . htmlspecialchars($car['brand'] . ' ' . $car['model']) . '">
                        <div class="car-card-body">
                            <h3>' . htmlspecialchars($car['brand'] . ' ' . $car['model']) . '</h3>
                            <div class="price">' . number_format($car['price'], 0, '.', ' ') . ' р.</div>
                            <a href="product.php?id=' . $car['id'] . '" class="btn btn-small">Подробнее</a>
                        </div>
                    </div>';
            }
            ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>