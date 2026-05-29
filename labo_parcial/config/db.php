<?php
// Configuracion de conexion MySQL
// Edita estos valores con los datos de tu servidor/phpMyAdmin.
$DB_HOST = 'localhost';
$DB_NAME = 'tu_base_de_datos';
$DB_USER = 'tu_usuario';
$DB_PASS = 'tu_password';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die('Error de conexion a la base de datos. Revisa config/db.php');
}
