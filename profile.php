<?php
session_start();
$usersFile = __DIR__ . '/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (isset($users[$email]) && password_verify($password, $users[$email]['password'])) {
        $_SESSION['user'] = $users[$email];
        header("Location: profile.php");
        exit;
    } else {
        $error = "❌ Nieprawidłowy email lub hasło";
    }
}
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Logowanie — Electro Shop</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="header-left">
        <button class="category-toggle" id="categoryToggleBtn">&#9776;</button>
        <h1>Electro Shop</h1>
    </div>
    <nav>
        <a href="register.php">🧑‍💻 Rejestracja</a>
        <a href="login.php" class="active">Logowanie 🔑</a>
        <a href="index.php">Katalog</a>
        <a href="about.php">O nas</a>
        <a href="cart.php">Koszyk (<?= array_sum($_SESSION['cart'] ?? []); ?>)</a>
    </nav>
</header>

<main class="auth-container">
    <div class="auth-box">
        <h2>Zaloguj się 🔑</h2>
        <?php if ($error): ?>
            <p style="color:red;font-weight:600;"><?= $error ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Adres e-mail" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <button type="submit">Zaloguj się</button>
        </form>
        <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
    </div>
</main>

<footer>
    <p>© 2025 Electro Shop — Wszystkie prawa zastrzeżone.</p>
</footer>
</body>
</html>
