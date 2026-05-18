<?php
$pageTitle = 'Mantenedor de Gestiones — PNK Inmobiliaria';
require_once 'includes/header.php';
requireLogin();

$isAdmin = getUserType() === 'administrador';

// Sólo el administrador puede ver y administrar gestiones
if (!$isAdmin) {
    header('Location: index.php');
    exit;
}

$msg = ''; $msgType = '';

// Acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Acción no autorizada (Token CSRF inválido).'; $msgType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

    if ($action === 'crear' || $action === 'editar') {
        $gestor_id = intval($_POST['gestor_id']);
        $propiedad_id = intval($_POST['propiedad_id']);
        $estado = $_POST['estado'] ?? 'pendiente';

        if ($action === 'crear') {
            // Verificar si ya existe
            $check = $pdo->prepare("SELECT id FROM gestiones WHERE gestor_id=? AND propiedad_id=?");
            $check->execute([$gestor_id, $propiedad_id]);
            if ($check->fetch()) {
                $msg = 'Este gestor ya está asignado a esta propiedad.'; $msgType = 'danger';
            } else {
                $stmt = $pdo->prepare("INSERT INTO gestiones (gestor_id, propiedad_id, estado) VALUES (?,?,?)");
                $stmt->execute([$gestor_id, $propiedad_id, $estado]);
                $msg = 'Gestión asignada correctamente.'; $msgType = 'success';
            }
        } elseif ($action === 'editar') {
            $gestion_id = intval($_POST['gestion_id']);
            $stmt = $pdo->prepare("UPDATE gestiones SET gestor_id=?, propiedad_id=?, estado=? WHERE id=?");
            $stmt->execute([$gestor_id, $propiedad_id, $estado, $gestion_id]);
            $msg = 'Gestión actualizada.'; $msgType = 'success';
        }
    } elseif ($action === 'eliminar') {
        $gestion_id = intval($_POST['gestion_id']);
        $pdo->prepare("DELETE FROM gestiones WHERE id=?")->execute([$gestion_id]);
        $msg = 'Gestión eliminada.'; $msgType = 'success';
    }
    }
}

// Filtros y Paginación
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$total = $pdo->query("SELECT COUNT(*) FROM gestiones")->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("
    SELECT g.*, u.nombre_completo AS gestor_nombre, p.codigo AS propiedad_codigo, p.comuna 
    FROM gestiones g
    JOIN usuarios u ON g.gestor_id = u.id
    JOIN propiedades p ON g.propiedad_id = p.id
    ORDER BY g.created_at DESC 
    LIMIT $perPage OFFSET $offset
");
$stmt->execute();
$gestiones = $stmt->fetchAll();

// Listas para el formulario
$gestores = $pdo->query("SELECT id, nombre_completo, penka_id FROM usuarios WHERE tipo_usuario='gestor' AND estado='activo'")->fetchAll();
$propiedades = $pdo->query("SELECT id, codigo, comuna FROM propiedades WHERE estado='activo'")->fetchAll();

// Para edición
$editGestion = null;
if (isset($_GET['edit'])) {
    $se = $pdo->prepare("SELECT * FROM gestiones WHERE id = ?");
    $se->execute([intval($_GET['edit'])]);
    $editGestion = $se->fetch();
}

$showForm = isset($_GET['new']) || $editGestion;
?>

<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-handshake text-warning me-2"></i>Gestiones y Asignaciones</h2>
        <div>
            <a href="dashboard.php" class="btn btn-outline-primary me-2"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
            <a href="?new=1" class="btn btn-warning text-dark fw-bold"><i class="fas fa-plus me-1"></i>Nueva Asignación</a>
        </div>
    </div>

    <?php if ($showForm): ?>
    <div class="card premium-card border-0 shadow p-4 mb-4">
        <h4 class="fw-bold mb-3"><i class="fas fa-<?= $editGestion ? 'edit' : 'plus-circle' ?> text-warning me-2"></i><?= $editGestion ? 'Editar' : 'Nueva' ?> Asignación</h4>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="action" value="<?= $editGestion ? 'editar' : 'crear' ?>">
            <?php if ($editGestion): ?><input type="hidden" name="gestion_id" value="<?= $editGestion['id'] ?>"><?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Gestor</label>
                    <select name="gestor_id" class="form-select" required>
                        <option value="">Seleccione Gestor...</option>
                        <?php foreach ($gestores as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ($editGestion['gestor_id']??'')==$g['id'] ? 'selected' : '' ?>>
                                <?= sanitize($g['nombre_completo']) ?> (<?= sanitize($g['penka_id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Propiedad</label>
                    <select name="propiedad_id" class="form-select" required>
                        <option value="">Seleccione Propiedad...</option>
                        <?php foreach ($propiedades as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($editGestion['propiedad_id']??'')==$p['id'] ? 'selected' : '' ?>>
                                <?= sanitize($p['codigo']) ?> - <?= sanitize($p['comuna']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Estado de Gestión</label>
                    <select name="estado" class="form-select" required>
                        <option value="pendiente" <?= ($editGestion['estado']??'')==='pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="activo" <?= ($editGestion['estado']??'')==='activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="finalizado" <?= ($editGestion['estado']??'')==='finalizado' ? 'selected' : '' ?>>Finalizado</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="fas fa-save me-1"></i>Guardar</button>
                <a href="crud-gestiones.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="card premium-card border-0 shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Gestor</th>
                            <th>Propiedad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($gestiones) > 0): ?>
                            <?php foreach ($gestiones as $g): ?>
                            <tr>
                                <td class="ps-4">#<?= $g['id'] ?></td>
                                <td><?= sanitize($g['gestor_nombre']) ?></td>
                                <td><?= sanitize($g['propiedad_codigo']) ?> (<?= sanitize($g['comuna']) ?>)</td>
                                <td>
                                    <?php if($g['estado']==='activo'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php elseif($g['estado']==='finalizado'): ?>
                                        <span class="badge bg-secondary">Finalizado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d-m-Y', strtotime($g['created_at'])) ?></td>
                                <td class="text-center pe-4">
                                    <a href="?edit=<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="gestion_id" value="<?= $g['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" data-confirm="¿Seguro que desea eliminar esta gestión?"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">No hay gestiones registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
