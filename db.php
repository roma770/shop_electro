<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');


$isRender = getenv('RENDER') || getenv('DB_HOST');

try {

    if ($isRender) {
        
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');

    } else {
       
        $host = 'localhost';
        $port = '3306';
        $dbname = 'users_db';   
        $user = 'root';        
        $password = '';        
    }

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    error_log("✅ Подключение к MySQL успешно: $dbname@$host");

} catch (PDOException $e) {
    error_log("❌ Ошибка подключения к БД: " . $e->getMessage());
    die("<h2 style='color:red'>❌ Ошибка подключения к базе данных</h2>");
}


function syncUsersBetweenJsonAndSQL(PDO $conn, string $usersFile): void
{
    if (!$conn) return;

    $usersJson = file_exists($usersFile)
        ? json_decode(file_get_contents($usersFile), true)
        : [];

    if (!is_array($usersJson)) {
        $usersJson = [];
    }

    try {
        $stmt = $conn->query(
            "SELECT username, email, password, role FROM users"
        );

        while ($row = $stmt->fetch()) {
            $email = $row['email'];

            if (!isset($usersJson[$email])) {
                $usersJson[$email] = [
                    'name'     => $row['username'],
                    'email'    => $row['email'],
                    'password' => $row['password'],
                    'role'     => $row['role'] ?? 'user'
                ];
            }
        }

        file_put_contents(
            $usersFile,
            json_encode($usersJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        error_log("🔄 Синхронизация MySQL → JSON завершена");

    } catch (PDOException $e) {
        error_log("⚠️ Ошибка синхронизации: " . $e->getMessage());
    }
}

?>
