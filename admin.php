<?php
session_start();

$products_file = __DIR__ . '/products.json';
$products = json_decode(file_get_contents($products_file), true);
$admin_password = 'roman67733';

// === Вход ===
if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['is_admin'] = true;
    } else {
        $error = "❌ Nieprawidłowe hasło!";
    }
}

// === Выход ===
if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header("Location: admin.php");
    exit;
}

// === Удаление ===
if (isset($_GET['delete']) && isset($_SESSION['is_admin'])) {
    unset($products[$_GET['delete']]);
    file_put_contents($products_file, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin.php");
    exit;
}

// === Добавление / Редактирование ===
if (isset($_POST['save']) && isset($_SESSION['is_admin'])) {
    $id = $_POST['id'];
    if ($id === '') {
        // Новый товар
        $id = count($products) > 0 ? max(array_keys($products)) + 1 : 1;
    }

    $products[$id] = [
        'title' => $_POST['title'],
        'price' => (float)$_POST['price'],
        'image' => $_POST['image'],
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'features' => array_filter(explode("\n", trim($_POST['features'])))
    ];
    file_put_contents($products_file, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin.php");
    exit;
}

// === Получение товара для редактирования ===
$edit_product = null;
if (isset($_GET['edit']) && isset($products[$_GET['edit']])) {
    $edit_product = $products[$_GET['edit']];
}
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panel Administratora — Electro Shop</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<header>
    <h1>⚙️ Panel Administratora</h1>
    <nav>
        <a href="index.php">🏠 Powrót do sklepu</a>
        <?php if (isset($_SESSION['is_admin'])): ?>
            <a href="?logout=1" class="logout">🚪 Wyloguj</a>
        <?php endif; ?>
    </nav>
</header>

<main>
<?php if (!isset($_SESSION['is_admin'])): ?>
    <div class="login-box">
        <h2>🔒 Zaloguj się do panelu administracyjnego</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="password" name="password" placeholder="Wprowadź hasło administratora..." required>
            <button type="submit">Login</button>
        </form>
    </div>
<?php else: ?>
    <section class="admin-panel">
        <h2><?= $edit_product ? "Edycja produktu ✏️" : "Dodaj nowy produkt ➕"?></h2>
        <form method="post" class="product-form">
            <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['edit'] ?? '') ?>">
            <input type="text" name="title" placeholder="Nazwa produktu" required value="<?= htmlspecialchars($edit_product['title'] ?? '') ?>">
            <input type="number" step="0.01" name="price" placeholder="Cena (zł)" required value="<?= htmlspecialchars($edit_product['price'] ?? '') ?>">
            <input type="text" name="image" placeholder="URL zdięcia" value="<?= htmlspecialchars($edit_product['image'] ?? '') ?>">
            <input type="text" name="category" placeholder="Kategoria" value="<?= htmlspecialchars($edit_product['category'] ?? '') ?>">
            <textarea name="description" placeholder="Opis"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
            <textarea name="features" placeholder="Funkcje (jedna na wiersz)"><?= isset($edit_product['features']) ? implode("\n", $edit_product['features']) : '' ?></textarea>
            <button type="submit" name="save">💾 Zapisz</button>
            <?php if ($edit_product): ?>
                <a href="admin.php" class="cancel">❌ Anulowanie</a>
            <?php endif; ?>
        </form>

        <h2>📦 Wszystkie produkty</h2>
        <div class="product-grid">
            <?php foreach ($products as $id => $p): ?>
                <div class="admin-product">
                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="">
                    <div class="info">
                        <h3><?= htmlspecialchars($p['title']) ?></h3>
                        <p class="price"><?= number_format($p['price'], 2, ',', ' ') ?> zł</p>
                        <div class="buttons">
                            <a href="?edit=<?= $id ?>" class="edit">✏️</a>
                            <a href="?delete=<?= $id ?>" onclick="return confirm('Usunąć produkt?')" class="delete">🗑️</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
</main>

<footer>
    <p>© 2025 Electro Shop</p>
</footer>
</body>
</html>
