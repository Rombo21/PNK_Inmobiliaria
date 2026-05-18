<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/db.php';
}
require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? 'PNK Inmobiliaria';
$pageDescription = $pageDescription ?? 'Plataforma inmobiliaria líder en la Región de Coquimbo, Chile. Compra, vende y gestiona propiedades.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= sanitize($pageDescription) ?>">
    <meta name="keywords" content="inmobiliaria, casas, departamentos, terrenos, La Serena, Coquimbo, Ovalle, Región de Coquimbo">
    <meta name="author" content="PNK Inmobiliaria">
    <title><?= sanitize($pageTitle) ?></title>
    <link rel="icon" href="img/iconoPNK.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navbar Principal -->
    <nav class="navbar-pnk" id="navbar-main">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <img src="img/LogoPNK2.png" alt="PNK Inmobiliaria">
            </a>
            
            <button class="nav-toggle" id="navToggle" aria-label="Menú">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="nav-links" id="navLinks">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Inicio</a>
                <a href="index.php#buscador" class="nav-link"><i class="fas fa-search"></i> Buscar</a>
                
                <?php if (isLoggedIn()): ?>
                    <!-- Usuario logueado -->
                    <div class="nav-dropdown">
                        <button class="nav-link nav-dropdown-toggle">
                            <i class="fas fa-user-circle"></i> <?= sanitize(getUserName()) ?>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <?php if (getUserType() === 'administrador'): ?>
                                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                                <a href="crud-usuarios.php"><i class="fas fa-users-cog"></i> Usuarios</a>
                                <a href="crud-propiedades.php"><i class="fas fa-building"></i> Propiedades</a>
                                <a href="crud-gestiones.php"><i class="fas fa-handshake"></i> Gestiones</a>
                                <a href="crud-visitas.php"><i class="fas fa-calendar-check"></i> Visitas</a>
                            <?php elseif (getUserType() === 'propietario'): ?>
                                <a href="crud-propiedades.php"><i class="fas fa-building"></i> Mis Propiedades</a>
                                <a href="crud-visitas.php"><i class="fas fa-calendar-check"></i> Visitas</a>
                            <?php elseif (getUserType() === 'gestor'): ?>
                                <a href="crud-propiedades.php"><i class="fas fa-building"></i> Mis Propiedades Asignadas</a>
                                <a href="crud-visitas.php"><i class="fas fa-calendar-check"></i> Visitas a Gestionar</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Usuario no logueado -->
                    <div class="nav-dropdown">
                        <button class="nav-link nav-dropdown-toggle">
                            <i class="fas fa-user-plus"></i> Regístrate
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="registro-propietario.php"><i class="fas fa-house-user"></i> Propietario</a>
                            <a href="registro-gestor.php"><i class="fas fa-user-tie"></i> Gestor Freelance</a>
                        </div>
                    </div>
                    <a href="login.php" class="nav-link nav-btn-login"><i class="fas fa-sign-in-alt"></i> Ingresar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
