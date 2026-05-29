<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/validation.php';
require_login();

$id = $_GET['id'] ?? '';
if ($id === '' || !ctype_digit((string)$id)) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM jugadores WHERE numero_jugador = ?');
$stmt->execute([(int)$id]);
$j = $stmt->fetch();
if (!$j) {
    header('Location: index.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$j, $errors] = validate_jugador_input();

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('UPDATE jugadores SET numero_jugador=?, nombre=?, apellido=?, dni=?, telefono=?, categoria=?, posicion=?, fecha_nacimiento=?, obra_social=?, apto_medico=?, estado=? WHERE numero_jugador=?');
            $stmt->execute([(int)$j['numero_jugador'], $j['nombre'], $j['apellido'], $j['dni'], $j['telefono'], $j['categoria'], $j['posicion'], $j['fecha_nacimiento'], $j['obra_social'], $j['apto_medico'], $j['estado'], (int)$id]);
            set_flash('success', 'Jugador actualizado correctamente.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Ya existe un jugador con ese N° Jugador. Elegi otro numero.';
            } else {
                $errors[] = 'No se pudo actualizar el jugador. Revisa la base de datos e intentalo nuevamente.';
            }
        }
    }
}

$title = 'Editar Jugador';
include __DIR__ . '/form.php';
