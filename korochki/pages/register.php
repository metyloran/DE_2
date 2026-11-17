<?php
// pages/register.php
require_once __DIR__ . '/../helpers/db.php';
session_start();

// Если уже авторизован — на главную
if (!empty($_SESSION['user_id'])) {
    header('Location: /DE/korochki/pages/index.php');
    exit;
}

$errors = [];
$fieldErrors = []; // Массив для ошибок конкретных полей
$login    = '';
$fio      = '';
$telephon = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $fio      = trim($_POST['fio'] ?? '');
    $telephon = trim($_POST['telephon'] ?? '');

    // ---------- Валидация логина ----------
    if ($login === '') {
        $fieldErrors['login'] = 'Введите логин.';
    } elseif (!preg_match('/^[A-Za-z0-9]{6,}$/', $login)) {
        $fieldErrors['login'] = 'Только латиница и цифры, не менее 6 символов.';
    }

    // ---------- Валидация пароля ----------
    if ($password === '') {
        $fieldErrors['password'] = 'Введите пароль.';
    } elseif (mb_strlen($password) < 8) {
        $fieldErrors['password'] = 'Пароль должен содержать минимум 8 символов.';
    }

    // ---------- Валидация ФИО ----------
    if ($fio === '') {
        $fieldErrors['fio'] = 'Введите ФИО.';
    } elseif (!preg_match('/^[А-Яа-яЁё\s\-]+$/u', $fio)) {
        $fieldErrors['fio'] = 'Используйте только кириллицу, пробелы и дефис.';
    }

    // ---------- Валидация телефона (строгий формат 8(999)123-45-67) ----------
    if ($telephon === '') {
        $fieldErrors['telephon'] = 'Введите номер телефона.';
    } elseif (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $telephon)) {
        $fieldErrors['telephon'] = 'Неверный формат. Должен быть: 8(999)123-45-67';
    }

    // Объединяем ошибки полей в общий массив для верхнего уведомления
    $errors = array_values($fieldErrors);

    // ---------- Проверка уникальности логина ----------
    if (empty($errors)) {
        $check = $pdo->prepare('SELECT id_polzovately FROM polzovately WHERE login = :login LIMIT 1');
        $check->execute(['login' => $login]);
        if ($check->fetch()) {
            $fieldErrors['login'] = 'Этот логин уже занят.';
            $errors[] = $fieldErrors['login'];
        }
    }

    // ---------- Вставка пользователя ----------
    if (empty($errors)) {
        // ⚠️ Рекомендация: используйте password_hash($password, PASSWORD_DEFAULT) вместо md5 для безопасности
        $hash = md5($password); 

        $stmt = $pdo->prepare(
            'INSERT INTO polzovately (login, parol, fio, telephon, rol_id)
             VALUES (:login, :parol, :fio, :telephon, :rol_id)'
        );
        $stmt->execute([
            'login'    => $login,
            'parol'    => $hash,
            'fio'      => $fio,
            'telephon' => $telephon,
            'rol_id'   => 1, // 1 = пользователь
        ]);

        // Автоматический вход после регистрации
        $_SESSION['user_id']      = (int)$pdo->lastInsertId();
        $_SESSION['user_login']   = $login;
        $_SESSION['user_fio']     = $fio;
        $_SESSION['user_role_id'] = 1;

        header('Location: /DE/korochki/pages/index.php');
        exit;
    }
}

$pageTitle = 'Регистрация - Корочки.есть';

ob_start();
?>

<!-- Небольшие стили для подсветки ошибок (можно перенести в ваш CSS файл) -->
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
    <h2>Регистрация</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <p><strong>Пожалуйста, исправьте следующие ошибки:</strong></p>
            <?php foreach ($errors as $error): ?>
                <p class="error-text">• <?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- action="" гарантирует отправку на тот же скрипт -->
    <form method="POST" action="" novalidate id="registerForm">
        <div class="form-group <?php echo isset($fieldErrors['login']) ? 'is-invalid' : ''; ?>">
            <label for="login">Логин</label>
            <input type="text" id="login" name="login"
                   value="<?php echo htmlspecialchars($login); ?>" required>
            <?php if (isset($fieldErrors['login'])): ?>
                <small class="field-error"><?php echo htmlspecialchars($fieldErrors['login']); ?></small>
            <?php else: ?>
                <small class="hint">Латиница и цифры, не менее 6 символов</small>
            <?php endif; ?>
        </div>

        <div class="form-group <?php echo isset($fieldErrors['password']) ? 'is-invalid' : ''; ?>">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>
            <?php if (isset($fieldErrors['password'])): ?>
                <small class="field-error"><?php echo htmlspecialchars($fieldErrors['password']); ?></small>
            <?php else: ?>
                <small class="hint">Минимум 8 символов</small>
            <?php endif; ?>
        </div>

        <div class="form-group <?php echo isset($fieldErrors['fio']) ? 'is-invalid' : ''; ?>">
            <label for="fio">ФИО</label>
            <input type="text" id="fio" name="fio"
                   value="<?php echo htmlspecialchars($fio); ?>" required>
            <?php if (isset($fieldErrors['fio'])): ?>
                <small class="field-error"><?php echo htmlspecialchars($fieldErrors['fio']); ?></small>
            <?php else: ?>
                <small class="hint">Используйте только символы кириллицы и пробелы</small>
            <?php endif; ?>
        </div>

        <div class="form-group <?php echo isset($fieldErrors['telephon']) ? 'is-invalid' : ''; ?>">
            <label for="telephon">Телефон</label>
            <input type="text" id="telephon" name="telephon"
                   placeholder="8(999)123-45-67"
                   value="<?php echo htmlspecialchars($telephon); ?>" required maxlength="16">
            <?php if (isset($fieldErrors['telephon'])): ?>
                <small class="field-error"><?php echo htmlspecialchars($fieldErrors['telephon']); ?></small>
            <?php else: ?>
                <small class="hint">Формат: 8(999)123-45-67</small>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-submit">Зарегистрироваться</button>
    </form>

    <p class="auth-switch">
        Уже есть аккаунт?
        <a href="/DE/korochki/pages/auth.php">Войти</a>
    </p>
</div>

<!-- Скрипт автоматического форматирования телефона -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('telephon');

    phoneInput.addEventListener('input', function(e) {
        // Удаляем все нецифровые символы
        let value = e.target.value.replace(/\D/g, '');
        
        // Если пользователь начал вводить с 9 или 7, заменяем на 8
        if (value.length > 0) {
            if (value[0] === '9') {
                value = '8' + value;
            } else if (value[0] === '7') {
                value = '8' + value.substring(1);
            } else if (value[0] !== '8') {
                value = '8' + value; // Принудительно начинаем с 8
            }
        }

        // Ограничиваем длину 11 цифрами (8 + 10 цифр номера)
        value = value.substring(0, 11);

        // Формируем строку в формате 8(999)123-45-67
        let formattedValue = '';
        if (value.length > 0) {
            formattedValue = '8';
        }
        if (value.length > 1) {
            formattedValue += '(' + value.substring(1, 4);
        }
        if (value.length >= 5) {
            formattedValue += ')' + value.substring(4, 7);
        }
        if (value.length >= 8) {
            formattedValue += '-' + value.substring(7, 9);
        }
        if (value.length >= 10) {
            formattedValue += '-' + value.substring(9, 11);
        }

        e.target.value = formattedValue;
    });

    // Улучшаем работу Backspace: если стираем до "8(", очищаем поле полностью
    phoneInput.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && e.target.value.length <= 3) {
            e.target.value = '';
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>