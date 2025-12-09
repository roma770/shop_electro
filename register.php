<?php
session_start();
require_once __DIR__ . '/db.php';

$usersFile = __DIR__ . '/users.json';


if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode(new stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}


$usersRaw = json_decode(file_get_contents($usersFile), true) ?? [];
$users = [];
if (is_array($usersRaw)) {
    $is_list = array_keys($usersRaw) === range(0, count($usersRaw) - 1);
    if ($is_list) {
        foreach ($usersRaw as $u) {
            if (!empty($u['email'])) $users[$u['email']] = $u;
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
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = '⚠️ Wypełnij wszystkie pola.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '❌ Nieprawidłowy adres e-mail.';
    } elseif ($email === $admin_email) {
        $error = '⚠️ Ten adres e-mail jest zarezerwowany.';
    } elseif (isset($users[$email])) {
        $error = '📧 Konto z tym adresem e-mail już istnieje.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $users[$email] = ['name' => $name, 'email' => $email, 'password' => $hash];
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($conn) {
            @pg_query_params($conn,
                "INSERT INTO users (username, email, password, role) VALUES ($1,$2,$3,'user')",
                [$name, $email, $hash]
            );
        }

       
        if ($conn) {
    $insert_result = @pg_query_params(
        $conn,
        "INSERT INTO users (username, email, password, role)
         VALUES ($1, $2, $3, 'user')",
        [$name, $email, $hash]
    );

    if ($insert_result) {
        echo "<p style='color:green;'>✅ Użytkownik zapisany w PostgreSQL!</p>";
    } else {
        echo "<p style='color:red;'>❌ Błąd zapisu do PostgreSQL: " . pg_last_error($conn) . "</p>";
    }
}

        $_SESSION['user'] = ['name' => $name, 'email' => $email];
        $_SESSION['success_message'] = "✅ Konto zostało pomyślnie utworzone! Witaj, $name 👋";
        header('Location: index.php');
        exit;
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
    display: flex; flex-direction: column;
    min-height: 100vh; margin: 0;
}
main {
    flex: 1; display: flex;
    justify-content: center; align-items: center;
    padding: 40px 15px;
}
.register-box {
    background: #fff; padding: 55px 65px; border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    text-align: center; width: 100%; max-width: 450px;
    animation: fadeIn .5s ease;
}
.register-box h2 { color: #2a7; font-weight: 700; margin-bottom: 25px; }
.register-box input {
    width: 100%; padding: 14px; margin-bottom: 15px;
    border: 1.6px solid #ddd; border-radius: 10px; font-size: 1em;
}
.register-box button {
    width: 100%; background: #2a7; color: #fff; border: none;
    border-radius: 10px; padding: 14px; font-weight: 600;
}
.error { background: #ffeaea; color: #d33; border-radius: 10px; padding: 10px; margin-bottom: 15px; }
</style>
</head>
<body>
<header>
    <div class="header-left"><h1>Electro Shop</h1></div>
    <nav>
        <a href="login.php">Zaloguj się 🔑</a>
        <a href="index.php">Katalog</a>
        <a href="about.php">O nas</a>
        <a href="cart.php">Koszyk (<?= array_sum($_SESSION['cart'] ?? []); ?>)</a>
    </nav>
</header>

<main>
    <div class="register-box">
        <h2>Zarejestruj się 🧑‍💻</h2>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="text" name="name" placeholder="Imię i nazwisko" required>
            <input type="email" name="email" placeholder="Adres e-mail" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <button type="submit">Utwórz konto</button>
        </form>
        <p>Masz już konto? <a href="login.php">Zaloguj się 🔑</a></p>
    </div>
</main>
<footer><p>© 2025 Electro Shop — Wszystkie prawa zastrzeżone.</p></footer>
</body>
</html>
