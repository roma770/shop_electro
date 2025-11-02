<?php
$host = getenv('DB_HOST') ?: 'dpg-d43q9ohr0fns73fdnsmg-a'; // Хост Render
$dbname = getenv('DB_NAME') ?: 'shop_users';                // Имя базы
$user = getenv('DB_USER') ?: 'shop_users_user';             // Пользователь
$password = getenv('DB_PASSWORD') ?: 'OJpw4aSzQ7YxGROyPmjyIXVABH8NfIKS'; // 🔑 ВСТАВЬ СЮДА пароль из Render Connections
$port = getenv('DB_PORT') ?: '5432';                        // Порт PostgreSQL

// === Подключаемся к базе ===
$conn = @pg_connect("host=$host dbname=$dbname user=$user password=$password port=$port");

if (!$conn) {
    // Ошибка подключения
    error_log("❌ Ошибка подключения к PostgreSQL: " . pg_last_error());
    $conn = null; // продолжаем работу без БД
} else {
    // Для Render логируем успешное подключение (один раз в логах)
    error_log("✅ Подключено к PostgreSQL ($dbname@$host)");
}

// === 🔄 Функция синхронизации JSON ↔ SQL ===
function syncUsersBetweenJsonAndSQL($conn, $usersFile) {
    if (!$conn) return;

    // Загружаем пользователей из JSON
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
                'password' => $row['password'],
                'role' => $row['role'] ?? 'user'
            ];
        }
    }

    // Сохраняем обновлённый JSON
    file_put_contents(
        $usersFile,
        json_encode($usersJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}
?>
