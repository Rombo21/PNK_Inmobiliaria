<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT p.*, u.nombre_completo as propietario_nombre FROM propiedades p JOIN usuarios u ON p.propietario_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$prop = $stmt->fetch();
if (!$prop) { header('Location: index.php'); exit; }

$pageTitle = ucfirst($prop['tipo']) . ' en ' . $prop['sector'] . ' — PNK Inmobiliaria';
require_once 'includes/header.php';

// Fotos
$stmtFotos = $pdo->prepare("SELECT * FROM fotos_propiedad WHERE propiedad_id = ? ORDER BY orden");
$stmtFotos->execute([$id]);
$fotos = $stmtFotos->fetchAll();

// Procesar solicitud de visita
$visitaMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_visita'])) {
    $nombre = sanitize($_POST['nombre_visitante'] ?? '');
    $correo = filter_input(INPUT_POST, 'correo_visita', FILTER_SANITIZE_EMAIL);
    $telefono = sanitize($_POST['telefono_visita'] ?? '');
    $fecha = $_POST['fecha_visita'] ?? '';
    $mensaje = sanitize($_POST['mensaje_visita'] ?? '');

    if ($nombre && $correo && $fecha) {
        $stmt = $pdo->prepare("INSERT INTO visitas (propiedad_id, nombre_visitante, correo, telefono, fecha_solicitada, mensaje) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $nombre, $correo, $telefono, $fecha, $mensaje]);
        $visitaMsg = 'success';
    } else {
        $visitaMsg = 'error';
    }
}
$shareUrl = urlencode(SITE_URL . '/detalle-propiedad.php?id=' . $id);
$shareText = urlencode(ucfirst($prop['tipo']) . ' en ' . $prop['sector'] . ' - PNK Inmobiliaria');
?>

