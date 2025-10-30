<?php


session_start();
$user_name = $_SESSION['user']['name'] ?? null;
$admin_email = 'admin@electroshop.pl';

if (!empty($_SESSION['success_message'])) {
    echo "<div class='alert-success'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
    unset($_SESSION['success_message']);
}

// === Показываем сообщение о регистрации ===
$success_message = $_SESSION['success_message'] ?? '';
if ($success_message) {
    echo "<div class='alert-success'>$success_message</div>";
    unset($_SESSION['success_message']);
}

// === Загружаем товары ===
$products_full = json_decode(file_get_contents(__DIR__ . '/products.json'), true);
$products = $products_full;
$all_categories = [];
if (is_array($products_full)) {
    $all_categories = array_unique(array_column($products_full, 'category'));
    sort($all_categories);
}

// === Фильтрация по категории ===
$category_query = trim($_GET['category'] ?? '');
$page_title = 'Wszystkie produkty:';
if ($category_query !== '') {
    $products = array_filter($products, fn($p) => $p['category'] === $category_query);
    $page_title = 'Kategoria: ' . ucfirst($category_query);
}

// === Поиск ===
$search_query = trim($_GET['q'] ?? '');
if ($search_query !== '') {
    $products = array_filter($products, function($p) use ($search_query) {
        return stripos($p['title'], $search_query) !== false || stripos($p['description'], $search_query) !== false;
    });
    $page_title = 'Wyniki wyszukiwania dla: "' . htmlspecialchars($search_query) . '"';
}

// === Корзина ===
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $id = $_POST['product_id'];
    if (isset($products_full[$id])) {
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    }
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Electro Shop — Sklep z technologią</title>
<link rel="stylesheet" href="style.css">
<style>
/* === ДОПОЛНИТЕЛЬНО ДЛЯ МОДАЛКИ === */
.product { cursor: pointer; transition: transform 0.2s ease; }
.product:hover { transform: translateY(-3px); }

