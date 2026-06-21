<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Валидация пароля
    $password_pattern = '/^(?=.*[A-Za-zА-Яа-я])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/u';
    if (!preg_match($password_pattern, $password)) {
        $error = 'Пароль должен содержать минимум 8 символов, включая буквы, цифры и специальные символы (!@#$%^&*() и др.)';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } else {
        // Проверка уникальности пользователя
        $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Пользователь с таким логином или email уже существует';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, full_name, email, phone, password) 
                            VALUES ('$username', '$full_name', '$email', '$phone', '$hashed_password')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = 'Регистрация успешна! <a href="login.php">Войти</a>';
            } else {
                $error = 'Ошибка регистрации: ' . mysqli_error($conn);
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2>Регистрация</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="username">Логин:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="full_name">Имя:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" 
                       pattern="(?=.*[A-Za-zА-Яа-я])(?=.*\d)(?=.*[!@#$%^&*(),.?\`:{}|<>]).{8,}"
                       title="Минимум 8 символов, содержащих буквы, цифры и спецсимволы (например, !@#$%^&*)"
                       required>
                <small class="password-hint">Пароль должен содержать минимум 8 символов, включая буквы, цифры и специальные символы</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Повторите пароль:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn">Зарегистрироваться</button>
        </form>

        <p class="text-center">Уже есть аккаунт? <a href="login.php">Войдите</a></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const form = document.getElementById('registerForm');

    if (passwordInput) {
        // Элементы для отображения требований
        const reqDiv = document.createElement('div');
        reqDiv.className = 'password-requirements';
        reqDiv.innerHTML = `
            <ul>
                <li class="req-length">Минимум 8 символов</li>
                <li class="req-letters">Содержит буквы</li>
                <li class="req-digits">Содержит цифры</li>
                <li class="req-special">Содержит спецсимволы (!@#$%^&*() и др.)</li>
            </ul>
        `;
        passwordInput.parentNode.appendChild(reqDiv);

        // Функция проверки пароля в реальном времени
        const updateRequirements = function() {
            const val = passwordInput.value;
            const lengthOk = val.length >= 8;
            const lettersOk = /[A-Za-zА-Яа-я]/.test(val);
            const digitsOk = /\d/.test(val);
            const specialOk = /[!@#$%^&*(),.?":{}|<>]/.test(val);
            
            document.querySelector('.req-length').classList.toggle('valid', lengthOk);
            document.querySelector('.req-letters').classList.toggle('valid', lettersOk);
            document.querySelector('.req-digits').classList.toggle('valid', digitsOk);
            document.querySelector('.req-special').classList.toggle('valid', specialOk);
            
            const allValid = lengthOk && lettersOk && digitsOk && specialOk;
            passwordInput.setCustomValidity(allValid ? '' : 'Пароль не соответствует требованиям');
        };
        
        passwordInput.addEventListener('input', updateRequirements);
        updateRequirements(); // начальное состояние
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            if (passwordInput.value !== confirmInput.value) {
                confirmInput.setCustomValidity('Пароли не совпадают');
            } else {
                confirmInput.setCustomValidity('');
            }
        });
    }

    // Дополнительная проверка перед отправкой формы
    form.addEventListener('submit', function(e) {
        if (passwordInput && passwordInput.validity.customError) {
            e.preventDefault();
            alert(passwordInput.validationMessage);
        }
        if (confirmInput && confirmInput.validity.customError) {
            e.preventDefault();
            alert(confirmInput.validationMessage);
        }
    });
});
</script>

<style>
.password-requirements {
    font-size: 0.85rem;
    margin-top: 5px;
    color: #666;
}
.password-requirements ul {
    margin: 0;
    padding-left: 20px;
}
.password-requirements li {
    list-style: none;
    position: relative;
    padding-left: 20px;
    margin: 3px 0;
}
.password-requirements li::before {
    content: "✘";
    position: absolute;
    left: 0;
    color: #dc3545;
}
.password-requirements li.valid::before {
    content: "✓";
    color: #28a745;
}
.password-hint {
    display: block;
    font-size: 0.8rem;
    color: #666;
    margin-top: 5px;
}
</style>

<?php require_once 'includes/footer.php'; ?>