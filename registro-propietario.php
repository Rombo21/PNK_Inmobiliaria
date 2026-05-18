<?php
$pageTitle = 'Registro Propietario — PNK Inmobiliaria';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = sanitize($_POST['rut'] ?? '');
    $nombre = sanitize($_POST['nombre_completo'] ?? '');
    $fechaNac = $_POST['fecha_nacimiento'] ?? '';
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $telefono = sanitize($_POST['telefono'] ?? '');
    $numPropiedad = sanitize($_POST['num_propiedad_bbr'] ?? '');

    // Validaciones
    $rutLimpio = str_replace(['.', ' '], '', $rut);
    if (!validarRut($rutLimpio)) {
        $error = 'El RUT ingresado no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } else {
        // Verificar duplicados
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE rut = ? OR correo = ?");
        $stmt->execute([$rutLimpio, $correo]);
        if ($stmt->fetch()) {
            $error = 'Ya existe un usuario con ese RUT o correo.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (rut, nombre_completo, fecha_nacimiento, correo, password, sexo, telefono, tipo_usuario, estado, num_propiedad_bbr) VALUES (?, ?, ?, ?, ?, ?, ?, 'propietario', 'pendiente', ?)");
            $stmt->execute([$rutLimpio, $nombre, $fechaNac, $correo, $hash, $sexo, $telefono, $numPropiedad]);
            $success = 'Tu cuenta está en revisión. Recibirás un correo cuando sea activada.';
        }
    }
}
?>

<section class="container my-5" style="max-width:700px;">
    <div class="card premium-card border-0 shadow p-4">
        <h2 class="text-center fw-bold mb-4"><i class="fas fa-house-user text-warning me-2"></i>Registro Propietario</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
        <?php else: ?>

        <form method="POST" data-validate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">RUT</label>
                    <input type="text" name="rut" class="form-control" required placeholder="12.345.678-9" value="<?= sanitize($_POST['rut'] ?? '') ?>">
                    <div class="invalid-feedback">RUT inválido</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre Completo</label>
                    <input type="text" name="nombre_completo" class="form-control" required value="<?= sanitize($_POST['nombre_completo'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" required value="<?= sanitize($_POST['correo'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Contraseña</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Confirmar Contraseña</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sexo</label>
                    <div class="d-flex gap-3 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sexo" value="M" id="sexoM" required>
                            <label class="form-check-label" for="sexoM">Masculino</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sexo" value="F" id="sexoF">
                            <label class="form-check-label" for="sexoF">Femenino</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Teléfono Móvil</label>
                    <input type="tel" name="telefono" class="form-control" placeholder="+56 9 1234 5678" value="<?= sanitize($_POST['telefono'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">N° de Propiedad según Registro de Bienes Raíces</label>
                    <input type="text" name="num_propiedad_bbr" class="form-control" placeholder="Ej: BR-45231">
                </div>
            </div>
            <button type="submit" class="btn btn-warning text-dark fw-bold w-100 py-2 mt-4">
                <i class="fas fa-user-plus me-1"></i> Registrarme como Propietario
            </button>
        </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="login.php" class="text-primary">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
