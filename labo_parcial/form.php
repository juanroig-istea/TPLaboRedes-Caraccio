<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> | Atlético Nova FC</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php"><img src="assets/img/logo.svg" alt="Atlético Nova FC"></a>
    <a class="btn btn-ghost" href="logout.php">Cerrar sesión</a>
</header>
<main class="wrap">
    <section class="card form-card">
        <div class="card-head">
            <div>
                <p class="eyebrow">Plantel</p>
                <h1><?= htmlspecialchars($title) ?></h1>
            </div>
            <a class="btn btn-ghost" href="index.php">Volver</a>
        </div>
        <?php if (!empty($errors)): ?>
            <div class="alert"><strong>Revisá estos datos:</strong><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div class="field"><label>N° Jugador</label><input class="input" name="numero_jugador" value="<?= htmlspecialchars($j['numero_jugador']) ?>" inputmode="numeric" pattern="[0-9]+" required></div>
            <div class="field"><label>Nombre</label><input class="input" name="nombre" value="<?= htmlspecialchars($j['nombre']) ?>" maxlength="100" required></div>
            <div class="field"><label>Apellido</label><input class="input" name="apellido" value="<?= htmlspecialchars($j['apellido']) ?>" maxlength="100" required></div>
            <div class="field"><label>DNI</label><input class="input" name="dni" value="<?= htmlspecialchars($j['dni']) ?>" maxlength="30" required></div>
            <div class="field"><label>Teléfono</label><input class="input" name="telefono" value="<?= htmlspecialchars($j['telefono']) ?>" maxlength="40" required></div>
            <div class="field"><label>Categoría</label><select class="input" name="categoria" required><?php foreach (categorias_validas() as $op): ?><option value="<?= htmlspecialchars($op) ?>" <?= $j['categoria']===$op?'selected':'' ?>><?= htmlspecialchars($op) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Posición</label><select class="input" name="posicion" required><?php foreach (posiciones_validas() as $op): ?><option value="<?= htmlspecialchars($op) ?>" <?= $j['posicion']===$op?'selected':'' ?>><?= htmlspecialchars($op) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Fecha de nacimiento</label><input class="input" type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($j['fecha_nacimiento']) ?>" required></div>
            <div class="field"><label>Obra social</label><input class="input" name="obra_social" value="<?= htmlspecialchars($j['obra_social']) ?>" maxlength="120" required></div>
            <div class="field"><label>Apto médico</label><select class="input" name="apto_medico" required><?php foreach (aptos_validos() as $op): ?><option value="<?= htmlspecialchars($op) ?>" <?= $j['apto_medico']===$op?'selected':'' ?>><?= htmlspecialchars($op) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Estado</label><select class="input" name="estado" required><?php foreach (estados_validos() as $op): ?><option value="<?= htmlspecialchars($op) ?>" <?= $j['estado']===$op?'selected':'' ?>><?= htmlspecialchars($op) ?></option><?php endforeach; ?></select></div>
            <div class="form-actions"><a class="btn btn-ghost" href="index.php">Cancelar</a><button class="btn btn-primary" type="submit">Guardar</button></div>
        </form>
    </section>
</main>
</body>
</html>
