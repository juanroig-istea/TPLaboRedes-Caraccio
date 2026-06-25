<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$totalJugadores = $pdo->query("
SELECT COUNT(*) FROM jugadores
")->fetchColumn();

$edadPromedio = $pdo->query("
SELECT ROUND(AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())),1)
FROM jugadores
")->fetchColumn();

$categorias = $pdo->query("
SELECT categoria, COUNT(*) cantidad
FROM jugadores
GROUP BY categoria
ORDER BY categoria
")->fetchAll();

$estados = $pdo->query("
SELECT estado, COUNT(*) cantidad
FROM jugadores
GROUP BY estado
")->fetchAll();

$posiciones = $pdo->query("
SELECT posicion, COUNT(*) cantidad
FROM jugadores
GROUP BY posicion
")->fetchAll();

$aptos = $pdo->query("
SELECT apto_medico, COUNT(*) cantidad
FROM jugadores
GROUP BY apto_medico
")->fetchAll();

$obras = $pdo->query("
SELECT obra_social, COUNT(*) cantidad
FROM jugadores
GROUP BY obra_social
ORDER BY cantidad DESC
")->fetchAll();
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Estadisticas | Atletico Nova FC</title>

<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header class="topbar">

    <a class="brand" href="index.php">
        <img src="assets/img/logo.svg" alt="Atletico Nova FC">
    </a>

    <nav class="top-actions">
        <span class="session-user">Panel deportivo</span>

        <a class="btn btn-ghost" href="index.php">
            Volver al panel
        </a>

        <a class="btn btn-ghost" href="logout.php">
            Cerrar sesion
        </a>
    </nav>

</header>

<main class="wrap">

<section class="hero-card">
    <div>
        <p class="eyebrow">Atletico Nova FC</p>

        <h1>Estadisticas del Plantel</h1>

        <p class="subtitle left">
            Indicadores generales de jugadores.
        </p>
    </div>
</section>


<section class="card">

<h2>Total de jugadores</h2>

<h1><?= $totalJugadores ?></h1>

<hr>

<h2>Edad promedio</h2>

<h1><?= $edadPromedio ?> anios</h1>

</section>


<section class="card">

<h2>Jugadores por categoria</h2>

<div class="table-wrap">

<table>

<thead>
<tr>
<th>Categoria</th>
<th>Cantidad</th>
</tr>
</thead>

<tbody>

<?php foreach($categorias as $c): ?>

<tr>
<td><?= htmlspecialchars($c['categoria']) ?></td>
<td><?= $c['cantidad'] ?></td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</section>


<section class="card">

<h2>Jugadores por estado</h2>

<div class="table-wrap">

<table>

<thead>
<tr>
<th>Estado</th>
<th>Cantidad</th>
</tr>
</thead>

<tbody>

<?php foreach($estados as $e): ?>

<tr>
<td><?= htmlspecialchars($e['estado']) ?></td>
<td><?= $e['cantidad'] ?></td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</section>


<section class="card">

<h2>Jugadores por posicion</h2>

<div class="table-wrap">

<table>

<thead>
<tr>
<th>Posicion</th>
<th>Cantidad</th>
</tr>
</thead>

<tbody>

<?php foreach($posiciones as $p): ?>

<tr>
<td><?= htmlspecialchars($p['posicion']) ?></td>
<td><?= $p['cantidad'] ?></td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</section>


<section class="card">

<h2>Apto medico</h2>

<div class="table-wrap">

<table>

<thead>
<tr>
<th>Estado</th>
<th>Cantidad</th>
</tr>
</thead>

<tbody>

<?php foreach($aptos as $a): ?>

<tr>
<td><?= htmlspecialchars($a['apto_medico']) ?></td>
<td><?= $a['cantidad'] ?></td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</section>


<section class="card">

<h2>Obras sociales</h2>

<div class="table-wrap">

<table>

<thead>
<tr>
<th>Obra social</th>
<th>Cantidad</th>
</tr>
</thead>

<tbody>

<?php foreach($obras as $o): ?>

<tr>
<td><?= htmlspecialchars($o['obra_social']) ?></td>
<td><?= $o['cantidad'] ?></td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</section>

</main>

</body>
</html>