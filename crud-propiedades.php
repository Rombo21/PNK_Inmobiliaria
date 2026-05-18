<?php
$pageTitle = 'Mantenedor de Propiedades — PNK Inmobiliaria';
require_once 'includes/header.php';
requireLogin();

$isAdmin = getUserType() === 'administrador';
$msg = ''; $msgType = '';

// Acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear' || $action === 'editar') {
        $tipo = $_POST['tipo'] ?? 'casa';
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $banos = intval($_POST['banos'] ?? 0);
        $dormitorios = intval($_POST['dormitorios'] ?? 0);
        $areaTerreno = floatval($_POST['area_terreno'] ?? 0);
        $areaConstruida = floatval($_POST['area_construida'] ?? 0);
        $precioCLP = intval($_POST['precio_clp'] ?? 0);
        $precioUF = floatval($_POST['precio_uf'] ?? 0);
        $provincia = sanitize($_POST['provincia'] ?? '');
        $comuna = sanitize($_POST['comuna'] ?? '');
        $sector = sanitize($_POST['sector'] ?? '');
        $latitud = floatval($_POST['latitud'] ?? 0);
        $longitud = floatval($_POST['longitud'] ?? 0);
        $bodega = isset($_POST['bodega']) ? 1 : 0;
        $estacionamiento = isset($_POST['estacionamiento']) ? 1 : 0;
        $logia = isset($_POST['logia']) ? 1 : 0;
        $cocinaAmoblada = isset($_POST['cocina_amoblada']) ? 1 : 0;
        $antejardin = isset($_POST['antejardin']) ? 1 : 0;
        $patioTrasero = isset($_POST['patio_trasero']) ? 1 : 0;
        $piscina = isset($_POST['piscina']) ? 1 : 0;
        $estado = $_POST['estado_prop'] ?? 'activo';
        $propietarioId = $isAdmin ? intval($_POST['propietario_id'] ?? getUserId()) : getUserId();

        if ($action === 'crear') {
            if (getUserType() === 'gestor') {
                $msg = 'Los gestores no pueden crear propiedades.'; $msgType = 'danger';
            } else {
                $codigo = generarCodigoPropiedad($pdo, $tipo);
                $stmt = $pdo->prepare("INSERT INTO propiedades (codigo, propietario_id, tipo, descripcion, banos, dormitorios, area_terreno, area_construida, precio_clp, precio_uf, fecha_publicacion, provincia, comuna, sector, latitud, longitud, bodega, estacionamiento, logia, cocina_amoblada, antejardin, patio_trasero, piscina, estado) VALUES (?,?,?,?,?,?,?,?,?,?,CURDATE(),?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$codigo, $propietarioId, $tipo, $descripcion, $banos, $dormitorios, $areaTerreno, $areaConstruida, $precioCLP, $precioUF, $provincia, $comuna, $sector, $latitud, $longitud, $bodega, $estacionamiento, $logia, $cocinaAmoblada, $antejardin, $patioTrasero, $piscina, $estado]);
                $newPropId = $pdo->lastInsertId();

            // Subir fotos
            if (isset($_FILES['fotos'])) {
                $orden = 1;
                foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp) {
                    if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK || $orden > 10) continue;
                    $file = ['name'=>$_FILES['fotos']['name'][$i], 'tmp_name'=>$tmp, 'size'=>$_FILES['fotos']['size'][$i], 'error'=>$_FILES['fotos']['error'][$i]];
                    $upload = handleFileUpload($file, 'propiedades/'.$newPropId, ['jpg','jpeg','png','webp'], 5*1024*1024);
                    if ($upload['success']) {
                        $pdo->prepare("INSERT INTO fotos_propiedad (propiedad_id, ruta_imagen, orden) VALUES (?,?,?)")->execute([$newPropId, $upload['path'], $orden++]);
                    }
                }
            }
            if (getUserType() !== 'gestor') {
                $msg = "Propiedad creada con código: {$codigo}"; $msgType = 'success';
            }
            } // Cierre del else
        } elseif ($action === 'editar') {
            $propId = intval($_POST['prop_id']);
            $sql = "UPDATE propiedades SET tipo=?, descripcion=?, banos=?, dormitorios=?, area_terreno=?, area_construida=?, precio_clp=?, precio_uf=?, provincia=?, comuna=?, sector=?, latitud=?, longitud=?, bodega=?, estacionamiento=?, logia=?, cocina_amoblada=?, antejardin=?, patio_trasero=?, piscina=?, estado=? WHERE id=?";
            $executeParams = [$tipo, $descripcion, $banos, $dormitorios, $areaTerreno, $areaConstruida, $precioCLP, $precioUF, $provincia, $comuna, $sector, $latitud, $longitud, $bodega, $estacionamiento, $logia, $cocinaAmoblada, $antejardin, $patioTrasero, $piscina, $estado, $propId];
            if (getUserType() === 'propietario') { 
                $sql .= ' AND propietario_id=?'; 
                $executeParams[] = getUserId(); 
            } elseif (getUserType() === 'gestor') {
                $sql .= " AND id IN (SELECT propiedad_id FROM gestiones WHERE gestor_id=? AND estado='activo')";
                $executeParams[] = getUserId();
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($executeParams);

            // Nuevas fotos si se suben
            if (isset($_FILES['fotos']) && $_FILES['fotos']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                $stmtOrden = $pdo->prepare("SELECT COALESCE(MAX(orden),0) FROM fotos_propiedad WHERE propiedad_id=?");
                $stmtOrden->execute([$propId]);
                $maxOrden = $stmtOrden->fetchColumn();
                foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp) {
                    if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK || $maxOrden >= 10) continue;
                    $file = ['name'=>$_FILES['fotos']['name'][$i], 'tmp_name'=>$tmp, 'size'=>$_FILES['fotos']['size'][$i], 'error'=>$_FILES['fotos']['error'][$i]];
                    $upload = handleFileUpload($file, 'propiedades/'.$propId, ['jpg','jpeg','png','webp'], 5*1024*1024);
                    if ($upload['success']) {
                        $pdo->prepare("INSERT INTO fotos_propiedad (propiedad_id, ruta_imagen, orden) VALUES (?,?,?)")->execute([$propId, $upload['path'], ++$maxOrden]);
                    }
                }
            }
            $msg = 'Propiedad actualizada.'; $msgType = 'success';
        }
    } elseif ($action === 'eliminar') {
        if (getUserType() !== 'gestor') {
            $propId = intval($_POST['prop_id']);
            $sql = "DELETE FROM propiedades WHERE id=?";
            $delParams = [$propId];
            if (getUserType() === 'propietario') { 
                $sql .= ' AND propietario_id=?'; 
                $delParams[] = getUserId(); 
            }
            try {
                $pdo->prepare($sql)->execute($delParams);
                $msg = 'Propiedad eliminada.'; $msgType = 'success';
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') {
                    $msg = 'No se puede eliminar la propiedad porque tiene gestiones o visitas asociadas. Finalícelas o cancélelas primero.';
                } else {
                    $msg = 'Error al eliminar la propiedad.';
                }
                $msgType = 'danger';
            }
        } else {
            $msg = 'Los gestores no pueden eliminar propiedades.'; $msgType = 'danger';
        }
    }
}

