<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Metodo no permitido. La eliminacion debe hacerse desde el panel.');
}

verify_csrf();
$id = $_POST['id'] ?? '';
if ($id !== '' && ctype_digit((string)$id)) {
    $stmt = $pdo->prepare('DELETE FROM jugadores WHERE numero_jugador = ?');
    $stmt->execute([(int)$id]);
    set_flash('success', 'Jugador eliminado definitivamente.');
}

header('Location: index.php');
exit;
