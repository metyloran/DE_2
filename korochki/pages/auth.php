<?php
// pages/auth.php

// 1. Подключаем соединение с базой данных
require_once __DIR__ . '/../helpers/db.php';

// 2. Запускаем сессию
session_start();

// Если пользователь уже авторизован — перенаправляем на главную
if (!empty($_SESSION['user_id'])) {
    header('Location: /DE/korochki/pages/index.php');
    exit;
}

// 3. Инициализируем переменные
$errors = [];
$fieldErrors = []; // Массив для ошибок конкретных полей
$login = '';

// 4. Обрабатываем отправку формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    // Простая валидация на пустоту
    if ($login === '') {
        $fieldErrors['login'] = 'Введите логин.';
    }
    if ($password === '') {
        $fieldErrors['password'] = 'Введите пароль.';
    }

    // Если базовых ошибок нет, ищем пользователя в БД
    if (empty($fieldErrors)) {
        $stmt = $pdo->prepare(
            'SELECT p.id_polzovately, p.login, p.parol, p.fio, p.rol_id, r.rol
             FROM polzovately p
             LEFT JOIN roli r ON r.id_roli = p.rol_id
             WHERE p.login = :login
             LIMIT 1'
        );
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();

        // Сравниваем хеши (MD5, как в вашей текущей реализации)
        if ($user && md5($password) === $user['parol']) {
            // Успешный вход — сохраняем данные в сессию
            $_SESSION['user_id']      = (int)$user['id_polzovately'];
            $_SESSION['user_login']   = $user['login'];
            $_SESSION['user_fio']     = $user['fio'];
            $_SESSION['user_role_id'] = (int)$user['rol_id'];
            $_SESSION['user_role']    = $user['rol'];

            // Перенаправляем: админ → в админ-панель, обычный → на главную
            if ((int)$user['rol_id'] === 2) {
                header('Location: /DE/korochki/pages/admin.php');
            } else {
                header('Location: /DE/korochki/pages/index.php');
            }
            exit;
        } else {
            // Безопасная практика: не указывать, что именно неверно (логин или пароль)
            $fieldErrors['login'] = 'Неверный логин или пароль.';
            $fieldErrors['password'] = 'Неверный логин или пароль.';
        }
    }
    
    // Объединяем ошибки полей в общий массив для верхнего уведомления
    $errors = array_values($fieldErrors);
}

// 5. Формируем контент для страницы
$pageTitle = 'Авторизация - Корочки.есть';

ob_start();
?>

<!-- Стили для подсветки ошибок (идентичны странице регистрации) -->
<style>
    .form-group.is-invalid input {
        border-color: #dc3545;
        background-color: #fff8f8;
    }
    .field-error {
        color: #dc3545;
        font-size: 0.85em;
        margin-top: 4px;
        display: block;
    }
    .error-messages {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
    }
    .error-messages p {
        margin: 5px 0;
    }
</style>

<div class="auth-form-container">
    <h2>Вход в систему</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <p><strong>Ошибка авторизации:</strong></p>
            <?php foreach ($errors as $error): ?>
                <p class="error-text">• <?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- action="" гарантирует отправку на текущий скрипт -->
    <form method="POST" action="" novalidate>
        <div class="form-group <?php echo isset($fieldErrors['login']) ? 'is-invalid' : ''; ?>">
            <label for="login">Логин</label>
            <input type="text" id="login" name="login"
                   value="<?php echo htmlspecialchars($login); ?>" required autocomplete="username">
            <?php if (isset($fieldErrors['login'])): ?>
                <small class="field-error"><?php echo htmlspecialchars($fieldErrors['login']); ?></small>
            <?php endif; ?>
        </div>

        <div class="form-group <?php echo isset($fieldErrors['password']) ? 'is-invalid' : ''; ?>">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <?php if (isset($fieldErrors['password'])): ?>
                <small class="field-error"><?php echo htmlspecialchars($fieldErrors['password']); ?></small>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-submit">Войти</button>
    </form>

    <p class="auth-switch">
        Еще нет аккаунта?
        <a href="/DE/korochki/pages/register.php">Регистрация</a>
    </p>
</div>

<?php
$content = ob_get_clean();

// 6. Подключаем layout для отображения страницы
require_once __DIR__ . '/layout.php';
?>