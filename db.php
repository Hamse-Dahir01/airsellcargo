<?php
declare(strict_types=1);

$defaults = [
    'DB_HOST' => 'localhost',
    'DB_PORT' => '3306',
    'DB_NAME' => 'airsellcargo',
    'DB_USER' => 'airsellcargo_app',
];

foreach ($defaults as $name => $default) {
    $value = getenv($name);
    if ($value === false || $value === '') {
        if ($name === 'DB_PASSWORD') {
            throw new RuntimeException('Database password is not configured.');
        }
        putenv("$name=$default");
    }
}

$dbPassword = getenv('DB_PASSWORD');
if (!is_string($dbPassword) || $dbPassword === '') {
    throw new RuntimeException('Database password is not configured.');
}

try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            getenv('DB_HOST'),
            getenv('DB_PORT'),
            getenv('DB_NAME')
        ),
        getenv('DB_USER'),
        $dbPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed.');
}
