<?php
echo "<h2>🔍 Проверка PostgreSQL</h2>";

$conn = pg_connect("host=localhost port=5432 dbname=shop_users user=postgres password=admin123");

if ($conn) {
    echo "<p style='color:green;'>✅ Подключение к PostgreSQL успешно!</p>";

    $result = pg_query($conn, "SELECT version();");
    $row = pg_fetch_row($result);
    echo "<p>Версия PostgreSQL: " . htmlspecialchars($row[0]) . "</p>";

    pg_close($conn);
} else {
    echo "<p style='color:red;'>❌ Не удалось подключиться к базе данных.</p>";
    echo "<p>Проверь логин, пароль и настройки PostgreSQL.</p>";
}
?>
