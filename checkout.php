<?php
session_start();
$products = json_decode(file_get_contents(__DIR__ . '/products.json'), true);
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

function cart_total($products) {
    $sum = 0.0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        if (isset($products[$id])) $sum += $products[$id]['price'] * $qty;
    }
    return $sum;
}

$order_placed = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['address'])) {
    // В реальном сайте тут могла бы быть запись в БД и отправка email
    $order_placed = true;
    $_SESSION['cart'] = [];
}
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Złożenie zamówienia — Electro Shop</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f7f8fa;
      margin: 0;
      padding: 0;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 50px;
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    header h1 {
      color: #2ea76d;
      margin: 0;
    }

    nav a {
      margin-left: 20px;
      text-decoration: none;
      color: #2ea76d;
      font-weight: 500;
    }

    main {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 80vh;
      padding-top: 50px;
    }

    .checkout-container {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
      padding: 40px 50px;
      width: 650px;
      text-align: center;
    }

    h2 {
      color: #2ea76d;
      margin-bottom: 20px;
    }

    .summary {
      font-size: 18px;
      margin-bottom: 20px;
      font-weight: 500;
      color: #222;
    }

    .cart-items {
      text-align: left;
      margin-bottom: 25px;
      border-top: 1px solid #eee;
      border-bottom: 1px solid #eee;
      padding: 15px 0;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      font-size: 15px;
    }

    label {
      display: block;
      text-align: left;
      font-weight: 600;
      margin-top: 15px;
      margin-bottom: 5px;
      color: #333;
    }

    input, textarea {
      width: 100%;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 15px;
      transition: 0.2s;
    }

    input:focus, textarea:focus {
      border-color: #2ea76d;
      box-shadow: 0 0 0 3px rgba(46,167,109,0.15);
      outline: none;
    }

    button {
      background-color: #2ea76d;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      margin-top: 25px;
      transition: background 0.2s;
    }

    button:hover {
      background-color: #23935f;
    }

    .thankyou {
      text-align: center;
      padding: 80px 30px;
    }

    .thankyou h2 {
      color: #2ea76d;
      margin-bottom: 15px;
    }

    .thankyou a {
      display: inline-block;
      margin-top: 15px;
      padding: 10px 20px;
      background: #2ea76d;
      color: white;
      border-radius: 8px;
      text-decoration: none;
    }

    .thankyou a:hover {
      background: #23935f;
    }

    footer {
      text-align: center;
      padding: 30px 0;
      color: #777;
      font-size: 14px;
    }

  </style>
</head>
<body>
<header>
  <h1>Złożenie zamówienia</h1>
  <nav>
    <a href="index.php">Katalog</a>
    <a href="about.php">O nas</a>
    <a href="cart.php">Koszyk (<?= array_sum($_SESSION['cart'] ?? []); ?>)</a>
  </nav>
</header>

<main>
<?php if ($order_placed): ?>
  <div class="thankyou">
    <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" width="100" alt="Success">
    <h2>Dziękujemy! Twoje zamówienie zostało przyjęte 🎉</h2>
    <p>To jest wersja demonstracyjna. W rzeczywistości tutaj odbywałoby się przetwarzanie płatności.</p>
    <a href="index.php">Wróć do katalogu</a>
  </div>
<?php elseif (empty($_SESSION['cart'])): ?>
  <div class="thankyou">
    <h2>Twój koszyk jest pusty 🛒</h2>
    <a href="index.php">Przejdź do sklepu</a>
  </div>
<?php else: ?>
  <div class="checkout-container">
    <h2>Podsumowanie zamówienia</h2>
    <div class="summary">Do zapłaty łącznie: 
      <strong><?= number_format(cart_total($products),2,',',' ') ?> zł</strong>
    </div>

    <div class="cart-items">
      <?php foreach ($_SESSION['cart'] as $id => $qty): 
        $p = $products[$id]; ?>
        <div class="cart-item">
          <span><?= htmlspecialchars($p['title']); ?> × <?= $qty ?></span>
          <span><?= number_format($p['price']*$qty,2,',',' ') ?> zł</span>
        </div>
      <?php endforeach; ?>
    </div>

    <form method="post">
      <label for="name">Imię i nazwisko:</label>
      <input type="text" id="name" name="name" placeholder="Wpisz swoje imię i nazwisko" required>

      <label for="address">Adres dostawy:</label>
      <textarea id="address" name="address" rows="3" placeholder="Ulica, numer, miasto, kod pocztowy" required></textarea>

      <label for="email">Adres e-mail:</label>
      <input type="email" id="email" name="email" placeholder="np. jan.kowalski@gmail.com" required>

      <label for="phone">Numer telefonu:</label>
      <input type="tel" id="phone" name="phone" placeholder="+48 123 456 789" required>

      <button type="submit">Potwierdź zamówienie</button>
    </form>
  </div>
<?php endif; ?>
</main>

<footer>
  <p>Sklep ze sprzętem - Electro.</p>
</footer>
</body>
</html>
