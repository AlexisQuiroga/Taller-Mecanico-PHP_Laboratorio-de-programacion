<?php
require_once '../check_session.php';
require_once '../db.php';

if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

$usuarios = mysqli_query($conn, "SELECT * FROM usuarios ORDER BY created_at DESC");
?>
<?php include '../header.php'; ?>
<?php include '../sidebar.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Usuarios</h4>
        <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Nuevo Usuario</a>
    </div>
    <?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?php echo $_SESSION['tipo']; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje'], $_SESSION['tipo']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($u = mysqli_fetch_assoc($usuarios)): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($u['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <?php
                            $badge_class = ['admin'=>'danger','mecanico'=>'primary','electricista'=>'warning text-dark','cliente'=>'success'];
                            $bc = $badge_class[$u['rol']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $bc; ?>"><?php echo ucfirst($u['rol']); ?></span>
                        </td>
                        <td>
                            <a href="editar.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="eliminar.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('¿Eliminar este usuario?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../footer.php'; ?>
