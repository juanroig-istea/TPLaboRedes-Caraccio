<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = "%$q%";
    $stmt = $pdo->prepare('SELECT * FROM jugadores WHERE nombre LIKE ? OR apellido LIKE ? OR dni LIKE ? ORDER BY numero_jugador ASC');
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query('SELECT * FROM jugadores ORDER BY numero_jugador ASC');
}
$jugadores = $stmt->fetchAll();
$flash = get_flash();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Jugadores | Atlético Nova FC</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php"><img src="assets/img/logo.svg" alt="Atlético Nova FC"></a>
    <nav class="top-actions">
        <span class="session-user">Panel deportivo</span>
        <a class="btn btn-ghost" href="logout.php">Cerrar sesión</a>
    </nav>
</header>
<main class="wrap">
    <section class="hero-card">
        <div>
            <p class="eyebrow">Atlético Nova FC</p>
            <h1>Gestión de Jugadores</h1>
            <p class="subtitle left">Administrá plantel, categorías, posiciones, aptos médicos y estados deportivos.</p>
        </div>
        <div class="row-actions">
    <a class="btn btn-primary" href="crear.php">+ Nuevo Jugador</a>
    <a class="btn btn-primary" href="estadisticas.php">Estadisticas</a>
    <a class="btn btn-primary" href="index.php">Volver al panel</a>
</div>
    </section>

    <section class="card">
        <div class="card-head">
            <form method="get" class="search-form">
                <input class="input search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre, apellido o DNI">
                <button class="btn btn-ghost" type="submit">Buscar</button>
                <?php if ($q !== ''): ?><a class="btn btn-ghost" href="index.php">Limpiar</a><?php endif; ?>
            </form>
        </div>
        <?php if ($flash): ?><div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>N° Jugador</th><th>Nombre</th><th>Apellido</th><th>DNI</th><th>Teléfono</th><th>Categoría</th><th>Posición</th><th>Fecha nac.</th><th>Obra social</th><th>Apto médico</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($jugadores as $j): ?>
                    <tr>
                        <td><?= htmlspecialchars($j['numero_jugador']) ?></td>
                        <td><?= htmlspecialchars($j['nombre']) ?></td>
                        <td><?= htmlspecialchars($j['apellido']) ?></td>
                        <td><?= htmlspecialchars($j['dni']) ?></td>
                        <td><?= htmlspecialchars($j['telefono']) ?></td>
                        <td><span class="pill neutral"><?= htmlspecialchars($j['categoria']) ?></span></td>
                        <td><?= htmlspecialchars($j['posicion']) ?></td>
                        <td><?= htmlspecialchars($j['fecha_nacimiento']) ?></td>
                        <td><?= htmlspecialchars($j['obra_social']) ?></td>
                        <td><span class="badge <?= htmlspecialchars(css_class($j['apto_medico'])) ?>"><?= htmlspecialchars($j['apto_medico']) ?></span></td>
                        <td><span class="badge <?= htmlspecialchars(css_class($j['estado'])) ?>"><?= htmlspecialchars($j['estado']) ?></span></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn btn-warn" href="editar.php?id=<?= urlencode($j['numero_jugador']) ?>">Editar</a>
                                <form method="post" action="eliminar.php" onsubmit="return confirm('¿Eliminar este jugador definitivamente?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($j['numero_jugador']) ?>">
                                    <button class="btn btn-danger" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!$jugadores): ?><div class="empty">No se encontraron jugadores.</div><?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
