<?php
require_once '../check_session.php';
require_once '../db.php';

if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

$id = (int)$_GET['id'];
$usuario = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM usuarios WHERE id = $id"));

if (!$usuario) {
    header('Location: listar.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conn, trim($_POST['nombre']));
    $apellido = mysqli_real_escape_string($conn, trim($_POST['apellido']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $rol = mysqli_real_escape_string($conn, $_POST['rol']);

    if (!$nombre || !$apellido || !$email || !$rol) {
        $error = 'Nombre, apellido, email y rol son obligatorios.';
    } else {
        $check = mysqli_query($conn, "SELECT id FROM usuarios WHERE email = '$email' AND id != $id");
        if (mysqli_num_rows($check) > 0) {
            $error = 'El email ya está en uso por otro usuario.';
        } else {
            $pass_sql = '';
            if (!empty($_POST['password'])) {
                $nueva = md5($_POST['password']);
                $pass_sql = ", password = '$nueva'";
            }
            $sql = "UPDATE usuarios SET nombre='$nombre', apellido='$apellido', email='$email', rol='$rol' $pass_sql WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['mensaje'] = 'Usuario actualizado correctamente.';
                $_SESSION['tipo'] = 'success';
                header('Location: listar.php');
                exit();
            } else {
                $error = 'Error al actualizar el usuario.';
            }
        }
    }
}
?>
<?php include '../header.php'; ?>
<?php include '../sidebar.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Editar Usuario</h4>
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
                        <input type="text" name="nombre" class="form-control" required
                            value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD']==='POST' ? $_POST['nombre'] : $usuario['nombre']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required
                            value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD']==='POST' ? $_POST['apellido'] : $usuario['apellido']); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD']==='POST' ? $_POST['email'] : $usuario['email']); ?>">
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
                            $rol_actual = $_SERVER['REQUEST_METHOD']==='POST' ? $_POST['rol'] : $usuario['rol'];
                            foreach ($roles as $r):
                            ?>
                            <option value="<?php echo $r; ?>" <?php echo $rol_actual === $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Confirmar
                        </button>
                        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../footer.php'; ?>
