<?php
require_once 'validaciones.php';
require_once 'consultas.php';
$conexion = conexion();

if ($_SESSION['rol'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$id = (int)$_GET['id'];
$usuario = obtenerUsuario($conexion, $id);

if (!$usuario) {
    header('Location: tablausuarios.php');
    exit();
}

$error = '';

if (!empty($_POST['modificar'])) {
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email    = trim($_POST['email']);
    $rol      = $_POST['rol'];

    if (!$nombre || !$apellido || !$email || !$rol) {
        $error = 'Nombre, apellido, email y rol son obligatorios.';
    } else {
        $verificar = verificarEmail($conexion, $email, $id);
        if (mysqli_num_rows($verificar) > 0) {
            $error = 'El email ya está en uso por otro usuario.';
        } else {
            $nueva_contrasena = !empty($_POST['password']) ? md5($_POST['password']) : '';
            if (actualizarUsuario($conexion, $id, $nombre, $apellido, $email, $rol, $nueva_contrasena)) {
                $_SESSION['mensaje'] = 'Usuario actualizado correctamente.';
                $_SESSION['tipo'] = 'success';
                header('Location: tablausuarios.php');
                exit();
            } else {
                $error = 'Error al actualizar el usuario.';
            }
        }
    }
}
?>
<?php include 'header.php'; ?>
<?php include 'menulateral.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Editar Usuario</h4>
    </div>
    <?php if ($error){ ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php }; ?>
    <div class="card border-0 shadow-sm" style="max-width:600px;">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required
                            value="<?php echo isset($_POST['nombre']) ? $_POST['nombre'] : $usuario['nombre']; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required
                            value="<?php echo isset($_POST['apellido']) ? $_POST['apellido'] : $usuario['apellido']; ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?php echo isset($_POST['email']) ? $_POST['email'] : $usuario['email']; ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nueva contraseña <small class="text-muted">(dejar vacío para no cambiar)</small></label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select" required>
                            <?php
                            $roles = ['admin','mecanico','electricista','cliente'];
                            if (isset($_POST['rol'])) {
                                $rol_actual = $_POST['rol'];
                            } else {
                                $rol_actual = $usuario['rol'];
                            }
                            foreach ($roles as $r) {
                            ?>
                            <option value="<?php echo $r; ?>" <?php echo $rol_actual === $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" name="modificar" value="1" class="btn btn-primary">
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
