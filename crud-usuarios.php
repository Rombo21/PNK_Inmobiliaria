<?php
$pageTitle = 'Mantenedor de Usuarios — PNK Inmobiliaria';
require_once 'includes/header.php';
requireAdmin();

$msg = ''; $msgType = '';

// Acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if ($action === 'toggle_estado' && $userId) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user && $user['tipo_usuario'] !== 'administrador') {
            $nuevoEstado = $user['estado'] === 'activo' ? 'inactivo' : 'activo';
            
            // Si se activa un gestor pendiente, generar PENKA_ID
            $penkaId = $user['penka_id'];
            if ($nuevoEstado === 'activo' && $user['tipo_usuario'] === 'gestor' && !$user['penka_id']) {
                $penkaId = generarPenkaId($pdo);
            }
            
            $stmt = $pdo->prepare("UPDATE usuarios SET estado = ?, penka_id = ? WHERE id = ?");
            $stmt->execute([$nuevoEstado, $penkaId, $userId]);
            $msg = "Usuario " . ($nuevoEstado === 'activo' ? 'activado' : 'desactivado') . " exitosamente.";
            if ($penkaId && !$user['penka_id']) {
                $msg .= " PENKA_ID asignado: {$penkaId}";
            }
            $msgType = 'success';
        }
    } elseif ($action === 'eliminar' && $userId) {
        // No eliminar al admin principal (id=1)
        if ($userId > 1) {
            $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND id != 1")->execute([$userId]);
            $msg = 'Usuario eliminado.'; $msgType = 'success';
        } else {
            $msg = 'No se puede eliminar al administrador principal.'; $msgType = 'danger';
        }
    } elseif ($action === 'editar' && $userId) {
        $nombre = sanitize($_POST['nombre_completo'] ?? '');
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $telefono = sanitize($_POST['telefono'] ?? '');
        $tipo = $_POST['tipo_usuario'] ?? '';
        $sexo = $_POST['sexo'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo=?, correo=?, telefono=?, tipo_usuario=?, sexo=? WHERE id=?");
        $stmt->execute([$nombre, $correo, $telefono, $tipo, $sexo, $userId]);
        $msg = 'Usuario actualizado.'; $msgType = 'success';
    }
}

// Filtros y paginación
$filtroTipo = $_GET['tipo'] ?? '';
$filtroEstado = $_GET['estado'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];
if ($filtroTipo) { $where .= " AND tipo_usuario = ?"; $params[] = $filtroTipo; }
if ($filtroEstado) { $where .= " AND estado = ?"; $params[] = $filtroEstado; }

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM usuarios $where");
$stmtCount->execute($params);
$total = $stmtCount->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT * FROM usuarios $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

// Para edición modal
$editUser = null;
if (isset($_GET['edit'])) {
    $stmtEdit = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmtEdit->execute([intval($_GET['edit'])]);
    $editUser = $stmtEdit->fetch();
}
?>

<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-users-cog text-warning me-2"></i>Mantenedor de Usuarios</h2>
        <a href="dashboard.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
            <i class="fas fa-<?= $msgType==='success'?'check':'exclamation' ?>-circle me-2"></i><?= sanitize($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card premium-card border-0 shadow p-3 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Tipo de Usuario</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="propietario" <?= $filtroTipo==='propietario'?'selected':'' ?>>Propietario</option>
                    <option value="gestor" <?= $filtroTipo==='gestor'?'selected':'' ?>>Gestor</option>
                    <option value="administrador" <?= $filtroTipo==='administrador'?'selected':'' ?>>Administrador</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo" <?= $filtroEstado==='activo'?'selected':'' ?>>Activo</option>
                    <option value="pendiente" <?= $filtroEstado==='pendiente'?'selected':'' ?>>Pendiente</option>
                    <option value="inactivo" <?= $filtroEstado==='inactivo'?'selected':'' ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-warning text-dark fw-bold w-100"><i class="fas fa-filter me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr><th>ID</th><th>RUT</th><th>Nombre</th><th>Correo</th><th>Tipo</th><th>Estado</th><th>PENKA_ID</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= sanitize($u['rut']) ?></td>
                    <td><?= sanitize($u['nombre_completo']) ?></td>
                    <td><?= sanitize($u['correo']) ?></td>
                    <td><span class="badge bg-<?= $u['tipo_usuario']==='administrador'?'danger':($u['tipo_usuario']==='propietario'?'primary':'info') ?>"><?= ucfirst($u['tipo_usuario']) ?></span></td>
                    <td><span class="badge bg-<?= $u['estado']==='activo'?'success':($u['estado']==='pendiente'?'warning text-dark':'secondary') ?>"><?= ucfirst($u['estado']) ?></span></td>
                    <td><?= $u['penka_id'] ? sanitize($u['penka_id']) : '—' ?></td>
                    <td>
                        <?php if ($u['id'] > 1): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="toggle_estado">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button class="btn btn-sm btn-<?= $u['estado']==='activo'?'outline-warning':'outline-success' ?>" title="<?= $u['estado']==='activo'?'Desactivar':'Activar' ?>">
                                <i class="fas fa-<?= $u['estado']==='activo'?'ban':'check' ?>"></i>
                            </button>
                        </form>
                        <a href="?edit=<?= $u['id'] ?>&<?= http_build_query(['tipo'=>$filtroTipo,'estado'=>$filtroEstado,'page'=>$page]) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" data-confirm="¿Eliminar este usuario?" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
    <nav><ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i===$page?'active':'' ?>">
            <a class="page-link" href="?page=<?= $i ?>&tipo=<?= $filtroTipo ?>&estado=<?= $filtroEstado ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</section>

<!-- Modal Editar -->
<?php if ($editUser): ?>
<div class="modal fade show d-block" style="background:rgba(0,0,0,0.5);" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit text-warning me-2"></i>Editar Usuario</h5>
                    <a href="crud-usuarios.php?tipo=<?= $filtroTipo ?>&estado=<?= $filtroEstado ?>&page=<?= $page ?>" class="btn-close"></a>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="editar">
                    <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre_completo" class="form-control" value="<?= sanitize($editUser['nombre_completo']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Correo</label>
                        <input type="email" name="correo" class="form-control" value="<?= sanitize($editUser['correo']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control" value="<?= sanitize($editUser['telefono']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo</label>
                        <select name="tipo_usuario" class="form-select">
                            <option value="propietario" <?= $editUser['tipo_usuario']==='propietario'?'selected':'' ?>>Propietario</option>
                            <option value="gestor" <?= $editUser['tipo_usuario']==='gestor'?'selected':'' ?>>Gestor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sexo</label>
                        <select name="sexo" class="form-select">
                            <option value="M" <?= $editUser['sexo']==='M'?'selected':'' ?>>Masculino</option>
                            <option value="F" <?= $editUser['sexo']==='F'?'selected':'' ?>>Femenino</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="crud-usuarios.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
