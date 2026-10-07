<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/theme.php';

/*
|--------------------------------------------------------------------------
| UniRide (uniride2) MySQL connection
|--------------------------------------------------------------------------
| Credentials come from environment variables so the SAME code runs on:
|   - Local XAMPP  -> uses the defaults below (host 127.0.0.1, user root, no pass)
|   - A cloud host -> set DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS in the
|                     host's dashboard (Railway, Render, etc.)
|
| A local ".env" file (KEY=VALUE per line) is also loaded if present, so you can
| override locally without editing this file. Never commit .env (see .gitignore).
*/

// --- Load a local .env if present (tiny parser, no dependency) ---------------
$envFile = dirname(__DIR__) . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim(trim($val), "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
        }
    }
}

$env = static function (string $key, string $default): string {
    $val = getenv($key);
    return ($val !== false && $val !== '') ? $val : $default;
};

$dbHost  = $env('DB_HOST', '127.0.0.1');
$dbPort  = $env('DB_PORT', '3306');
$dbName  = $env('DB_NAME', 'uniride2');
$dbUser  = $env('DB_USER', 'root');
$dbPass  = $env('DB_PASS', '');
$charset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    error_log('[UniRide DB] ' . $e->getMessage());

    if (!empty($UNI_DB_OPTIONAL)) {
        $pdo = null;
        return;
    }

    http_response_code(500);
    uniride_render_error_page(
        'Database connection failed. On a cloud host, check the DB_HOST, DB_PORT, ' .
        'DB_NAME, DB_USER and DB_PASS environment variables. On XAMPP, start MySQL ' .
        'and confirm phpMyAdmin contains the database "uniride2".'
    );
}
