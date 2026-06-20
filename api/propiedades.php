<?php
/**
 * API AJAX para propiedades — PNK Inmobiliaria
 * Devuelve JSON con propiedades filtradas.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? '';

if ($action === 'buscar') {
    $provincia = $_GET['provincia'] ?? '';
    $comuna = $_GET['comuna'] ?? '';
    $tipo = $_GET['tipo'] ?? '';
    $sector = $_GET['sector'] ?? '';

    $where = "WHERE p.estado = 'activo'";
    $params = [];

    if ($provincia) {
        $where .= " AND p.provincia = ?";
        $params[] = $provincia;
    }
    if ($comuna) {
        $where .= " AND p.comuna = ?";
        $params[] = $comuna;
    }
    if ($tipo) {
        $where .= " AND p.tipo = ?";
        $params[] = $tipo;
    }
    if ($sector) {
        $where .= " AND p.sector LIKE ?";
        $params[] = "%$sector%";
    }

    $sql = "SELECT p.id, p.codigo, p.tipo, p.precio_clp, p.precio_uf, p.provincia, p.comuna, p.sector,
                p.dormitorios, p.banos, p.area_construida,
                (SELECT fp.ruta_imagen FROM fotos_propiedad fp WHERE fp.propiedad_id = p.id ORDER BY fp.orden LIMIT 1) as foto
            FROM propiedades p
            $where
            ORDER BY p.fecha_publicacion DESC
            LIMIT 30";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    echo json_encode($results);
    exit;
}

// Obtener comunas por provincia
if ($action === 'comunas') {
    $provincia = $_GET['provincia'] ?? '';
    $comunas = [
        'Elqui' => ['La Serena','Coquimbo','Andacollo','La Higuera','Paihuano','Vicuña'],
        'Limarí' => ['Ovalle','Combarbalá','Monte Patria','Punitaqui','Río Hurtado'],
        'Choapa' => ['Illapel','Canela','Los Vilos','Salamanca']
    ];
    echo json_encode($comunas[$provincia] ?? []);
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
