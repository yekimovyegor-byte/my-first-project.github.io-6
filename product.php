<?php
require_once 'includes/config.php';

$car_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$car_id) {
    redirect('catalog.php');
}

$query = "SELECT * FROM cars WHERE id = $car_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    redirect('catalog.php');
}

$car = mysqli_fetch_assoc($result);

require_once 'includes/header.php';
?>

<div class="container">
    <div class="product-detail">
        <div class="product-image">
            <?php if ($car['image']): ?>
                <img src="<?php echo $car['image']; ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
            <?php else: ?>
                <div class="no-image-large">Нет изображения</div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <h1><?php echo $car['brand'] . ' ' . $car['model']; ?></h1>
            <p class="product-price"><?php echo number_format($car['price'], 0, '.', ' '); ?> р.</p>
            
            <div class="product-description">
                <h3>Описание:</h3>
                <p><?php echo nl2br($car['description']); ?></p>
            </div>

            <?php if (isLoggedIn()): ?>
                <a href="buy.php?car_id=<?php echo $car['id']; ?>" class="btn btn-large">Купить сейчас</a>
            <?php else: ?>
                <p>Для покупки необходимо <a href="login.php">войти</a> или <a href="register.php">зарегистрироваться</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>