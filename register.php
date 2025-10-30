<?php
session_start();

$usersFile = __DIR__ . '/users.json';

// === создаём файл, если его нет ===
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode(new stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$usersRaw = json_decode(file_get_contents($usersFile), true) ?? [];

// === нормализуем структуру users[email] = userData ===
$users = [];
if (is_array($usersRaw)) {
    $is_list = array_keys($usersRaw) === range(0, count($usersRaw) - 1);
    if ($is_list) {
        foreach ($usersRaw as $u) {
            if (!empty($u['email'])) {
                $users[trim($u['email'])] = $u;
            }
        }
    } else {
        $users = $usersRaw;
    }
}

$admin_email = 'admin@electroshop.pl';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass_raw = $_POST['password'] ?? '';

    // === Валидация ===
    if ($name === '' || $email === '' || $pass_raw === '') {
        $error = '⚠️ Wypełnij wszystkie pola.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '❌ Nieprawidłowy adres e-mail.';
    } elseif ($email === $admin_email) {
        $error = '⚠️ Ten adres e-mail jest zarezerwowany.';
    } elseif (isset($users[$email])) {
        $error = '📧 Konto z tym adresem e-mail już istnieje.';
    } else {
        // === Добавляем пользователя ===
        $users[$email] = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($pass_raw, PASSWORD_DEFAULT)
        ];

        if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
            $error = '❌ Błąd zapisu pliku. Sprawdź uprawnienia serwera.';
        } else {
            $_SESSION['user'] = ['name' => $name, 'email' => $email];
            $_SESSION['success_message'] = "✅ Konto zostało pomyślnie utworzone! Witaj, $name 👋";
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rejestracja — Electro Shop</title>
<link rel="stylesheet" href="style.css">
<style>
body {
    background: #f5f7fb;
    font-family: 'Inter', sans-serif;
    margin: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
main {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 15px;
}
.register-box {
    background: #fff;
    padding: 55px 65px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    text-align: center;
    width: 100%;
    max-width: 450px;
    animation: fadeIn .5s ease;
    transition: transform .3s ease;
}
.register-box:hover {
    transform: translateY(-3px);
}
.register-box h2 {
    color: #2a7;
    font-weight: 700;
    font-size: 1.9em;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.register-box input {
    width: 100%;
    padding: 14px;
    margin-bottom: 15px;
    border: 1.6px solid #ddd;
    border-radius: 10px;
    font-size: 1em;
    transition: all 0.25s ease;
}
.register-box input:focus {
    border-color: #2a7;
    box-shadow: 0 0 0 4px rgba(42,167,100,0.15);
    outline: none;
}
.register-box button {
    width: 100%;
    background: #2a7;
    color: #fff;
    font-weight: 600;
    border: none;
    padding: 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: background .25s, transform .1s;
}
.register-box button:hover {
    background: #1f5;
    transform: translateY(-2px);
}
.register-box p {
    margin-top: 15px;
    font-size: 0.95em;
}
.register-box a {
    color: #2a7;
    text-decoration: none;
    font-weight: 600;
}
.register-box a:hover {
    text-decoration: underline;
}
.error {
    background: #ffeaea;
    color: #d33;
    border: 1px solid #f5b0b0;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 15px;
    font-weight: 500;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@media (max-width: 480px) {
    .register-box { padding: 40px 30px; }
    .register-box h2 { font-size: 1.6em; }
}
</style>
</head>
<body>
<header>
    <div class="header-left">
        <button class="category-toggle" id="categoryToggleBtn">&#9776;</button>
        <h1>Electro Shop</h1>
    </div>
    <nav>
        <?php if (basename($_SERVER['PHP_SELF']) !== 'register.php'): ?>
            <a href="register.php">Rejestracja 🧑‍💻</a>
        <?php endif; ?>

        <?php if (basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
            <a href="login.php">Zaloguj się 🔑</a>
        <?php endif; ?>

        <a href="index.php">Katalog</a>
        <a href="about.php">O nas</a>
        <a href="cart.php">Koszyk (<?= array_sum($_SESSION['cart'] ?? []); ?>)</a>
    </nav>
</header>

<main>
    <div class="register-box">
        <h2>Zarejestruj się 🧑‍💻</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="text" name="name" placeholder="Imię i nazwisko" required>
            <input type="email" name="email" placeholder="Adres e-mail" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <button type="submit">Utwórz konto</button>
        </form>
        <p>Masz już konto? <a href="login.php">Zaloguj się 🔑</a></p>
    </div>
</main>

<footer>
    <p>© 2025 Electro Shop — Wszystkie prawa zastrzeżone.</p>
</footer>
</body>
</html>