<section class="container my-5">
    <a href="index.php" class="btn btn-outline-primary mb-3"><i class="fas fa-arrow-left me-1"></i>Volver</a>

    <div class="row g-4">
        <!-- Galería -->
        <div class="col-lg-7">
            <div class="card premium-card border-0 shadow overflow-hidden">
                <?php if (count($fotos) > 0): ?>
                <div id="carruselDetalle" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php foreach ($fotos as $i => $f): ?>
                        <button type="button" data-bs-target="#carruselDetalle" data-bs-slide-to="<?= $i ?>" <?= $i===0?'class="active"':'' ?>></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($fotos as $i => $f): ?>
                        <div class="carousel-item <?= $i===0?'active':'' ?>">
                            <img src="<?= sanitize($f['ruta_imagen']) ?>" class="d-block w-100" alt="Foto <?= $i+1 ?>" style="height:450px;object-fit:cover;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carruselDetalle" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carruselDetalle" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                </div>
                <?php else: ?>
                <img src="img/LogoPNK2.png" class="w-100 p-5" alt="Sin fotos">
                <?php endif; ?>
            </div>
        </div>

        <!-- Info -->
        <div class="col-lg-5">
            <div class="card premium-card border-0 shadow p-4">
                <span class="badge bg-warning text-dark mb-2 align-self-start"><?= ucfirst($prop['tipo']) ?></span>
                <h2 class="fw-bold"><?= ucfirst($prop['tipo']) ?> en <?= sanitize($prop['sector'] ?: $prop['comuna']) ?></h2>
                <p class="text-muted">#<?= sanitize($prop['codigo']) ?> | <i class="fas fa-map-marker-alt"></i> <?= sanitize($prop['comuna']) ?>, <?= sanitize($prop['provincia']) ?></p>
                
                <h3 class="text-primary fw-bold">$<?= number_format($prop['precio_clp'], 0, ',', '.') ?></h3>
                <p class="text-muted"><?= number_format($prop['precio_uf'], 2, ',', '.') ?> UF</p>
                
                <hr>
                <div class="row g-2 text-center mb-3">
                    <div class="col-4"><i class="fas fa-bed fa-lg text-primary"></i><br><strong><?= $prop['dormitorios'] ?></strong><br><small>Dormitorios</small></div>
                    <div class="col-4"><i class="fas fa-bath fa-lg text-primary"></i><br><strong><?= $prop['banos'] ?></strong><br><small>Baños</small></div>
                    <div class="col-4"><i class="fas fa-ruler-combined fa-lg text-primary"></i><br><strong><?= $prop['area_construida'] ?></strong><br><small>m² construidos</small></div>
                </div>
                <p><strong>Terreno:</strong> <?= $prop['area_terreno'] ?> m²</p>
                <p><strong>Publicado:</strong> <?= date('d/m/Y', strtotime($prop['fecha_publicacion'])) ?></p>

                <!-- Amenidades -->
                <h5 class="fw-bold mt-3"><i class="fas fa-list-check text-warning me-1"></i>Amenidades</h5>
                <div class="row g-2">
                    <?php
                    $amenidades = [
                        'bodega' => ['Bodega', 'fa-box'],
                        'estacionamiento' => ['Estacionamiento', 'fa-car'],
                        'logia' => ['Logia', 'fa-shirt'],
                        'cocina_amoblada' => ['Cocina Amoblada', 'fa-utensils'],
                        'antejardin' => ['Antejardín', 'fa-seedling'],
                        'patio_trasero' => ['Patio Trasero', 'fa-tree'],
                        'piscina' => ['Piscina', 'fa-water-ladder'],
                    ];
                    foreach ($amenidades as $key => [$label, $icon]):
                    ?>
                    <div class="col-6">
                        <span class="<?= $prop[$key] ? 'text-success' : 'text-danger' ?>">
                            <i class="fas <?= $prop[$key] ? 'fa-check-circle' : 'fa-times-circle' ?> me-1"></i>
                            <?= $label ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Compartir -->
                <div class="mt-3">
                    <strong>Compartir:</strong>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $shareUrl ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fab fa-x-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Descripción -->
    <div class="card premium-card border-0 shadow p-4 mt-4">
        <h4 class="fw-bold"><i class="fas fa-align-left text-warning me-2"></i>Descripción</h4>
        <p><?= nl2br(sanitize($prop['descripcion'])) ?></p>
    </div>

    <!-- Mapa -->
    <?php if ($prop['latitud'] && $prop['longitud']): ?>
    <div class="card premium-card border-0 shadow p-4 mt-4">
        <h4 class="fw-bold"><i class="fas fa-map-marked-alt text-warning me-2"></i>Ubicación</h4>
        <iframe width="100%" height="350" style="border:0;border-radius:10px;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
            src="https://maps.google.com/maps?q=<?= $prop['latitud'] ?>,<?= $prop['longitud'] ?>&z=15&output=embed"></iframe>
    </div>
    <?php endif; ?>

    <!-- Solicitar Visita -->
    <div class="card premium-card border-0 shadow p-4 mt-4">
        <h4 class="fw-bold"><i class="fas fa-calendar-alt text-warning me-2"></i>Solicitar Visita</h4>
        <?php if ($visitaMsg === 'success'): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>¡Solicitud enviada exitosamente!</div>
        <?php elseif ($visitaMsg === 'error'): ?>
            <div class="alert alert-danger">Complete todos los campos obligatorios.</div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="solicitar_visita" value="1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre Completo</label>
                    <input type="text" name="nombre_visitante" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Correo Electrónico</label>
                    <input type="email" name="correo_visita" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="tel" name="telefono_visita" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Fecha Deseada</label>
                    <input type="date" name="fecha_visita" class="form-control" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Mensaje</label>
                    <textarea name="mensaje_visita" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-warning text-dark fw-bold mt-3"><i class="fas fa-paper-plane me-1"></i>Enviar Solicitud</button>
        </form>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
