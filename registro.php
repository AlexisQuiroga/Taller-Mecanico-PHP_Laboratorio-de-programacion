<?php
session_start();
if (isset($_SESSION['id'])) {
    header('Location: dashboard.php');
    exit();
}
require_once 'consultas.php';
$conexion = conexion();

$error = '';

if (!empty($_POST['aceptar'])) {
    $nombre    = trim($_POST['nombre']);
    $apellido  = trim($_POST['apellido']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirmar = $_POST['confirmar'];

    if (!$nombre || !$apellido || !$email || !$password || !$confirmar) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($password !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $verificar = verificarEmail($conexion, $email);
        if (mysqli_num_rows($verificar) > 0) {
            $error = 'El email ya está registrado.';
        } else {
            $contrasena_hash = md5($password);
            if (insertarUsuario($conexion, $nombre, $apellido, $email, $contrasena_hash, 'cliente')) {
                header('Location: login.php');
                exit();
            } else {
                $error = 'Error al registrar el usuario.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TallerMec - Registro</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>body { background-color: #f0f2f5; }</style>
</head>
<body>
<div class="min-vh-100 d-flex align-items-center justify-content-center">
    <div class="col-11 col-sm-8 col-md-5 col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-wrench-adjustable-circle-fill text-primary" style="font-size: 3rem;"></i>
                    <h4 class="fw-bold mt-2 mb-0">TallerMec</h4>
                    <p class="text-muted small">Crear cuenta de cliente</p>
                </div>
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo  ($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php }; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required
                            value="<?php echo isset($_POST['nombre']) ?  ($_POST['nombre']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" required
                            value="<?php echo isset($_POST['apellido']) ?  ($_POST['apellido']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required
                            value="<?php echo isset($_POST['email']) ?  ($_POST['email']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-4">
                        <label for="confirmar" class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="confirmar" name="confirmar" required>
                    </div>
                    <button type="submit" name="aceptar" value="1" class="btn btn-primary w-100">
                        <i class="bi bi-person-check me-2"></i>Registrarse
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="login.php" class="text-muted small">¿Ya tenés cuenta? Iniciá sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>/js/bootstrap.min.js"></script>
<script src="<?= BASE_URL ?>/js/main.js"></script>
</body>
</html>
