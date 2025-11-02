<?php
// ===========================================================
// ✅ Универсальное подключение PostgreSQL (Render + localhost)
// ===========================================================
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Определяем: Render или localhost
$isRender = getenv('RENDER') || getenv('DB_HOST');

// === Конфигурация Render ===
if ($isRender) {
    // 🔧 Render Database Credentials
    $host = 'dpg-d43q9ohr0fns73fdnsmg-a.frankfurt-postgres.render.com';
    $port = '5432';
    $dbname = 'shop_users';
    $user = 'shop_users_user';
    $password = 'OJpw4aSzQ7YxGROyPmjyIXVABH8NfIKS';

    // Полный URL Render (он из Connections → External Database URL)
    $renderUrl = "postgresql://$user:$password@$host:$port/$dbname";

    // Подключение
    $conn = @pg_connect($renderUrl);

    if (!$conn) {
        $error = pg_last_error();
        error_log("❌ Ошибка Render PostgreSQL: $error");
        echo "<h3 style='color:red'>❌ Ошибка подключения к Render PostgreSQL.<br>$error</h3>";
    } else {
        error_log("✅ Подключено к PostgreSQL (Render: $dbname@$host)");
    }

// === Конфигурация локального XAMPP ===
} else {
    $host = 'localhost';
    $port = '5432';
    $dbname = 'shop_users';
    $user = 'postgres';
    $password = 'admin123';

    $conn = @pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

    if (!$conn) {
        $error = pg_last_error();
        error_log("❌ Ошибка локального PostgreSQL: $error");
        echo "<h3 style='color:red'>❌ Ошибка подключения к локальной БД.<br>$error</h3>";
    } else {
        error_log("✅ Подключено к PostgreSQL (Localhost: $dbname@$host)");
    }
}

// === Проверка подключения ===
if (!$conn) {
    die("<h2 style='color:red'>⛔ Не удалось подключиться к базе данных.</h2>");
}

// === 🔄 Функция синхронизации JSON ↔ SQL ===
function syncUsersBetweenJsonAndSQL($conn, $usersFile) {
    if (!$conn) return;

    $usersJson = file_exists($usersFile)
        ? json_decode(file_get_contents($usersFile), true)
        : [];

    $result = @pg_query($conn, "SELECT username, email, password, role FROM users");
    if (!$result) {
        error_log("⚠️ Ошибка запроса при синхронизации: " . pg_last_error());
        return;
    }

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

    file_put_contents(
        $usersFile,
        json_encode($usersJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    error_log("🔄 Синхронизация JSON ↔ SQL завершена.");
}
?>
