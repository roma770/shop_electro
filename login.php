<?php
session_start();
require_once __DIR__ . '/db.php';

$usersFile = __DIR__ . '/users.json';
$usersRaw = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// --- нормализуем структуру users
$users = [];
if (is_array($usersRaw)) {
    $is_list = array_keys($usersRaw) === range(0, count($usersRaw) - 1);
    if ($is_list) {
        foreach ($usersRaw as $u) {
            if (!empty($u['email'])) $users[trim($u['email'])] = $u;
        }
    } else {
        $users = $usersRaw;
    }
}

// === админ ===
$admin_email = 'admin@electroshop.pl';
$admin_password = 'admin1234';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- вход администратора
    if ($email === $admin_email && $password === $admin_password) {
        $_SESSION['user'] = ['name' => 'Administrator', 'email' => $admin_email];
        $_SESSION['success_message'] = "👑 Witaj ponownie, Administrator!";
        header("Location: index.php");
        exit;
    }

    // --- вход через PostgreSQL
    if ($conn) {
        $res = @pg_query_params($conn, "SELECT * FROM users WHERE email=$1", [$email]);
        if ($res && pg_num_rows($res) > 0) {
            $dbUser = pg_fetch_assoc($res);
            if (password_verify($password, $dbUser['password'])) {
                $_SESSION['user'] = [
                    'name' => $dbUser['username'],
                    'email' => $dbUser['email']
                ];
                $_SESSION['success_message'] = "👋 Witaj ponownie, " . htmlspecialchars($dbUser['username']) . "!";
                header("Location: index.php");
                exit;
            }
        }
    }

    // --- вход через JSON
    if ($email && isset($users[$email]) && password_verify($password, $users[$email]['password'])) {
        $_SESSION['user'] = $users[$email];
        $_SESSION['success_message'] = "👋 Witaj ponownie, " . htmlspecialchars($users[$email]['name']) . "!";
        header("Location: index.php");
        exit;
    }

    $error = "❌ Nieprawidłowy adres e-mail lub hasło.";
}
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Zaloguj się — Electro Shop</title>
<link rel="stylesheet" href="style.css">
<style>
body {
    background: #f5f7fb;
    font-family: 'Inter', sans-serif;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
main.auth-container {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
}
.auth-box {
    background: #fff;
    padding: 60px 70px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    text-align: center;
    max-width: 450px;
    width: 100%;
    animation: fadeIn 0.5s ease;
}
.auth-box:hover { transform: translateY(-3px); }
.auth-box h2 {
    font-size: 1.9em;
    color: #2a7;
    margin-bottom: 25px;
    font-weight: 700;
}
.auth-box input {
    width: 100%; padding: 14px; margin-bottom: 16px;
    border: 1.6px solid #ddd; border-radius: 10px; font-size: 1em;
    transition: all 0.25s;
}
.auth-box input:focus {
    border-color: #2a7;
    box-shadow: 0 0 0 4px rgba(42,167,100,0.15);
}
.auth-box button {
    width: 100%; background: #2a7; color: #fff;
    border: none; border-radius: 10px; padding: 14px;
    font-size: 1em; font-weight: 600; cursor: pointer;
    transition: background 0.25s, transform 0.1s;
}
.auth-box button:hover { background: #1f5; transform: translateY(-2px); }
.error {
    background: #ffeaea; color: #d33;
    border: 1px solid #f5b0b0; padding: 10px;
    border-radius: 10px; margin-bottom: 15px;
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

<main class="auth-container">
    <div class="auth-box">
        <h2>Zaloguj się 🔑</h2>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="email" name="email" placeholder="Adres e-mail" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <button type="submit">Zaloguj się 🔑</button>
        </form>
        <p>Nie masz konta? <a href="register.php">Zarejestruj się 🧑‍💻</a></p>
    </div>
</main>

<footer><p>© 2025 Electro Shop — Wszystkie prawa zastrzeżone.</p></footer>
</body>
</html>
