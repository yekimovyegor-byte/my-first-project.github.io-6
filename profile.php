<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Необходимо войти в систему для просмотра профиля';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Обработка загрузки чека
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_receipt'])) {
    $order_id = (int)$_POST['order_id'];
    $amount = (float)$_POST['amount'];
    $payment_date = $_POST['payment_date'];

    // Проверяем, что заказ принадлежит пользователю
    $check_order = "SELECT id, total_amount FROM orders WHERE id = $order_id AND user_id = $user_id AND payment_method = 'credit' OR payment_method = 'full'";
    $order_res = mysqli_query($conn, $check_order);
    if (mysqli_num_rows($order_res) === 0) {
        $error = 'Неверный заказ.';
    } elseif ($amount <= 0) {
        $error = 'Сумма должна быть положительной.';
    } elseif (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== 0) {
        $error = 'Необходимо загрузить чек.';
    } else {
        $order = mysqli_fetch_assoc($order_res);
        $remaining = $order['total_amount'];
        if ($amount == $remaining) {
            $error = 'Сумма платежа не может превышать остаток к оплате.';
        } else {
            $upload_dir = 'assets/receipts/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . $order_id . '_' . basename($_FILES['receipt']['name']);
            $target_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_path)) {
                $insert_payment = "INSERT INTO payments (order_id, amount, receipt_file, payment_date) 
                                   VALUES ($order_id, $amount, '$target_path', '$payment_date')";
                if (mysqli_query($conn, $insert_payment)) {
                    // Обновляем total_paid в заказе
                    $new_total = $order['total_paid'] + $amount;
                    $payment_status = ($new_total >= $order['total_amount']) ? 'paid' : 'partial';
                    $update_order = "UPDATE orders SET total_paid = $new_total, payment_status = '$payment_status' WHERE id = $order_id";
                    mysqli_query($conn, $update_order);
                    $success = 'Чек загружен, ожидает проверки администратором.';
                } else {
                    $error = 'Ошибка сохранения платежа.';
                }
            } else {
                $error = 'Ошибка загрузки файла.';
            }
        }
    }
}

// Получение информации о пользователе
$user_query = "SELECT username, full_name, email, phone, created_at FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Получение заказов с данными об автомобилях и платежами
$orders_query = "SELECT o.*, c.brand, c.model, c.price, c.image 
                 FROM orders o 
                 JOIN cars c ON o.car_id = c.id 
                 WHERE o.user_id = $user_id 
                 ORDER BY o.order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

require_once 'includes/header.php';
?>

