// Валидация форм и интерактивность для страниц входа/регистрации
function initAuth() {
    // Форма регистрации
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('fullName');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmPassword');
            
            // Очистка ошибок
            clearErrors(registerForm);
            
            let isValid = true;
            
            // Валидация имени
            if (!name.value.trim() || name.value.length < 2) {
                showError(name, 'Ім\'я повинно містити не менше 2 символів');
                isValid = false;
            }
            
            // Валидация email
            if (!isValidEmail(email.value)) {
                showError(email, 'Введіть коректну email адресу');
                isValid = false;
            }
            
            // Валидация пароля
            if (password.value.length < 6) {
                showError(password, 'Пароль повинен містити не менше 6 символів');
                isValid = false;
            }
            
            // Подтверждение пароля
            if (confirmPassword && password.value !== confirmPassword.value) {
                showError(confirmPassword, 'Паролі не співпадають');
                isValid = false;
            }
            
            if (isValid) {
                showLoading(registerForm);
                registerForm.submit();
            }
        });
        
        // Live-валидация
        const inputs = registerForm.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
    }
    
    // Форма входа
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail');
            const password = document.getElementById('loginPassword');
            
            clearErrors(loginForm);
            
            let isValid = true;
            
            if (!isValidEmail(email.value)) {
                showError(email, 'Введіть коректну email адресу');
                isValid = false;
            }
            
            if (!password.value.trim()) {
                showError(password, 'Введіть пароль');
                isValid = false;
            }
            
            if (isValid) {
                showLoading(loginForm);
                loginForm.submit();
            }
        });
    }
    
    // Показать/скрыть пароль
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                input.type = 'password';
                this.innerHTML = '<i class="bi bi-eye"></i>';
            }
        });
    });
    
    // Переключение между входом и регистрацией (анимация)
    const switchToRegister = document.getElementById('switchToRegister');
    const switchToLogin = document.getElementById('switchToLogin');
    const loginCard = document.getElementById('loginCard');
    const registerCard = document.getElementById('registerCard');
    
    if (switchToRegister && loginCard && registerCard) {
        switchToRegister.addEventListener('click', function(e) {
            e.preventDefault();
            loginCard.style.display = 'none';
            registerCard.style.display = 'block';
            registerCard.classList.add('fade-in');
        });
    }
    
    if (switchToLogin && loginCard && registerCard) {
        switchToLogin.addEventListener('click', function(e) {
            e.preventDefault();
            registerCard.style.display = 'none';
            loginCard.style.display = 'block';
            loginCard.classList.add('fade-in');
        });
    }
}

// Валидация email
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Показать ошибку
function showError(input, message) {
    input.classList.add('is-invalid');
    const feedback = input.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
    } else {
        const div = document.createElement('div');
        div.className = 'invalid-feedback';
        div.textContent = message;
        input.parentNode.appendChild(div);
    }
}

// Очистить ошибки
function clearErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
}

// Валидация поля
function validateField(input) {
    const id = input.id;
    
    if (id === 'fullName' || id === 'name') {
        if (!input.value.trim() || input.value.length < 2) {
            showError(input, 'Ім\'я повинно містити не менше 2 символів');
        } else {
            input.classList.remove('is-invalid');
        }
    }
    
    if (id === 'email' || id === 'loginEmail') {
        if (!isValidEmail(input.value)) {
            showError(input, 'Введіть коректну email адресу');
        } else {
            input.classList.remove('is-invalid');
        }
    }
    
    if (id === 'password') {
        if (input.value.length > 0 && input.value.length < 6) {
            showError(input, 'Пароль повинен містити не менше 6 символів');
        } else if (input.value.length >= 6) {
            input.classList.remove('is-invalid');
            // Показать индикатор силы пароля
            updatePasswordStrength(input.value);
        }
    }
}

// Индикатор силы пароля
function updatePasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');
    if (!strengthBar) return;
    
    let strength = 0;
    let messages = [];
    
    if (password.length >= 6) { strength++; } else { messages.push('Мінімум 6 символів'); }
    if (password.match(/[a-z]/)) { strength++; } else { messages.push('Маленька літера'); }
    if (password.match(/[A-Z]/)) { strength++; } else { messages.push('Велика літера'); }
    if (password.match(/[0-9]/)) { strength++; } else { messages.push('Цифра'); }
    if (password.match(/[^a-zA-Z0-9]/)) { strength++; } else { messages.push('Спецсимвол'); }
    
    const width = (strength / 5) * 100;
    strengthBar.style.width = width + '%';
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthBar.className = 'progress-bar';
        if (strengthText) strengthText.textContent = 'Введіть пароль для оцінки надійності';
        return;
    }
    
    let label, className;
    if (strength <= 2) {
        label = 'Слабкий';
        className = 'bg-danger';
    } else if (strength <= 3) {
        label = 'Середній';
        className = 'bg-warning';
    } else if (strength <= 4) {
        label = 'Хороший';
        className = 'bg-info';
    } else {
        label = 'Сильний 🎉';
        className = 'bg-success';
    }
    
    strengthBar.className = 'progress-bar ' + className;
    
    if (strengthText) {
        const msg = messages.length > 0 ? ' (' + messages.join(', ') + ')' : '';
        strengthText.textContent = 'Надійність: ' + label + msg;
        strengthText.className = strength <= 2 ? 'text-danger' : (strength <= 3 ? 'text-warning' : 'text-success');
    }
}

// Показать загрузку
function showLoading(form) {
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Завантаження...';
        
        // Восстановление через 10 секунд (на случай ошибки)
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }, 10000);
    }
}