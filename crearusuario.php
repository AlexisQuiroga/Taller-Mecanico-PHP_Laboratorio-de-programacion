<?php
require_once 'validaciones.php';
require_once 'consultas.php';
$conexion = conexion();

if ($_SESSION['rol'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email    = trim($_POST['email']);
    $password = md5($_POST['password']);
    $rol      = $_POST['rol'];

    if (!$nombre || !$apellido || !$email || !$_POST['password'] || !$rol) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $verificar = verificarEmail($conexion, $email);
        if (mysqli_num_rows($verificar) > 0) {
            $error = 'El email ya está registrado.';
        } else {
            if (insertarUsuario($conexion, $nombre, $apellido, $email, $password, $rol)) {
                $_SESSION['mensaje'] = 'Usuario creado correctamente.';
                $_SESSION['tipo'] = 'success';
                header('Location: tablausuarios.php');
                exit();
            } else {
                $error = 'Error al crear el usuario.';
            }
        }
    }
}
?>
<?php include 'header.php'; ?>
<?php include 'menulateral.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Nuevo Usuario</h4>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm" style="max-width:600px;">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select" required>
                            <option value="">Seleccionar rol...</option>
                            <option value="admin" <?php echo (isset($_POST['rol']) && $_POST['rol']==='admin') ? 'selected':''; ?>>Admin</option>
                            <option value="mecanico" <?php echo (isset($_POST['rol']) && $_POST['rol']==='mecanico') ? 'selected':''; ?>>Mecánico</option>
                            <option value="electricista" <?php echo (isset($_POST['rol']) && $_POST['rol']==='electricista') ? 'selected':''; ?>>Electricista</option>
                            <option value="cliente" <?php echo (isset($_POST['rol']) && $_POST['rol']==='cliente') ? 'selected':''; ?>>Cliente</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Confirmar
                        </button>
                        <a href="tablausuarios.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
