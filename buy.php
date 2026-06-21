<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Необходимо авторизоваться для покупки';
    redirect('login.php');
}

$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

if (!$car_id) {
    redirect('catalog.php');
}

// Проверка существования автомобиля
$car_query = "SELECT * FROM cars WHERE id = $car_id";
$car_result = mysqli_query($conn, $car_query);
if (mysqli_num_rows($car_result) === 0) {
    redirect('catalog.php');
}
$car = mysqli_fetch_assoc($car_result);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method']; // 'full' or 'credit'
    $user_id = $_SESSION['user_id'];

    // Проверяем, не оформлен ли уже заказ на этот авто
    $check_query = "SELECT id FROM orders WHERE user_id = $user_id AND car_id = $car_id AND admin_status IN ('pending', 'approved')";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $error = 'Вы уже оформили заказ на этот автомобиль. Дождитесь обработки или проверьте историю заказов.';
    } else {
        // Создаём заказ
        $total_amount = $car['price'];
        $insert_order = "INSERT INTO orders (user_id, car_id, payment_method, total_amount) 
                         VALUES ($user_id, $car_id, '$payment_method', $total_amount)";
        if (mysqli_query($conn, $insert_order)) {
            $order_id = mysqli_insert_id($conn);

            // Если полная оплата, обрабатываем чек
           /* if ($payment_method === 'full' && isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0) {
                $upload_dir = 'assets/receipts/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_name = time() . '_' . $order_id . '_' . basename($_FILES['receipt']['name']);
                $target_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_path)) {
                    // Сохраняем платёж
                    $payment_date = date('Y-m-d');
                    $insert_payment = "INSERT INTO payments (order_id, amount, receipt_file, payment_date) 
                                       VALUES ($order_id, $total_amount, '$target_path', '$payment_date')";
                    mysqli_query($conn, $insert_payment);
                    // Обновляем сумму оплаты в заказе (автоматически)
                    $update_order = "UPDATE orders SET total_paid = total_paid + $total_amount, payment_status = 'paid' WHERE id = $order_id";
                    mysqli_query($conn, $update_order);
                } else {
                    $error = 'Ошибка загрузки чека. Заказ создан, но чек не прикреплён.';
                }
            }
*/
            if (empty($error)) {
                $_SESSION['success'] = 'Заказ успешно оформлен! Ожидайте подтверждения администратора.';
                redirect('profile.php');
            }
        } else {
            $error = 'Ошибка оформления заказа: ' . mysqli_error($conn);
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2>Оформление заказа</h2>
        <div class="car-summary">
            <h3><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
            <p class="price"><?php echo number_format($car['price'], 0, '.', ' '); ?> ₽</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Способ оплаты:</label>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="full" required> 
                        Полная оплата (прикрепить чек)
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="credit" required> 
                        Кредит (ежемесячные платежи, чеки прикрепляйте в личном кабинете)
                    </label>
                </div>
            </div>

           <!-- <div id="receipt-block" style="display:none;">
                <div class="form-group">
                    <label for="receipt">Чек об оплате (изображение):</label>
                    <input type="file" id="receipt" name="receipt" accept="image/*,application/pdf">
                    <small class="form-text">Загрузите чек на полную сумму. После подтверждения администратором заказ будет выполнен.</small>
                </div>
            </div>--->

            <button type="submit" class="btn">Оформить заказ</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const receiptBlock = document.getElementById('receipt-block');
        if (this.value === 'full') {
            receiptBlock.style.display = 'block';
            document.getElementById('receipt').required = true;
        } else {
            receiptBlock.style.display = 'none';
            document.getElementById('receipt').required = false;
        }
    });
});
</script>

<style>
.payment-options {
    margin-top: 5px;
}
.payment-option {
    display: block;
    margin: 10px 0;
    cursor: pointer;
}
.car-summary {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.car-summary h3 {
    margin: 0 0 10px 0;
}
.price {
    font-size: 1.3rem;
    font-weight: bold;
    color: #28a745;
}
</style>

<?php require_once 'includes/footer.php'; ?>