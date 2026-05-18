<?php
$pageTitle = 'Dashboard — PNK Inmobiliaria';
require_once 'includes/header.php';
requireAdmin();

// Stats
$totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalPropiedades = $pdo->query("SELECT COUNT(*) FROM propiedades WHERE estado='activo'")->fetchColumn();
$pendientes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado='pendiente'")->fetchColumn();
$totalVisitas = $pdo->query("SELECT COUNT(*) FROM visitas WHERE estado='pendiente'")->fetchColumn();
?>

<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="fas fa-tachometer-alt text-warning me-2"></i>Dashboard Administrador</h1>
        <span class="badge bg-primary fs-6 p-2"><i class="fas fa-user-shield me-1"></i>Bienvenido: <?= sanitize(getUserName()) ?></span>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card premium-card border-0 shadow text-center p-4" style="border-left:4px solid #0d47a1 !important;">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h3 class="fw-bold"><?= $totalUsuarios ?></h3>
                <p class="text-muted mb-0">Total Usuarios</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card premium-card border-0 shadow text-center p-4" style="border-left:4px solid #28a745 !important;">
                <i class="fas fa-building fa-3x text-success mb-3"></i>
                <h3 class="fw-bold"><?= $totalPropiedades ?></h3>
                <p class="text-muted mb-0">Propiedades Activas</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card premium-card border-0 shadow text-center p-4" style="border-left:4px solid #ffc107 !important;">
                <i class="fas fa-user-clock fa-3x text-warning mb-3"></i>
                <h3 class="fw-bold"><?= $pendientes ?></h3>
                <p class="text-muted mb-0">Usuarios Pendientes</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card premium-card border-0 shadow text-center p-4" style="border-left:4px solid #dc3545 !important;">
                <i class="fas fa-calendar-check fa-3x text-danger mb-3"></i>
                <h3 class="fw-bold"><?= $totalVisitas ?></h3>
                <p class="text-muted mb-0">Visitas Pendientes</p>
            </div>
        </div>
    </div>

    <!-- Acciones principales -->
    <div class="row g-4">
        <div class="col-md-6">
            <a href="crud-usuarios.php" class="text-decoration-none">
                <div class="card premium-card border-0 shadow p-5 text-center h-100">
                    <i class="fas fa-users-cog fa-5x text-primary mb-4"></i>
                    <h3 class="fw-bold text-dark">Mantenedor de Usuarios</h3>
                    <p class="text-muted">Gestionar usuarios, activar cuentas, asignar PENKA_ID</p>
                    <?php if ($pendientes > 0): ?>
                        <span class="badge bg-warning text-dark fs-6"><?= $pendientes ?> pendientes de activación</span>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="crud-propiedades.php" class="text-decoration-none">
                <div class="card premium-card border-0 shadow p-5 text-center h-100">
                    <i class="fas fa-building fa-5x text-success mb-4"></i>
                    <h3 class="fw-bold text-dark">Mantenedor de Propiedades</h3>
                    <p class="text-muted">Crear, editar y administrar propiedades publicadas</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 mt-4">
            <a href="crud-gestiones.php" class="text-decoration-none">
                <div class="card premium-card border-0 shadow p-5 text-center h-100">
                    <i class="fas fa-handshake fa-5x text-info mb-4"></i>
                    <h3 class="fw-bold text-dark">Gestiones</h3>
                    <p class="text-muted">Asignar gestores freelance a las propiedades publicadas</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 mt-4">
            <a href="crud-visitas.php" class="text-decoration-none">
                <div class="card premium-card border-0 shadow p-5 text-center h-100">
                    <i class="fas fa-calendar-check fa-5x text-danger mb-4"></i>
                    <h3 class="fw-bold text-dark">Administrar Visitas</h3>
                    <p class="text-muted">Revisar y actualizar el estado de las solicitudes de visitas</p>
                    <?php if ($totalVisitas > 0): ?>
                        <span class="badge bg-danger text-white fs-6"><?= $totalVisitas ?> visitas pendientes</span>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
