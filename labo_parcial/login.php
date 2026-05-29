<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
redirect_if_logged();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $user = trim($_POST['user'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT user, password FROM login WHERE user = ? LIMIT 1');
    $stmt->execute([$user]);
    $account = $stmt->fetch();
    if ($account && password_verify($password, $account['password'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $account['user'];
        csrf_token();
        header('Location: index.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Atlético Nova FC</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<main class="login-card">
    <div class="logo"><img src="assets/img/logo.svg" alt="Atlético Nova FC"></div>
    <h1>Iniciar sesión</h1>
    <p class="subtitle">Acceso al panel de gestión deportiva</p>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="field"><label>Usuario</label><input class="input" name="user" autocomplete="username" required></div>
        <div class="field"><label>Contraseña</label><input class="input" type="password" name="password" autocomplete="current-password" required></div>
        <button class="btn btn-primary full" type="submit">Ingresar</button>
    </form>
</main>
</body>
</html>
