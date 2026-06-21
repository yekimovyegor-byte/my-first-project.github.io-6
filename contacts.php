<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="container">
    <h1>Контакты</h1>
    
    <div class="contacts-grid">
        <div class="contact-info">
            <h2>Наш адрес</h2>
            <p>г. Москва, ул. Автомобильная, д. 1</p>
            
            <h2>Телефон</h2>
            <p>+7 (999) 123-45-67</p>
            
            <h2>Email</h2>
            <p>info@autosalon.ru</p>
            
            <h2>Режим работы</h2>
            <p>Пн-Пт: 9:00 - 20:00</p>
            <p>Сб-Вс: 10:00 - 18:00</p>
        </div>
        
        <div class="contact-form">
            <h2>Напишите нам</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Ваше имя:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Сообщение:</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                
                <button type="submit" class="btn">Отправить</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>