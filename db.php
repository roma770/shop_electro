<?php
$host = "localhost";
$dbname = "shop_users";
$user = "postgres";
$password = "admin123"; // 🔹 замени на свой пароль

$conn = @pg_connect("host=$host dbname=$dbname user=$user password=$password");

if (!$conn) {
    error_log("⚠️ Brak połączenia z PostgreSQL: " . pg_last_error());
    $conn = null; // продолжаем работу без БД
}

// === 🔄 Функция синхронизации JSON ↔ SQL ===
function syncUsersBetweenJsonAndSQL($conn, $usersFile) {
    if (!$conn) return;

    $usersJson = file_exists($usersFile)
        ? json_decode(file_get_contents($usersFile), true)
        : [];

    // Загружаем пользователей из PostgreSQL
    $result = @pg_query($conn, "SELECT username, email, password, role FROM users");
    if (!$result) return;

    while ($row = pg_fetch_assoc($result)) {
        $email = $row['email'];
        if (!isset($usersJson[$email])) {
            $usersJson[$email] = [
                'name' => $row['username'],
                'email' => $row['email'],
                'password' => $row['password']
            ];
        }
    }

    // Сохраняем JSON обратно
    file_put_contents(
        $usersFile,
        json_encode($usersJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}
?>
