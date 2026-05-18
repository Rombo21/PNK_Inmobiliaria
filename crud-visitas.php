<?php
$pageTitle = 'Administrar Visitas — PNK Inmobiliaria';
require_once 'includes/header.php';
requireLogin();

$userType = getUserType();
$userId = getUserId();
$isAdmin = $userType === 'administrador';
$isPropietario = $userType === 'propietario';
$isGestor = $userType === 'gestor';

$msg = ''; $msgType = '';

// Acciones POST (sólo actualizar estado de la visita)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Acción no autorizada (Token CSRF inválido).'; $msgType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'actualizar_estado') {
        $visita_id = intval($_POST['visita_id']);
        $estado = $_POST['estado'];

        // Validar permisos
        $allowed = false;
        if ($isAdmin) {
            $allowed = true;
        } else {
            // Verificar si la visita pertenece a una propiedad del usuario o gestionada por él
            $checkQ = "
                SELECT v.id FROM visitas v 
                JOIN propiedades p ON v.propiedad_id = p.id
                LEFT JOIN gestiones g ON g.propiedad_id = p.id AND g.estado = 'activo'
                WHERE v.id = ? 
            ";
            $params = [$visita_id];
            
            if ($isPropietario) {
                $checkQ .= " AND p.propietario_id = ?";
                $params[] = $userId;
            } elseif ($isGestor) {
                $checkQ .= " AND g.gestor_id = ?";
                $params[] = $userId;
            }
            
            $checkStmt = $pdo->prepare($checkQ);
            $checkStmt->execute($params);
            if ($checkStmt->fetch()) {
                $allowed = true;
            }
        }

        if ($allowed) {
            $stmt = $pdo->prepare("UPDATE visitas SET estado = ? WHERE id = ?");
            $stmt->execute([$estado, $visita_id]);
            $msg = 'Estado de la visita actualizado.'; $msgType = 'success';
        } else {
            $msg = 'No tiene permisos para modificar esta visita.'; $msgType = 'danger';
        }
        }
    }
}

// Filtros y Paginación
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$query = "
    FROM visitas v
    JOIN propiedades p ON v.propiedad_id = p.id
";
$where = " WHERE 1=1 ";
$params = [];

if ($isPropietario) {
    $where .= " AND p.propietario_id = ? ";
    $params[] = $userId;
} elseif ($isGestor) {
    $query .= " JOIN gestiones g ON g.propiedad_id = p.id AND g.estado = 'activo' ";
    $where .= " AND g.gestor_id = ? ";
    $params[] = $userId;
}

$totalQ = "SELECT COUNT(DISTINCT v.id) " . $query . $where;
$stmtTotal = $pdo->prepare($totalQ);
$stmtTotal->execute($params);
$total = $stmtTotal->fetchColumn();
$totalPages = ceil($total / $perPage);

$dataQ = "
    SELECT v.*, p.codigo as propiedad_codigo, p.comuna
    " . $query . $where . "
    GROUP BY v.id
    ORDER BY v.created_at DESC 
    LIMIT $perPage OFFSET $offset
";
$stmtData = $pdo->prepare($dataQ);
$stmtData->execute($params);
$visitas = $stmtData->fetchAll();
?>

<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-calendar-check text-warning me-2"></i>Gestión de Visitas</h2>
        <?php if ($isAdmin): ?>
            <a href="dashboard.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
        <?php endif; ?>
    </div>

    <div class="card premium-card border-0 shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Fecha Solicitud</th>
                            <th>Propiedad</th>
                            <th>Visitante</th>
                            <th>Contacto</th>
                            <th>Fecha Visita</th>
                            <th>Estado</th>
                            <th class="text-center pe-4">Actualizar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($visitas) > 0): ?>
                            <?php foreach ($visitas as $v): ?>
                            <tr>
                                <td class="ps-4"><?= date('d-m-Y', strtotime($v['created_at'])) ?></td>
                                <td>
                                    <a href="detalle-propiedad.php?id=<?= $v['propiedad_id'] ?>" target="_blank" class="text-decoration-none fw-bold">
                                        <?= sanitize($v['propiedad_codigo']) ?>
                                    </a>
                                    <br><small class="text-muted"><?= sanitize($v['comuna']) ?></small>
                                </td>
                                <td>
                                    <?= sanitize($v['nombre_visitante']) ?>
                                    <?php if ($v['mensaje']): ?>
                                        <i class="fas fa-comment-dots text-primary ms-1" title="<?= sanitize($v['mensaje']) ?>" data-bs-toggle="tooltip"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="mailto:<?= sanitize($v['correo']) ?>" class="text-decoration-none"><i class="fas fa-envelope me-1"></i><?= sanitize($v['correo']) ?></a>
                                    <br>
                                    <?php if ($v['telefono']): ?>
                                        <a href="tel:<?= sanitize($v['telefono']) ?>" class="text-decoration-none text-muted"><i class="fas fa-phone me-1"></i><?= sanitize($v['telefono']) ?></a>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= date('d-m-Y', strtotime($v['fecha_solicitada'])) ?></td>
                                <td>
                                    <?php if($v['estado']==='confirmada'): ?>
                                        <span class="badge bg-success">Confirmada</span>
                                    <?php elseif($v['estado']==='cancelada'): ?>
                                        <span class="badge bg-danger">Cancelada</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4">
                                    <form method="POST" class="d-flex align-items-center justify-content-center gap-2">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="action" value="actualizar_estado">
                                        <input type="hidden" name="visita_id" value="<?= $v['id'] ?>">
                                        <select name="estado" class="form-select form-select-sm" style="width: auto;" required>
                                            <option value="pendiente" <?= $v['estado']==='pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                            <option value="confirmada" <?= $v['estado']==='confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                            <option value="cancelada" <?= $v['estado']==='cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-save"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4">No hay visitas registradas.</td></tr>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>

<?php require_once 'includes/footer.php'; ?>
