<?php
require_once __DIR__ . '/config.php';
try {
    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if (DB_DRIVER === 'sqlite') {
        $sqlitePath = (string)(getenv('MTH_SQLITE_PATH') ?: __DIR__ . '/../var/mth.sqlite');
        if ($sqlitePath !== ':memory:') {
            $sqliteDirectory = dirname($sqlitePath);
            if (!is_dir($sqliteDirectory) && !mkdir($sqliteDirectory, 0750, true) && !is_dir($sqliteDirectory)) {
                throw new RuntimeException('The SQLite database directory could not be created.');
            }
        }

        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, $pdoOptions);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');

        require_once __DIR__ . '/sqlite-schema.php';
        mthEnsureSqliteSchema($pdo);

        require_once __DIR__ . '/catalog-seed.php';
        mthSeedSuppliedCatalog($pdo);
    } elseif (DB_DRIVER === 'mysql') {
        if (DB_HOST === '' || DB_NAME === '' || DB_USER === '' || DB_PASS === '') {
            throw new RuntimeException('MySQL is selected, but one or more MTH_DB_* connection settings are missing.');
        }

        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            $pdoOptions
        );
    } else {
        throw new RuntimeException('Unsupported database driver. Choose sqlite or mysql.');
    }
} catch (Throwable $e) {
    http_response_code(500);
    exit(APP_ENV === 'development'
        ? htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
        : 'Database connection failed.');
}
