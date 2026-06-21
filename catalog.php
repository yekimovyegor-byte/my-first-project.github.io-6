<?php
require_once 'includes/config.php';

if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

// Получение параметров фильтрации
$brand_filter = isset($_GET['brand']) ? mysqli_real_escape_string($conn, $_GET['brand']) : '';
$model_filter = isset($_GET['model']) ? mysqli_real_escape_string($conn, $_GET['model']) : '';
$price_min = isset($_GET['price_min']) ? (int)$_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) ? (int)$_GET['price_max'] : 1000000;

// Базовый запрос
$query = "SELECT * FROM cars WHERE 1=1";

if (!empty($brand_filter)) {
    $query .= " AND brand LIKE '%$brand_filter%'";
}

if (!empty($model_filter)) {
    $query .= " AND model LIKE '%$model_filter%'";
}

if ($price_min > 0) {
    $query .= " AND price >= $price_min";
}

if ($price_max < 1000000) {
    $query .= " AND price <= $price_max";
}

$query .= " ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);

// Получение уникальных брендов для фильтра
$brands_query = "SELECT DISTINCT brand FROM cars ORDER BY brand";
$brands_result = mysqli_query($conn, $brands_query);

require_once 'includes/header.php';
?>

<div class="container">
    <h1>Каталог автомобилей</h1>

    <!-- Форма фильтрации -->
    <div class="filter-section">
        <form method="GET" action="" class="filter-form">
            <div class="filter-group">
                <label for="brand">Бренд:</label>
                <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($brand_filter); ?>" placeholder="Введите бренд">
            </div>

            <div class="filter-group">
                <label for="model">Марка:</label>
                <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($model_filter); ?>" placeholder="Введите марку">
            </div>

            <div class="filter-group">
                <label for="price_min">Цена от:</label>
                <input type="number" id="price_min" name="price_min" value="<?php echo $price_min; ?>" min="0">
            </div>

            <div class="filter-group">
                <label for="price_max">Цена до:</label>
                <input type="number" id="price_max" name="price_max" value="<?php echo $price_max; ?>" min="0">
            </div>

            <button type="submit" class="btn">Применить фильтр</button>
            <a href="catalog.php" class="btn btn-secondary">Сбросить</a>
        </form>
    </div>

    <!-- Список автомобилей -->
    <div class="cars-grid">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($car = mysqli_fetch_assoc($result)): ?>
                <div class="car-card">
                    <?php if ($car['image']): ?>
                        <img src="<?php echo $car['image']; ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>" class="car-image">
                    <?php else: ?>
                        <div class="no-image">Нет изображения</div>
                    <?php endif; ?>
                    
                    <div class="car-info">
                        <h3><?php echo $car['brand'] . ' ' . $car['model']; ?></h3>
                        <p class="price"><?php echo number_format($car['price'], 0, '.', ' '); ?> р.</p>
                        <p class="description"><?php echo mb_substr($car['description'], 0, 100) . '...'; ?></p>
                        
                        <div class="car-actions">
                            <a href="product.php?id=<?php echo $car['id']; ?>" class="btn">Подробнее</a>
                            <?php if (isLoggedIn()): ?>
                                <a href="buy.php?car_id=<?php echo $car['id']; ?>" class="btn btn-primary">Купить</a>
                            <?php else: ?>
                                <a href="login.php" class="btn">Войдите для покупки</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-results">Автомобили не найдены</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>