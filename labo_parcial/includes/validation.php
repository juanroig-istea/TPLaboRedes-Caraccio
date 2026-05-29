<?php
function clean_post(string $key): string {
    return trim((string)($_POST[$key] ?? ''));
}

function categorias_validas(): array {
    return ['Primera', 'Reserva', 'Sub 20', 'Sub 17', 'Sub 15', 'Infantiles'];
}

function posiciones_validas(): array {
    return ['Arquero', 'Defensor', 'Mediocampista', 'Delantero'];
}

function estados_validos(): array {
    return ['Activo', 'Lesionado', 'Suspendido', 'Baja'];
}

function aptos_validos(): array {
    return ['Vigente', 'Pendiente', 'Vencido'];
}

function validate_jugador_input(): array {
    $data = [
        'numero_jugador' => clean_post('numero_jugador'),
        'nombre' => clean_post('nombre'),
        'apellido' => clean_post('apellido'),
        'dni' => clean_post('dni'),
        'telefono' => clean_post('telefono'),
        'categoria' => clean_post('categoria'),
        'posicion' => clean_post('posicion'),
        'fecha_nacimiento' => clean_post('fecha_nacimiento'),
        'obra_social' => clean_post('obra_social'),
        'apto_medico' => clean_post('apto_medico'),
        'estado' => clean_post('estado'),
    ];

    $errors = [];
    if ($data['numero_jugador'] === '' || !ctype_digit($data['numero_jugador']) || (int)$data['numero_jugador'] <= 0) {
        $errors[] = 'El N° Jugador debe ser un numero entero positivo.';
    }

    foreach (['nombre' => 'Nombre', 'apellido' => 'Apellido', 'dni' => 'DNI', 'telefono' => 'Telefono', 'obra_social' => 'Obra social'] as $field => $label) {
        if ($data[$field] === '') {
            $errors[] = "$label es obligatorio.";
        }
    }

    if (!in_array($data['categoria'], categorias_validas(), true)) {
        $errors[] = 'La categoria seleccionada no es valida.';
    }
    if (!in_array($data['posicion'], posiciones_validas(), true)) {
        $errors[] = 'La posicion seleccionada no es valida.';
    }
    if (!in_array($data['apto_medico'], aptos_validos(), true)) {
        $errors[] = 'El apto medico seleccionado no es valido.';
    }
    if (!in_array($data['estado'], estados_validos(), true)) {
        $errors[] = 'El estado seleccionado no es valido.';
    }

    $date = DateTime::createFromFormat('Y-m-d', $data['fecha_nacimiento']);
    $dateErrors = DateTime::getLastErrors();
    if (!$date || $dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0 || $date->format('Y-m-d') !== $data['fecha_nacimiento']) {
        $errors[] = 'La fecha de nacimiento no es valida.';
    }

    return [$data, $errors];
}
