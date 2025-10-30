<?php
session_start();
$user_name = $_SESSION['user']['name'] ?? null;

$products = json_decode(file_get_contents(__DIR__ . '/products.json'), true);
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// === Обновление / очистка / оформление ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        foreach ($_POST['qty'] as $id => $q) {
            $q = max(0, (int)$q);
            if ($q === 0) unset($_SESSION['cart'][$id]);
            else $_SESSION['cart'][$id] = $q;
        }
    } elseif (isset($_POST['clear'])) {
        $_SESSION['cart'] = [];
    } elseif (isset($_POST['checkout'])) {
        header('Location: checkout.php');
        exit;
    }
    header('Location: cart.php');
    exit;
}

function cart_total($products) {
    $sum = 0.0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        if (isset($products[$id])) $sum += $products[$id]['price'] * $qty;
    }
    return $sum;
}

// === Категории ===
$all_categories = [];
if (is_array($products)) {
    $all_categories = array_unique(array_column($products, 'category'));
    sort($all_categories);
}
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Koszyk — Electro Shop</title>
<link rel="stylesheet" href="style.css">

<style>
body {
    background: #f6f7fb;
    font-family: 'Inter', sans-serif;
}

/* === ПУСТАЯ КОРЗИНА === */
.empty-cart {
    text-align: center;
    background: #fff;
    max-width: 600px;
    margin: 120px auto;
    padding: 50px 40px;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    animation: fadeIn .5s ease;
}
.empty-cart-icon {
    font-size: 80px;
    margin-bottom: 15px;
    animation: float 2s ease-in-out infinite;
}
.empty-cart h2 {
    color: #1d8348;
    font-size: 1.8em;
    margin-bottom: 12px;
}
.empty-cart p {
    color: #555;
    line-height: 1.6;
    margin-bottom: 25px;
}
.back-to-shop {
    display: inline-block;
    background: #2a7;
    color: #fff;
    text-decoration: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 600;
    transition: background .25s, transform .15s;
}
.back-to-shop:hover {
    background: #1f5;
    transform: translateY(-2px);
}

/* === ТАБЛИЦА КОРЗИНЫ === */
.cart-wrapper {
    max-width: 900px;
    margin: 50px auto;
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.07);
}
.cart {
    width: 100%;
    border-collapse: collapse;
    text-align: center;
}
.cart th, .cart td {
    padding: 14px;
    border-bottom: 1px solid #eee;
}
.cart th {
    color: #1a1a1a;
    font-weight: 600;
}
.cart img {
    width: 60px;
    border-radius: 8px;
}
.cart input[type="number"] {
    width: 60px;
    text-align: center;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

.cart-footer {
    margin-top: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cart-footer .total {
    font-size: 1.2em;
    color: #333;
}
.cart-footer .actions button {
    background: #2a7;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 18px;
    font-weight: 600;
    cursor: pointer;
    margin-left: 10px;
    transition: background .25s, transform .15s;
}
.cart-footer .actions button:hover {
    background: #1f5;
    transform: translateY(-2px);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}
</style>
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
        <a href="logout.php" class="logout-btn">🚪 Wyloguj się</a>
    <?php else: ?>
        <a href="register.php" class="<?= basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : '' ?>">👨‍💻 Zarejestruj się</a>
        <a href="login.php" class="<?= basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>">🔑 Zaloguj się</a>
    <?php endif; ?>
        <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">📦 Katalog</a>
        <a href="about.php" class="<?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">ℹ️ O nas</a>
        <a href="cart.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">🛒 Koszyk (<?= array_sum($_SESSION['cart'] ?? []); ?>)</a>
    </nav>
</header>

<!-- === Боковое меню === -->
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
<form method="post">
<?php if (empty($_SESSION['cart'])): ?>
    <div class="empty-cart">
        <div class="empty-cart-icon">🛒</div>
        <h2>Twój koszyk jest pusty</h2>
        <p>Wygląda na to, że nie dodałeś jeszcze żadnych produktów.<br>Sprawdź nasz katalog i wybierz coś dla siebie!</p>
        <a href="index.php" class="back-to-shop">⬅ Wróć do sklepu</a>
    </div>
<?php else: ?>
    <div class="cart-wrapper">
        <table class="cart">
            <thead>
                <tr><th>Produkt</th><th>Cena</th><th>Ilość</th><th>Suma</th></tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $id => $qty): 
                    if (!isset($products[$id])) continue;
                    $p = $products[$id];
                ?>
                <tr>
                    <td class="product-cell">
                        <div class="product-thumb">
                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                        </div>
                        <span class="product-title"><?= htmlspecialchars($p['title']) ?></span>
                    </td>
                    <td><?= number_format($p['price'], 2, ',', ' ') ?> zł</td>
                    <td><input type="number" name="qty[<?= $id ?>]" value="<?= $qty ?>" min="0"></td>
                    <td><?= number_format($p['price'] * $qty, 2, ',', ' ') ?> zł</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-footer">
            <p class="total">Całkowito: <strong><?= number_format(cart_total($products), 2, ',', ' ') ?> zł</strong></p>
            <div class="actions">
                <button type="submit" name="update">Zaktualizuj 🔁</button>
                <button type="submit" name="checkout">Zamów 💳</button>
                <button type="submit" name="clear">🗑 Wyczyść</button>
            </div>
        </div>
    </div>
<?php endif; ?>
</form>
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
