<div id="overlay"></div>
<nav id="sidebar" class="d-flex flex-column">
    <div class="sidebar-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-wrench-adjustable-circle-fill fs-4 text-warning"></i>
            <span class="fs-5 fw-bold text-white">TallerMec</span>
        </div>
    </div>
    <div class="user-info">
        <div class="text-white-50 small">Bienvenido,</div>
        <div class="text-white fw-semibold"><?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></div>
        <div class="mt-1">
            <?php
            $rol = $_SESSION['rol'];
            $badge_class = [
                'admin' => 'bg-danger',
                'mecanico' => 'bg-primary',
                'electricista' => 'bg-warning text-dark',
                'cliente' => 'bg-success'
            ];
            $badge = $badge_class[$rol] ?? 'bg-secondary';
            ?>
            <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($rol); ?></span>
        </div>
    </div>
    <ul class="nav flex-column mt-2 flex-grow-1">
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>
        <?php if ($rol === 'admin'): ?>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/usuarios/listar.php" class="nav-link">
                <i class="bi bi-people me-2"></i>Usuarios
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/vehiculos/listar.php" class="nav-link">
                <i class="bi bi-car-front me-2"></i>Vehículos
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/ordenes/listar.php" class="nav-link">
                <i class="bi bi-clipboard-check me-2"></i>Órdenes de Trabajo
            </a>
        </li>
        <?php elseif ($rol === 'mecanico' || $rol === 'electricista'): ?>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/ordenes/listar.php" class="nav-link">
                <i class="bi bi-clipboard-check me-2"></i>Mis Órdenes
            </a>
        </li>
        <?php elseif ($rol === 'cliente'): ?>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/vehiculos/listar.php" class="nav-link">
                <i class="bi bi-car-front me-2"></i>Mis Vehículos
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/ordenes/listar.php" class="nav-link">
                <i class="bi bi-clipboard-check me-2"></i>Mis Órdenes
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/ordenes/crear.php" class="nav-link">
                <i class="bi bi-plus-circle me-2"></i>Solicitar Orden
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <div class="p-3">
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline-light w-100">
            <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
        </a>
    </div>
</nav>
<div id="page-content">
<nav class="navbar navbar-light bg-white border-bottom d-md-none px-3">
    <button class="btn btn-outline-secondary" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    <span class="fw-bold text-primary">TallerMec</span>
</nav>
