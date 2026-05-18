<?php
/**
 * API AJAX para usuarios — PNK Inmobiliaria
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Solo admin puede acceder
if (!isLoggedIn() || getUserType() !== 'administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'detalle') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        echo json_encode(['error' => 'ID inválido']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT u.*, 
        (SELECT COUNT(*) FROM propiedades p WHERE p.propietario_id = u.id) as total_propiedades
        FROM usuarios u WHERE u.id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if ($user) {
        unset($user['password']);
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
