<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/validation.php';
require_login();

$j = [
    'numero_jugador'=>'', 'nombre'=>'', 'apellido'=>'', 'dni'=>'', 'telefono'=>'',
    'categoria'=>'Primera', 'posicion'=>'Mediocampista', 'fecha_nacimiento'=>'',
    'obra_social'=>'', 'apto_medico'=>'Pendiente', 'estado'=>'Activo'
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$j, $errors] = validate_jugador_input();

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO jugadores (numero_jugador, nombre, apellido, dni, telefono, categoria, posicion, fecha_nacimiento, obra_social, apto_medico, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([(int)$j['numero_jugador'], $j['nombre'], $j['apellido'], $j['dni'], $j['telefono'], $j['categoria'], $j['posicion'], $j['fecha_nacimiento'], $j['obra_social'], $j['apto_medico'], $j['estado']]);
            set_flash('success', 'Jugador creado correctamente.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Ya existe un jugador con ese N° Jugador. Elegi otro numero.';
            } else {
                $errors[] = 'No se pudo crear el jugador. Revisa la base de datos e intentalo nuevamente.';
            }
        }
    }
}

$title = 'Nuevo Jugador';
include __DIR__ . '/form.php';