// Filtros
$filtroTipo = $_GET['tipo'] ?? '';
$filtroEstado = $_GET['estado_prop'] ?? '';
$filtroComuna = $_GET['comuna'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$params = [];
$where = "WHERE 1=1";
if (getUserType() === 'propietario') { 
    $where .= " AND propietario_id = ?"; 
    $params[] = getUserId(); 
} elseif (getUserType() === 'gestor') {
    $where .= " AND id IN (SELECT propiedad_id FROM gestiones WHERE gestor_id = ? AND estado = 'activo')";
    $params[] = getUserId();
}
if ($filtroTipo) { $where .= " AND tipo = ?"; $params[] = $filtroTipo; }
if ($filtroEstado) { $where .= " AND estado = ?"; $params[] = $filtroEstado; }
if ($filtroComuna) { $where .= " AND comuna LIKE ?"; $params[] = "%{$filtroComuna}%"; }

$total = $pdo->prepare("SELECT COUNT(*) FROM propiedades $where");
$total->execute($params);
$totalRows = $total->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

$stmt = $pdo->prepare("SELECT * FROM propiedades $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$propiedades = $stmt->fetchAll();

// Para edición
$editProp = null;
if (isset($_GET['edit'])) {
    $q = "SELECT * FROM propiedades WHERE id = ?";
    $editParams = [intval($_GET['edit'])];
    if (getUserType() === 'propietario') { 
        $q .= " AND propietario_id = ?"; 
        $editParams[] = getUserId(); 
    } elseif (getUserType() === 'gestor') {
        $q .= " AND id IN (SELECT propiedad_id FROM gestiones WHERE gestor_id = ? AND estado = 'activo')";
        $editParams[] = getUserId();
    }
    $se = $pdo->prepare($q);
    $se->execute($editParams);
    $editProp = $se->fetch();
}

$showForm = isset($_GET['new']) || $editProp;

// Propietarios (para admin)
$propietarios = [];
if ($isAdmin) {
    $propietarios = $pdo->query("SELECT id, nombre_completo FROM usuarios WHERE tipo_usuario IN ('propietario','administrador') AND estado='activo'")->fetchAll();
}
?>

<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold"><i class="fas fa-building text-warning me-2"></i><?= $isAdmin ? 'Mantenedor de Propiedades' : 'Mis Propiedades' ?></h2>
        <div>
            <?php if ($isAdmin): ?><a href="dashboard.php" class="btn btn-outline-primary me-2"><i class="fas fa-arrow-left me-1"></i>Dashboard</a><?php endif; ?>
            <?php if (getUserType() !== 'gestor'): ?>
                <a href="?new=1" class="btn btn-warning text-dark fw-bold"><i class="fas fa-plus me-1"></i>Nueva Propiedad</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($showForm): ?>
    <!-- Formulario Crear/Editar -->
    <div class="card premium-card border-0 shadow p-4 mb-4">
        <h4 class="fw-bold mb-3"><i class="fas fa-<?= $editProp ? 'edit' : 'plus-circle' ?> text-warning me-2"></i><?= $editProp ? 'Editar' : 'Nueva' ?> Propiedad</h4>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $editProp ? 'editar' : 'crear' ?>">
            <?php if ($editProp): ?><input type="hidden" name="prop_id" value="<?= $editProp['id'] ?>"><?php endif; ?>

            <div class="row g-3">
                <?php if ($isAdmin): ?>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Propietario</label>
                    <select name="propietario_id" class="form-select" required>
                        <?php foreach ($propietarios as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($editProp && $editProp['propietario_id']==$p['id'])?'selected':'' ?>><?= sanitize($p['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="casa" <?= ($editProp && $editProp['tipo']==='casa')?'selected':'' ?>>Casa</option>
                        <option value="departamento" <?= ($editProp && $editProp['tipo']==='departamento')?'selected':'' ?>>Departamento</option>
                        <option value="terreno" <?= ($editProp && $editProp['tipo']==='terreno')?'selected':'' ?>>Terreno</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="estado_prop" class="form-select">
                        <option value="activo" <?= ($editProp && $editProp['estado']==='activo')?'selected':'' ?>>Activo</option>
                        <option value="inactivo" <?= ($editProp && $editProp['estado']==='inactivo')?'selected':'' ?>>Inactivo</option>
                        <option value="vendido" <?= ($editProp && $editProp['estado']==='vendido')?'selected':'' ?>>Vendido</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?= sanitize($editProp['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Dormitorios</label>
                    <input type="number" name="dormitorios" class="form-control" min="0" value="<?= $editProp['dormitorios'] ?? 0 ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Baños</label>
                    <input type="number" name="banos" class="form-control" min="0" value="<?= $editProp['banos'] ?? 0 ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Área Terreno (m²)</label>
                    <input type="number" name="area_terreno" class="form-control" step="0.01" value="<?= $editProp['area_terreno'] ?? 0 ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Área Construida (m²)</label>
                    <input type="number" name="area_construida" class="form-control" step="0.01" value="<?= $editProp['area_construida'] ?? 0 ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Precio CLP ($)</label>
                    <input type="number" name="precio_clp" class="form-control" value="<?= $editProp['precio_clp'] ?? 0 ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Precio UF</label>
                    <input type="number" name="precio_uf" class="form-control" step="0.01" value="<?= $editProp['precio_uf'] ?? 0 ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Provincia</label>
                    <select name="provincia" id="form-provincia" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="Elqui" <?= ($editProp && $editProp['provincia']==='Elqui')?'selected':'' ?>>Elqui</option>
                        <option value="Limarí" <?= ($editProp && $editProp['provincia']==='Limarí')?'selected':'' ?>>Limarí</option>
                        <option value="Choapa" <?= ($editProp && $editProp['provincia']==='Choapa')?'selected':'' ?>>Choapa</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Comuna</label>
                    <select name="comuna" id="form-comuna" class="form-select" required>
                        <option value="<?= sanitize($editProp['comuna'] ?? '') ?>"><?= sanitize($editProp['comuna'] ?? 'Seleccionar provincia primero') ?></option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Sector</label>
                    <input type="text" name="sector" class="form-control" value="<?= sanitize($editProp['sector'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Latitud</label>
                    <input type="number" name="latitud" class="form-control" step="0.0000001" value="<?= $editProp['latitud'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Longitud</label>
                    <input type="number" name="longitud" class="form-control" step="0.0000001" value="<?= $editProp['longitud'] ?? '' ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Amenidades</label>
                    <div class="check-grid">
                        <?php foreach (['bodega'=>'Bodega','estacionamiento'=>'Estacionamiento','logia'=>'Logia','cocina_amoblada'=>'Cocina Amoblada','antejardin'=>'Antejardín','patio_trasero'=>'Patio Trasero','piscina'=>'Piscina'] as $k=>$v): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="<?= $k ?>" id="chk_<?= $k ?>" <?= ($editProp && $editProp[$k])?'checked':'' ?>>
                            <label class="form-check-label" for="chk_<?= $k ?>"><?= $v ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Fotos (máx 10, 5MB c/u)</label>
                    <input type="file" name="fotos[]" class="form-control" multiple accept="image/*" data-preview="foto-preview">
                    <div id="foto-preview" class="d-flex flex-wrap mt-2"></div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="fas fa-save me-1"></i>Guardar</button>
                <a href="crud-propiedades.php" class="btn btn-secondary ms-2">Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card premium-card border-0 shadow p-3 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="casa" <?= $filtroTipo==='casa'?'selected':'' ?>>Casa</option>
                    <option value="departamento" <?= $filtroTipo==='departamento'?'selected':'' ?>>Departamento</option>
                    <option value="terreno" <?= $filtroTipo==='terreno'?'selected':'' ?>>Terreno</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Estado</label>
                <select name="estado_prop" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo" <?= $filtroEstado==='activo'?'selected':'' ?>>Activo</option>
                    <option value="inactivo" <?= $filtroEstado==='inactivo'?'selected':'' ?>>Inactivo</option>
                    <option value="vendido" <?= $filtroEstado==='vendido'?'selected':'' ?>>Vendido</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Comuna</label>
                <input type="text" name="comuna" class="form-control" value="<?= sanitize($filtroComuna) ?>" placeholder="Buscar...">
            </div>
            <div class="col-md-3">
                <button class="btn btn-warning text-dark fw-bold w-100"><i class="fas fa-filter me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr><th>Código</th><th>Tipo</th><th>Comuna</th><th>Sector</th><th>Precio</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php foreach ($propiedades as $p): ?>
                <tr>
                    <td class="fw-bold"><?= sanitize($p['codigo']) ?></td>
                    <td><span class="badge bg-primary"><?= ucfirst($p['tipo']) ?></span></td>
                    <td><?= sanitize($p['comuna']) ?></td>
                    <td><?= sanitize($p['sector']) ?></td>
                    <td>$<?= number_format($p['precio_clp'], 0, ',', '.') ?></td>
                    <td><span class="badge bg-<?= $p['estado']==='activo'?'success':($p['estado']==='vendido'?'info':'secondary') ?>"><?= ucfirst($p['estado']) ?></span></td>
                    <td>
                        <a href="detalle-propiedad.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                        <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="prop_id" value="<?= $p['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" data-confirm="¿Eliminar esta propiedad?"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav><ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&tipo=<?= $filtroTipo ?>&estado_prop=<?= $filtroEstado ?>&comuna=<?= $filtroComuna ?>"><?= $i ?></a></li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    actualizarComunas('form-provincia', 'form-comuna');
});
</script>

<?php require_once 'includes/footer.php'; ?>
