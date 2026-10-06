<?php

function mthEnsureSqliteSchema(PDO $pdo): void
{
    $version = (int)$pdo->query('PRAGMA user_version')->fetchColumn();
    if ($version >= 1) {
        return;
    }

    $schemaPath = __DIR__ . '/../database.sql';
    $schema = file_get_contents($schemaPath);
    if ($schema === false) {
        throw new RuntimeException('The database schema file could not be read.');
    }

    $schema = preg_replace('/^\s*SET\s+FOREIGN_KEY_CHECKS\s*=\s*\d+\s*;\s*$/im', '', $schema);
    $schema = preg_replace('/\bUNIQUE\s+KEY\s+[a-z0-9_]+\s*\(([^)]+)\)/i', 'UNIQUE ($1)', $schema);
    $schema = preg_replace('/,\s*INDEX\s+[a-z0-9_]+\s*\([^)]+\)/i', '', $schema);
    $schema = preg_replace('/\bINDEX\s+[a-z0-9_]+\s*\([^)]+\)\s*,?/i', '', $schema);
    $schema = preg_replace(
        '/\bid\s+(?:TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)\s+AUTO_INCREMENT\s+PRIMARY\s+KEY\b/i',
        'id INTEGER PRIMARY KEY AUTOINCREMENT',
        $schema
    );

    $conversions = [
        '/\bENUM\s*\([^)]*\)/i' => 'TEXT',
        '/\b(?:LONGTEXT|MEDIUMTEXT|TINYTEXT|JSON|VARCHAR\s*\(\s*\d+\s*\)|CHAR\s*\(\s*\d+\s*\))\b/i' => 'TEXT',
        '/\bTINYINT(?:\s*\(\s*\d+\s*\))?\b|\b(?:SMALLINT|MEDIUMINT|BIGINT|INT)\b/i' => 'INTEGER',
        '/\bDECIMAL\s*\(\s*\d+\s*,\s*\d+\s*\)|\bDOUBLE(?:\s+PRECISION)?\b|\bFLOAT\b/i' => 'REAL',
        '/\b(?:DATETIME|TIMESTAMP|DATE|TIME)\b/i' => 'TEXT',
        '/\s+ON\s+UPDATE\s+CURRENT_TIMESTAMP(?:\s*\(\s*\))?/i' => '',
        '/\)\s*ENGINE\s*=\s*[a-z0-9_]+[^;]*;/i' => ');',
    ];
    foreach ($conversions as $pattern => $replacement) {
        $schema = preg_replace($pattern, $replacement, $schema);
    }

    if (preg_match('/\bAUTO_INCREMENT\b|\bENGINE\s*=/i', $schema)) {
        throw new RuntimeException('The MySQL schema contains syntax that has no SQLite conversion.');
    }

    $pdo->beginTransaction();
    try {
        $pdo->exec($schema);
        $pdo->exec('PRAGMA user_version = 1');
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new RuntimeException('The SQLite schema could not be initialized: ' . $e->getMessage(), 0, $e);
    }
}
