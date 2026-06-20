<?php
$pageTitle = 'PNK Inmobiliaria — Tu hogar en la Región de Coquimbo';
$pageDescription = 'Encuentra casas, departamentos y terrenos en La Serena, Coquimbo y Ovalle. Plataforma inmobiliaria de confianza.';
require_once 'includes/header.php';

// Cargar propiedades destacadas desde BD
$stmtProps = $pdo->query("
    SELECT p.*, 
        (SELECT fp.ruta_imagen FROM fotos_propiedad fp WHERE fp.propiedad_id = p.id ORDER BY fp.orden LIMIT 1) as foto_principal
    FROM propiedades p 
    WHERE p.estado = 'activo' 
    ORDER BY p.fecha_publicacion DESC 
    LIMIT 6
");
$propiedades = $stmtProps->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section" style="background:linear-gradient(135deg,rgba(13,71,161,0.92),rgba(10,25,80,0.95)),url('img/casa_destacada_1/Casadestacada1.webp') center/cover;min-height:400px;display:flex;align-items:center;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="font-family:'Playfair Display',serif;font-size:2.8rem;font-weight:800;margin-bottom:1rem;color:#fff;">
            <i class="fas fa-building" style="color:#ffc107;"></i> PNK Inmobiliaria
        </h1>
        <p style="font-size:1.2rem;opacity:0.9;max-width:600px;margin:0 auto 2rem;">
            Tu próximo hogar en la Región de Coquimbo te está esperando
        </p>
        <a href="#buscador" class="btn btn-warning btn-lg text-dark fw-bold px-4">
            <i class="fas fa-search me-2"></i>Buscar Propiedades
        </a>
    </div>
</section>

<!-- Sobre Nosotros -->
<section id="sobre-nosotros" class="container my-5">
    <div class="card premium-card border-0 shadow-sm p-4 p-md-5 text-center bg-white mx-auto" style="max-width:1000px;">
        <div class="card-body">
            <i class="fa-solid fa-handshake-angle fa-4x text-warning mb-4"></i>
            <h2 class="mb-4 fw-bold">Sobre nosotros</h2>
            <p class="fs-5 text-muted px-md-5">
                En <strong class="text-primary">PNK Inmobiliaria</strong> trabajamos para conectar a propietarios y gestores freelance con las mejores oportunidades del mercado inmobiliario en la Región de Coquimbo.
                Nuestro compromiso es ofrecer un servicio transparente, confiable y cercano, adaptado a las necesidades de cada cliente.
            </p>
        </div>
    </div>
</section>

<!-- Buscador -->
<section id="buscador" class="container my-5">
    <div class="card premium-card border-0 shadow p-4">
        <h2 class="text-center mb-4 fw-bold"><i class="fas fa-search text-warning me-2"></i>Buscar Propiedades</h2>
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-bold">Tipo</label>
                <select id="filtro-tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="casa">Casa</option>
                    <option value="departamento">Departamento</option>
                    <option value="terreno">Terreno</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Provincia</label>
                <select id="filtro-provincia" class="form-select">
                    <option value="">Todas</option>
                    <option value="Elqui">Elqui</option>
                    <option value="Limarí">Limarí</option>
                    <option value="Choapa">Choapa</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Comuna</label>
                <select id="filtro-comuna" class="form-select">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Sector</label>
                <select id="filtro-sector" class="form-select">
                    <option value="">Todos los sectores</option>
                </select>
            </div>
            <div class="col-md-2">
                <button onclick="buscarPropiedades()" class="btn btn-warning text-dark fw-bold w-100 py-2">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
        </div>
    </div>
    <div id="resultados-busqueda" class="mt-4"></div>
</section>

<!-- Propiedades Destacadas -->
<section class="container my-5">
    <h2 class="mb-4 fw-bold"><i class="fas fa-star text-warning me-2"></i>Propiedades Destacadas</h2>
    <div class="row g-4">
        <?php foreach ($propiedades as $prop): ?>
        <div class="col-md-4">
            <div class="card premium-card border-0 h-100">
                <img src="<?= sanitize($prop['foto_principal'] ?: 'img/LogoPNK2.png') ?>" 
                    class="card-img-top" alt="<?= sanitize($prop['tipo']) ?> en <?= sanitize($prop['sector']) ?>"
                    style="height:240px;object-fit:cover;border-radius:15px 15px 0 0;">
                <div class="card-body text-center">
                    <span class="badge bg-warning text-dark mb-2"><?= ucfirst($prop['tipo']) ?></span>
                    <h5 class="fw-bold"><?= ucfirst($prop['tipo']) ?> en <?= sanitize($prop['sector'] ?: $prop['comuna']) ?></h5>
                    <p class="text-muted small">
                        <i class="fas fa-map-marker-alt"></i> <?= sanitize($prop['comuna']) ?>, <?= sanitize($prop['provincia']) ?>
                    </p>
                    <p class="text-muted small">#<?= sanitize($prop['codigo']) ?></p>
                    <p class="fw-bold fs-5 text-primary">$<?= number_format($prop['precio_clp'], 0, ',', '.') ?></p>
                    <a href="detalle-propiedad.php?id=<?= $prop['id'] ?>" class="btn btn-warning text-dark fw-bold px-4">
                        <i class="fas fa-eye me-1"></i> ¡Quiero saber más!
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        actualizarComunas('filtro-provincia', 'filtro-comuna', 'filtro-sector');
    });
</script>

<?php require_once 'includes/footer.php'; ?>
