<?php
$pageTitle = 'Registro Gestor Freelance — PNK Inmobiliaria';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido. Recargue la página.';
    } else {
        $rut = sanitize($_POST['rut'] ?? '');
    $nombre = sanitize($_POST['nombre_completo'] ?? '');
    $fechaNac = $_POST['fecha_nacimiento'] ?? '';
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $telefono = sanitize($_POST['telefono'] ?? '');

    $rutLimpio = str_replace(['.', ' '], '', $rut);

    if (!validarRut($rutLimpio)) {
        $error = 'El RUT ingresado no es válido.';
    } elseif (!validarPassword($password)) {
        $error = 'La contraseña debe tener al menos 8 caracteres, letras y números.';
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
            // Manejar certificado
            $certPath = null;
            if (isset($_FILES['certificado']) && $_FILES['certificado']['error'] === UPLOAD_ERR_OK) {
                $upload = handleFileUpload($_FILES['certificado'], 'certificados', ['pdf','jpg','jpeg','png'], 10 * 1024 * 1024);
                if ($upload['success']) {
                    $certPath = $upload['path'];
                } else {
                    $error = $upload['error'];
                }
            }

            if (!$error) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (rut, nombre_completo, fecha_nacimiento, correo, password, sexo, telefono, tipo_usuario, estado, certificado_antecedentes) VALUES (?, ?, ?, ?, ?, ?, ?, 'gestor', 'pendiente', ?)");
                $stmt->execute([$rutLimpio, $nombre, $fechaNac, $correo, $hash, $sexo, $telefono, $certPath]);
                $success = 'Postulación enviada. Si eres aceptado recibirás tu PENKA_ID por correo.';
            }
        }
        }
    }
}
?>

<section class="container my-5" style="max-width:700px;">
    <div class="card premium-card border-0 shadow p-4">
        <h2 class="text-center fw-bold mb-4"><i class="fas fa-user-tie text-warning me-2"></i>Registro Gestor Freelance</h2>

        <?php 
            if ($error) { $msg = $error; $msgType = 'danger'; }
            if ($success) { $msg = $success; $msgType = 'success'; }
        ?>
        <?php if (!$success): ?>

        <form method="POST" enctype="multipart/form-data" data-validate>
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">RUT</label>
                    <input type="text" name="rut" class="form-control" required placeholder="12.345.678-9">
                    <div class="invalid-feedback">RUT inválido</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre Completo</label>
                    <input type="text" name="nombre_completo" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Contraseña</label>
                    <input type="password" name="password" class="form-control" required minlength="8" placeholder="Mín. 8 caracteres, letras y números">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Confirmar Contraseña</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sexo</label>
                    <div class="d-flex gap-3 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sexo" value="M" id="sexoMG" required>
                            <label class="form-check-label" for="sexoMG">Masculino</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sexo" value="F" id="sexoFG">
                            <label class="form-check-label" for="sexoFG">Femenino</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Teléfono Móvil</label>
                    <input type="tel" name="telefono" class="form-control" placeholder="+56 9 1234 5678">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Certificado de Antecedentes (PDF o imagen, máx 10MB)</label>
                    <input type="file" name="certificado" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
            <button type="submit" class="btn btn-warning text-dark fw-bold w-100 py-2 mt-4">
                <i class="fas fa-paper-plane me-1"></i> Enviar Postulación
            </button>
        </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="login.php" class="text-primary">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
