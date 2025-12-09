<?php
session_start();
$user_name = $_SESSION['user']['name'] ?? null;



$products_full = json_decode(file_get_contents(__DIR__ . '/products.json'), true);
$all_categories = [];

if (is_array($products_full)) {
    $all_categories = array_unique(array_column($products_full, 'category'));
    sort($all_categories);
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>O nas — Electro Shop</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="header-left">
        <button class="category-toggle" id="categoryToggleBtn" aria-label="Kategorie">&#9776;</button>
        <h1>Electro Shop</h1>
        <form method="get" class="search-bar">
            <input type="text" name="q" placeholder="🔍 Szukaj produktu..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit">Szukaj</button>
        </form>
    </div>

    <nav>
    <?php if (isset($_SESSION['user'])): ?>

        <a href="logout.php" class="logout-btn">Wyloguj się</a>
    <?php else: ?>
        <a href="register.php" class="<?= basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : '' ?>">👨‍💻 Zarejestruj się</a>
        <a href="login.php" class="<?= basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>">🔑 Zaloguj się</a>
    <?php endif; ?>
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">📦 Katalog</a>
    <a href="about.php" class="<?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">ℹ️ O nas</a>
    <a href="cart.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">🛒 Koszyk (<?= array_sum($_SESSION['cart'] ?? []); ?>)</a>
</nav>

</header>



<aside class="category-sidebar" id="categorySidebar">
    <nav class="category-nav">
        <h3>Kategorie</h3>
        <ul>
            <?php foreach ($all_categories as $cat): ?>
                <li><a href="index.php?category=<?= urlencode($cat) ?>"><?= ucfirst($cat) ?></a></li>
            <?php endforeach; ?>
            <li><a href="index.php">📂 Wszystkie produkty</a></li>
        </ul>

        <hr class="menu-divider">

        <h3>Menu sklepu</h3>
        <ul>
            <li><a href="cart.php">🛒 Koszyk (<?= array_sum($_SESSION['cart']); ?>)</a></li>
            <li><a href="#">📦 Śledź przesyłkę</a></li>
            <li><a href="#">📍 Lokalizacja</a></li>
            <li><a href="about.php">💬 Kontakt</a></li>
            <li><a href="#">💰 Kredyt</a></li>
            <li><a href="#">❓ Pomoc</a></li>
        </ul>
    </nav>
</aside>


<div class="overlay" id="overlay"></div>

<main>
    <section class="about">
        <h2>O nas:</h2>
        <p><strong>Electro Shop</strong> to nowoczesny sklep internetowy z elektroniką, w którym technologia spotyka się z pasją. Od 2020 roku pomagamy naszym klientom wybierać najlepsze urządzenia — od smartfonów po komputery gamingowe.</p>

        <h3>🎯 Nasza misja</h3>
        <p>Wierzymy, że technologia ma ułatwiać życie, a nie je komplikować. Dlatego oferujemy tylko sprawdzony sprzęt od renomowanych producentów, z pełną gwarancją i szybką dostawą.</p>

        <h3>⚙️ Co oferujemy?</h3>
        <ul>
            <li>✅ Smartfony i akcesoria do nich</li>
            <li>💻 Laptopy i komputery stacjonarne</li>
            <li>🎧 Słuchawki, głośniki i zestawy audio</li>
            <li>⌚ Smartwatche i urządzenia fitness</li>
            <li>🔋 Powerbanki, ładowarki i inne akcesoria</li>
        </ul>

        <h3>💬 Opinie klientów</h3>
        <div class="reviews">
            <blockquote>
                <p>„Super obsługa i błyskawiczna dostawa! Mój nowy laptop dotarł w 24 godziny.”</p>
                <footer>— Anna, Warszawa</footer>
            </blockquote>
            <blockquote>
                <p>„Kupuję tutaj regularnie. Zawsze dobre ceny i pomocny support.”</p>
                <footer>— Tomasz, Kraków</footer>
            </blockquote>
            <blockquote>
                <p>„Zamówiłem słuchawki, przyszły następnego dnia, 100% oryginał. Polecam!”</p>
                <footer>— Karolina, Gdańsk</footer>
            </blockquote>
        </div>

        <h3>📦 Wysyłka i gwarancja</h3>
        <p>Współpracujemy z zaufanymi firmami kurierskimi. Każdy produkt objęty jest minimum 12-miesięczną gwarancją producenta.</p>

        <h3>📍 Kontakt</h3>
        <p><strong>Adres:</strong> Warszawa, ul. Przykładowa 12</p>
        <p><strong>Telefon:</strong> +48 600 123 456</p>
        <p><strong>Email:</strong> kontakt@electroshop.pl</p>

        <h3>🕓 Godziny pracy</h3>
        <p>Poniedziałek – Piątek: 9:00 – 18:00<br>
        Sobota: 10:00 – 15:00<br>
        Niedziela: nieczynne</p>
    </section>
</main>

<footer>
    <p>© 2025 Electro Shop — Sklep z technologią</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('categorySidebar');
    const toggleBtn = document.getElementById('categoryToggleBtn');
    const overlay = document.getElementById('overlay');


    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    });
});
</script>
</body>
</html>
