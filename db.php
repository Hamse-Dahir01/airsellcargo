<?php
declare(strict_types=1);

$required = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER'];

foreach ($required as $name) {
    $value = getenv($name);

    if ($value === false || $value === '') {
        error_log("Missing required database environment variable: {$name}");
        http_response_code(500);
        exit('Database configuration is unavailable.');
    }
}

$dbPassword = getenv('DB_PASSWORD');
if ($dbPassword === false) {
    error_log('Missing database environment variable: DB_PASSWORD');
    http_response_code(500);
    exit('Database configuration is unavailable.');
}

$dbHost = (string) getenv('DB_HOST');
$dbPort = (string) getenv('DB_PORT');
$dbName = (string) getenv('DB_NAME');
$dbUser = (string) getenv('DB_USER');

if (!filter_var($dbPort, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 65535],
])) {
    error_log('Invalid database port configuration.');
    http_response_code(500);
    exit('Database configuration is unavailable.');
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $dbHost,
    $dbPort,
    $dbName
);

try {
    $pdo = new PDO(
        $dsn,
        $dbUser,
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
