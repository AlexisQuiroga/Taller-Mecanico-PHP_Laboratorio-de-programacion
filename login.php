<?php
session_start();
if (isset($_SESSION['id'])) {
    header('Location: dashboard.php');
    exit();
}
require_once 'consultas.php';
$conexion = conexion();

$error = '';

if (!empty($_POST['ingresar'])) {
    $email     = trim($_POST['email']);
    $contrasena = md5($_POST['password']);
    $resultado  = login($conexion, $email, $contrasena);

    if ($resultado && mysqli_num_rows($resultado) === 1) {
        $usuario = mysqli_fetch_array($resultado);
        $_SESSION['id']       = $usuario['id'];
        $_SESSION['nombre']   = $usuario['nombre'];
        $_SESSION['apellido'] = $usuario['apellido'];
        $_SESSION['email']    = $usuario['email'];
        $_SESSION['rol']      = $usuario['rol'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Email o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller Mecánico - Iniciar Sesión</title>
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
                    <h4 class="fw-bold mt-2 mb-0">Taller Mecánico</h4>
                    <p class="text-muted small">Sistema de Gestión de Taller</p>
                </div>
                <?php if ($error){ ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php }; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required autofocus
                                value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <button type="submit" name="ingresar" value="1" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="registro.php" class="text-muted small">¿No tenés cuenta? Registrate</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>/js/bootstrap.min.js"></script>
<script src="<?= BASE_URL ?>/js/main.js"></script>
</body>
</html>