.product-modal {
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    z-index: 2000;
    width: 600px;
    max-width: 95%;
    display: none;
}
.product-modal.open { display: block; }

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 { margin: 0; color: #222; }
.close-btn {
    font-size: 22px;
    background: none;
    border: none;
    cursor: pointer;
}
.modal-content {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}
.modal-content img {
    width: 220px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.modal-details ul {
    margin: 8px 0 0 0;
    padding-left: 0;
    list-style: none;
}
.modal-details li {
    margin-bottom: 6px;
}
.modal-details li::before {
    content: "✅ ";
}
.modal-details .price {
    color: #2a7;
    font-weight: bold;
    font-size: 1.2em;
}
.modal-benefits {
    margin-top: 15px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    color: #2a7;
}
.modal-benefits span {
    background: #e8f5e9;
    padding: 6px 10px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.modal-details button {
    background: #2a7;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 18px;
    cursor: pointer;
    margin-top: 10px;
    font-weight: bold;
}
.modal-details button:hover { background: #1f5; }
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
<nav class="top-nav">
    <?php if (isset($_SESSION['user'])): ?>
        <?php if (basename($_SERVER['SCRIPT_NAME']) === 'index.php'): ?>
            <div class="user-panel">
                <span class="wave">👋</span>
                <span><strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong></span>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['user']['email'] === 'admin@electroshop.pl'): ?>
            <a href="admin.php" class="nav-btn admin">🛠️ Panel admina</a>
        <?php endif; ?>

        <a href="logout.php" class="nav-btn logout">🚪 Wyloguj się</a>
    <?php else: ?>
        <a href="register.php" class="nav-btn gray <?= basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : '' ?>">👨‍💻 Rejestracja</a>
        <a href="login.php" class="nav-btn green <?= basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>">🔑 Zaloguj się</a>
    <?php endif; ?>

    <a href="index.php" class="nav-btn green <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">📦 Katalog</a>
    <a href="about.php" class="nav-btn blue <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">ℹ️ O nas</a>
    <a href="cart.php" class="nav-btn green <?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">🛒 Koszyk (<?= array_sum($_SESSION['cart'] ?? []); ?>)</a>
</nav>



</header>

<aside class="category-sidebar" id="categorySidebar">
    <nav class="category-nav">
        <h3>Kategorie</h3>
        <ul>
            <?php foreach ($all_categories as $cat): ?>
                <li><a href="?category=<?= urlencode($cat) ?>"><?= ucfirst($cat) ?></a></li>
            <?php endforeach; ?>
            <li><a href="index.php">📂 Wszystkie produkty</a></li>
        </ul>
        <hr class="menu-divider">
        <h3>Menu sklepu</h3>
        <ul>
            <li><a href="cart.php">🛒 Koszyk</a></li>
            <li><a href="#">📦 Śledź przesyłkę</a></li>
            <li><a href="#">📍 Lokalizacja</a></li>
            <li><a href="about.php">💬 Kontakt</a></li>
            <li><a href="#">💰 Kredyt</a></li>
            <li><a href="#">❓ Pomoc</a></li>
        </ul>
    </nav>
</aside>

<!-- Полупрозрачный фон -->
<div class="overlay" id="overlay"></div>

<main>
    
    <h2 class="page-title"><?= $page_title ?></h2>
    <section class="products">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $id => $p): ?>
                <article class="product" data-id="<?= $id ?>">
                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    <h2><?= htmlspecialchars($p['title']) ?></h2>
                    <p class="price"><?= number_format($p['price'], 2, ',', ' ') ?> zł</p>
                    <button class="add-to-cart" data-id="<?= $id ?>">Dodaj do koszyka</button>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Brak produktów do wyświetlenia.</p>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>© 2025 Electro Shop — Sklep z technologią</p>
</footer>

<!-- Модальное окно -->
<div class="product-modal" id="productModal">
    <div class="modal-header">
        <h3 id="modalTitle"></h3>
        <button class="close-btn" id="modalClose">&times;</button>
    </div>
    <div class="modal-content">
        <img id="modalImage" src="" alt="">
        <div class="modal-details">
            <p id="modalDescription"></p>
            <p class="price" id="modalPrice"></p>
            <ul id="modalFeatures"></ul>
            <div class="modal-benefits">
                <span>🚚 Darmowa dostawa</span>
                <span>🕒 Wysyłка 1–3 dni</span>
                <span>💳 Płatność przy odbiorze</span>
                <span>📞 Wsparcie 24/7</span>
            </div>
            <form method="post">
                <input type="hidden" name="product_id" id="modalProductId">
                <button type="submit">Dodaj do koszyka</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('categorySidebar');
    const toggleBtn = document.getElementById('categoryToggleBtn');
    const overlay = document.getElementById('overlay');
    const modal = document.getElementById('productModal');
    const modalClose = document.getElementById('modalClose');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        modal.classList.remove('open');
    });

    modalClose.addEventListener('click', () => modal.classList.remove('open'));

    const products = <?= json_encode($products_full, JSON_UNESCAPED_UNICODE) ?>;

    document.querySelectorAll('.product').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart')) return;
            const id = card.dataset.id;
            const prod = products[id];
            if (!prod) return;
            document.getElementById('modalTitle').textContent = prod.title;
            document.getElementById('modalImage').src = prod.image;
            document.getElementById('modalDescription').textContent = prod.description;
            document.getElementById('modalPrice').textContent = prod.price.toLocaleString('pl-PL') + ' zł';
            document.getElementById('modalProductId').value = id;
            const list = document.getElementById('modalFeatures');
            list.innerHTML = '';
            if (prod.features) prod.features.forEach(f => {
                const li = document.createElement('li');
                li.textContent = f;
                list.appendChild(li);
            });
            modal.classList.add('open');
        });
    });

    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.id;
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="product_id" value="${id}">`;
            document.body.appendChild(form);
            form.submit();
        });
    });
});
</script>
</body>
</html>
