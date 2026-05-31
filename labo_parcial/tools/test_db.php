<?php
// Script de prueba de conexión a la base de datos
// Uso: php tools/test_db.php

require __DIR__ . '/../config/db.php';

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
    echo "OK\n";
    exit(0);
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
