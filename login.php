<?php
$pageTitle = 'Iniciar Sesión — PNK Inmobiliaria';
require_once 'includes/header.php';

$error = '';
$success = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($correo) || empty($password)) {
            $error = 'Por favor complete todos los campos.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ? LIMIT 1");
            $stmt->execute([$correo]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['estado'] !== 'activo') {
                    $error = 'Su cuenta aún no ha sido activada. Por favor espere la confirmación del administrador.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nombre_completo'] = $user['nombre_completo'];
                    $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
                    $_SESSION['correo'] = $user['correo'];

                    switch ($user['tipo_usuario']) {
                        case 'administrador':
                            header('Location: dashboard.php');
                            break;
                        case 'propietario':
                            header('Location: crud-propiedades.php');
                            break;
                        case 'gestor':
                            header('Location: crud-propiedades.php');
                            break;
                    }
                    exit;
                }
            } else {
                $error = 'Correo o contraseña incorrectos.';
            }
        }
    } elseif ($_POST['action'] === 'recuperar') {
        $correo = filter_input(INPUT_POST, 'correo_recuperar', FILTER_SANITIZE_EMAIL);
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        // Siempre mostrar éxito para no revelar si el correo existe
        $success = 'Si el correo está registrado, recibirás un enlace de recuperación. (Simulado)';
    }
}
?>

<section class="container my-5" style="max-width:500px;">
    <!-- Login -->
    <div class="card premium-card border-0 shadow p-4 mb-4">
        <h2 class="text-center fw-bold mb-4"><i class="fas fa-sign-in-alt text-warning me-2"></i>Iniciar Sesión</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= sanitize($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= sanitize($success) ?></div>
        <?php endif; ?>

        <form method="POST" data-validate>
            <input type="hidden" name="action" value="login">
            <div class="mb-3">
                <label class="form-label fw-bold">Correo Electrónico</label>
                <input type="email" name="correo" class="form-control" required placeholder="correo@ejemplo.cl">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-warning text-dark fw-bold w-100 py-2">
                <i class="fas fa-sign-in-alt me-1"></i> Ingresar
            </button>
        </form>
        <div class="text-center mt-3">
            <a href="#recuperar" class="text-primary" data-bs-toggle="collapse" data-bs-target="#formRecuperar">
                <i class="fas fa-key me-1"></i>¿Olvidaste tu contraseña?
            </a>
        </div>
    </div>

    <!-- Recuperar Contraseña -->
    <div class="collapse" id="formRecuperar">
        <div class="card premium-card border-0 shadow p-4">
            <h3 class="text-center fw-bold mb-3"><i class="fas fa-key text-warning me-2"></i>Recuperar Contraseña</h3>
            <form method="POST">
                <input type="hidden" name="action" value="recuperar">
                <div class="mb-3">
                    <label class="form-label fw-bold">Correo Electrónico</label>
                    <input type="email" name="correo_recuperar" class="form-control" required placeholder="correo@ejemplo.cl">
                </div>
                <button type="submit" class="btn btn-outline-primary fw-bold w-100">
                    <i class="fas fa-paper-plane me-1"></i> Enviar enlace de recuperación
                </button>
            </form>
        </div>
    </div>

    <!-- Links registro -->
    <div class="text-center mt-4">
        <p class="text-muted">¿No tienes cuenta?</p>
        <a href="registro-propietario.php" class="btn btn-outline-primary me-2"><i class="fas fa-house-user me-1"></i>Registrar Propietario</a>
        <a href="registro-gestor.php" class="btn btn-outline-success"><i class="fas fa-user-tie me-1"></i>Registrar Gestor</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
