<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$car_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$car_id) {
    redirect('index.php');
}

// Получение данных автомобиля
$query = "SELECT * FROM cars WHERE id = $car_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    redirect('index.php');
}

$car = mysqli_fetch_assoc($result);
$error = '';
$success = '';

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $price = (float)$_POST['price'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Валидация
    if (empty($brand) || empty($model) || empty($price) || empty($description)) {
        $error = 'Все поля обязательны для заполнения';
    } else {
        // Начинаем с базового запроса без изображения
        $update_query = "UPDATE cars SET 
                        brand = '$brand', 
                        model = '$model', 
                        price = $price, 
                        description = '$description'";
        
        // Обработка загрузки нового изображения
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            $file_type = $_FILES['image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = '../assets/uploads/';
                
                // Создаем папку если её нет
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Удаляем старое изображение если оно есть
                if (!empty($car['image'])) {
                    $old_image_path = '../' . $car['image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                // Генерируем уникальное имя файла
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = time() . '_' . uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_path = 'assets/uploads/' . $file_name;
                    $update_query .= ", image = '$image_path'";
                }
            } else {
                $error = 'Допустимые форматы изображений: JPG, PNG, GIF';
            }
        }
        
        // Завершаем запрос
        $update_query .= " WHERE id = $car_id";
        
        if (empty($error)) {
            if (mysqli_query($conn, $update_query)) {
                $success = 'Автомобиль успешно обновлен';
                
                // Обновляем данные для отображения
                $result = mysqli_query($conn, $query);
                $car = mysqli_fetch_assoc($result);
            } else {
                $error = 'Ошибка обновления: ' . mysqli_error($conn);
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>Редактирование автомобиля</h1>
        <a href="index.php" class="btn">Вернуться в админ-панель</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="edit-form-container">
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label for="brand">Бренд:</label>
                <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($car['brand']); ?>" required>
            </div>

            <div class="form-group">
                <label for="model">Марка:</label>
                <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($car['model']); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Цена (руб):</label>
                <input type="number" id="price" name="price" value="<?php echo $car['price']; ?>" min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description" rows="10" required><?php echo htmlspecialchars($car['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Текущее изображение:</label>
                <?php if ($car['image']): ?>
                    <div class="current-image">
                        <img src="../<?php echo $car['image']; ?>" alt="Текущее изображение" style="max-width: 300px; max-height: 200px;">
                        <p class="image-path"><?php echo $car['image']; ?></p>
                    </div>
                <?php else: ?>
                    <p>Изображение не загружено</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image">Загрузить новое изображение (оставьте пустым, чтобы не менять):</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                <small class="form-text">Допустимые форматы: JPG, PNG, GIF. Максимальный размер: 5MB</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="index.php" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 0;
    padding: 20px 0;
    border-bottom: 2px solid #333;
}

.edit-form-container {
    max-width: 800px;
    margin: 0 auto 50px;
    padding: 30px;
    background: #f9f9f9;
    border-radius: 5px;
}

.current-image {
    margin: 15px 0;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-align: center;
}

.current-image img {
    max-width: 100%;
    height: auto;
    border-radius: 3px;
}

.image-path {
    margin-top: 10px;
    color: #666;
    font-size: 0.9rem;
    word-break: break-all;
}

.form-text {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 0.9rem;
}

.form-actions {
    margin-top: 30px;
    display: flex;
    gap: 15px;
}

textarea {
    resize: vertical;
    min-height: 150px;
}
</style>

<?php require_once '../includes/footer.php'; ?>