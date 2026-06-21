// Подтверждение удаления
document.addEventListener('DOMContentLoaded', function() {
    // Подтверждение для ссылок с классом confirm-delete
    const deleteLinks = document.querySelectorAll('a[onclick*="confirm"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('onclick').match(/'([^']+)'/)[1])) {
                e.preventDefault();
            }
        });
    });

    // Валидация формы регистрации
    const registerForm = document.querySelector('form[action="register.php"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Пароли не совпадают!');
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Пароль должен содержать минимум 6 символов!');
            }
        });
    }

    // Маска для телефона
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
            e.target.value = !x[2] ? x[1] : '+' + x[1] + ' (' + x[2] + ') ' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
        });
    }

    // Фильтрация цен
    const priceMin = document.getElementById('price_min');
    const priceMax = document.getElementById('price_max');
    
    if (priceMin && priceMax) {
        priceMin.addEventListener('change', function() {
            if (priceMax.value && parseInt(priceMin.value) > parseInt(priceMax.value)) {
                priceMax.value = priceMin.value;
            }
        });
        
        priceMax.addEventListener('change', function() {
            if (priceMin.value && parseInt(priceMax.value) < parseInt(priceMin.value)) {
                priceMin.value = priceMax.value;
            }
        });
    }
});