<div class="container">
    <h1>Мой профиль</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <section class="profile-info">
        <h2>Личные данные</h2>
        <div class="info-grid">
            <div class="info-item"><strong>Логин:</strong> <?php echo htmlspecialchars($user['username']); ?></div>
            <div class="info-item"><strong>Имя:</strong> <?php echo htmlspecialchars($user['full_name']); ?></div>
            <div class="info-item"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></div>
            <div class="info-item"><strong>Телефон:</strong> <?php echo htmlspecialchars($user['phone']); ?></div>
            <div class="info-item"><strong>Дата регистрации:</strong> <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></div>
        </div>
    </section>

    <section class="profile-orders">
        <h2>Мои заказы</h2>

        <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                <?php
                $payments_query = "SELECT * FROM payments WHERE order_id = {$order['id']} ORDER BY payment_date DESC";
                $payments_result = mysqli_query($conn, $payments_query);
                $remaining = $order['total_amount'] - $order['total_paid'];
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">Заказ №<?php echo $order['id']; ?></span>
                        <span class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="order-car">
                        <?php if ($order['image']): ?>
                            <img src="<?php echo $order['image']; ?>" alt="<?php echo $order['brand']; ?>" class="order-car-image">
                        <?php endif; ?>
                        <div>
                            <strong><?php echo htmlspecialchars($order['brand'] . ' ' . $order['model']); ?></strong>
                            <span class="order-price"><?php echo number_format($order['total_amount'], 0, '.', ' '); ?> р.</span>
                        </div>
                    </div>
                    <div class="order-details">
                        <div class="order-method">
                            Способ оплаты: 
                            <strong><?php echo $order['payment_method'] == 'full' ? 'Полная оплата' : 'Кредит'; ?></strong>
                        </div>
                        <div class="order-status">
                            Статус заказа: 
                            <?php
                            $status_text = '';
                            switch ($order['admin_status']) {
                                case 'pending': $status_text = 'Ожидает подтверждения'; break;
                                case 'approved': $status_text = 'Активен'; break;
                                case 'rejected': $status_text = 'Отклонён'; break;
                            }
                            ?>
                            <span class="status-badge status-<?php echo $order['admin_status']; ?>"><?php echo $status_text; ?></span>
                        </div>
                        <div class="order-payment">
                            Статус оплаты: 
                            <?php
                            $payment_status_text = '';
                            switch ($order['payment_status']) {
                                case 'unpaid': $payment_status_text = 'Не оплачено'; break;
                                case 'partial': $payment_status_text = 'Частично оплачено'; break;
                                case 'paid': $payment_status_text = 'Оплачено полностью'; break;
                            }
                            ?>
                            <span class="status-badge payment-<?php echo $order['payment_status']; ?>"><?php echo $payment_status_text; ?></span>
                            <?php if ($order['payment_status'] != 'paid'): ?>
                                (Оплачено: <?php echo number_format($order['total_paid'], 0, '.', ' '); ?> р. / Осталось: <?php echo number_format($remaining, 0, '.', ' '); ?> р.)
                            <?php endif; ?>
                        </div>

                        <?php if ($order['payment_method'] == 'credit' && $order['admin_status'] == 'approved' && $order['payment_status'] != 'paid'): ?>
                            <div class="upload-receipt-form">
                                <h4>Загрузить чек ежемесячного платежа</h4>
                                <form method="POST" enctype="multipart/form-data" class="inline-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <div class="form-group">
                                        <label>Сумма платежа (₽):</label>
                                        <input type="number" name="amount" step="0.01" min="1" max="<?php echo $remaining; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Дата платежа:</label>
                                        <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Чек (изображение/PDF):</label>
                                        <input type="file" name="receipt" accept="image/*,application/pdf" required>
                                    </div>
                                    <button type="submit" name="upload_receipt" class="btn-small">Загрузить чек</button>
                                </form>
                            </div>
                        <?php endif; ?>

                         <?php if ($order['payment_method'] == 'full' && $order['admin_status'] == 'approved' && $order['payment_status'] != 'paid'): ?>
                            <div class="upload-receipt-form">
                                <h4>Загрузить чек платежа</h4>
                                <form method="POST" enctype="multipart/form-data" class="inline-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <div class="form-group">
                                        <label>Сумма платежа (р.):</label>
                                        <input type="number" name="amount" step="0.01" min="1" max="<?php echo $remaining; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Дата платежа:</label>
                                        <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Чек (изображение/PDF):</label>
                                        <input type="file" name="receipt" accept="image/*,application/pdf" required>
                                    </div>
                                    <button type="submit" name="upload_receipt" class="btn-small">Загрузить чек</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <?php if (mysqli_num_rows($payments_result) > 0): ?>
                            <div class="payments-history">
                                <h4>История платежей</h4>
                                <table class="payments-table">
                                    <thead>
                                        <tr><th>Дата</th><th>Сумма</th><th>Статус</th><th>Чек</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                                            <tr>
                                                <td><?php echo date('d.m.Y', strtotime($payment['payment_date'])); ?></td>
                                                <td><?php echo number_format($payment['amount'], 0, '.', ' '); ?> ₽</td>
                                                <td>
                                                    <?php if ($payment['status'] == 'pending'): ?>
                                                        <span class="status-badge status-pending">На проверке</span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-approved">Подтверждён</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><a href="<?php echo $payment['receipt_file']; ?>" target="_blank">Просмотр</a></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-orders">У вас пока нет заказов. <a href="catalog.php">Перейти в каталог</a></div>
        <?php endif; ?>
    </section>
</div>

<style>
.order-card {
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    background: #fff;
}
.order-header {
    background: #f4f4f4;
    padding: 10px 15px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
}
.order-car {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
}
.order-car-image {
    width: 80px;
    height: 60px;
    object-fit: cover;
}
.order-price {
    font-weight: bold;
    color: #28a745;
    margin-left: 15px;
}
.order-details {
    padding: 0 15px 15px 15px;
    border-top: 1px solid #eee;
}
.order-method, .order-status, .order-payment {
    margin: 10px 0;
}
.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 0.8rem;
}
.status-pending { background: #fff3cd; color: #856404; }
.status-approved { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }
.payment-unpaid { background: #f8d7da; color: #721c24; }
.payment-partial { background: #fff3cd; color: #856404; }
.payment-paid { background: #d4edda; color: #155724; }
.upload-receipt-form {
    margin: 15px 0;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 5px;
}
.upload-receipt-form h4 {
    margin: 0 0 10px 0;
}
.inline-form .form-group {
    margin-bottom: 10px;
}
.payments-history {
    margin-top: 15px;
}
.payments-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}
.payments-table th, .payments-table td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
</style>

<?php require_once 'includes/footer.php'; ?>