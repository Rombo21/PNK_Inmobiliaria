<?php
/**
 * PNK Inmobiliaria — Verificación de autenticación
 * Incluir al inicio de páginas protegidas.
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Verifica si el usuario está logueado.
 * Si no, redirige a login.php
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Verifica si el usuario es administrador.
 * Si no, redirige al index.
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['tipo_usuario'] !== 'administrador') {
        header('Location: index.php');
        exit;
    }
}

/**
 * Verifica si el usuario es propietario o admin.
 */
function requirePropietarioOrAdmin() {
    requireLogin();
    if (!in_array($_SESSION['tipo_usuario'], ['administrador', 'propietario'])) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Verifica si el usuario está logueado.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Retorna el tipo de usuario actual o null.
 */
function getUserType(): ?string {
    return $_SESSION['tipo_usuario'] ?? null;
}

/**
 * Retorna el nombre del usuario logueado.
 */
function getUserName(): string {
    return $_SESSION['nombre_completo'] ?? 'Usuario';
}

/**
 * Retorna el ID del usuario logueado.
 */
function getUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Sanitiza input para prevenir XSS.
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida formato RUT chileno (XX.XXX.XXX-X o XXXXXXXX-X).
 */
function validarRut(string $rut): bool {
    // Limpiar puntos y espacios
    $rut = str_replace(['.', ' '], '', trim($rut));
    
    // Debe tener formato XXXXXXXX-X
    if (!preg_match('/^[0-9]{7,8}-[0-9kK]{1}$/', $rut)) {
        return false;
    }
    
    list($numero, $dv) = explode('-', $rut);
    $dv = strtoupper($dv);
    
    // Calcular dígito verificador
    $suma = 0;
    $factor = 2;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += intval($numero[$i]) * $factor;
        $factor = $factor === 7 ? 2 : $factor + 1;
    }
    
    $resto = $suma % 11;
    $dvCalculado = 11 - $resto;
    
    if ($dvCalculado == 11) $dvCalculado = '0';
    elseif ($dvCalculado == 10) $dvCalculado = 'K';
    else $dvCalculado = strval($dvCalculado);
    
    return $dv === $dvCalculado;
}

/**
 * Genera PENKA_ID único para gestores.
 */
function generarPenkaId(PDO $pdo): string {
    $year = date('Y');
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING_INDEX(penka_id, '-', -1) AS UNSIGNED)) as max_num 
                        FROM usuarios WHERE penka_id LIKE 'PNK-{$year}-%'");
    $result = $stmt->fetch();
    $nextNum = ($result['max_num'] ?? 0) + 1;
    return "PNK-{$year}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
}

/**
 * Genera código de propiedad único.
 */
function generarCodigoPropiedad(PDO $pdo, string $tipo): string {
    $prefijo = match($tipo) {
        'casa' => 'C',
        'departamento' => 'D',
        'terreno' => 'T',
        default => 'X'
    };
    
    $year = date('Y');
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)) as max_num 
                        FROM propiedades WHERE codigo LIKE '{$prefijo}{$year}-%'");
    $result = $stmt->fetch();
    $nextNum = ($result['max_num'] ?? 0) + 1;
    return "{$prefijo}{$year}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
}

/**
 * Manejo de subida de archivos.
 */
function handleFileUpload(array $file, string $destDir, array $allowedExts, int $maxSize): array {
    $result = ['success' => false, 'path' => '', 'error' => ''];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Error al subir el archivo.';
        return $result;
    }
    
    // Validar tamaño
    if ($file['size'] > $maxSize) {
        $sizeMB = $maxSize / (1024 * 1024);
        $result['error'] = "El archivo excede el tamaño máximo permitido ({$sizeMB}MB).";
        return $result;
    }
    
    // Validar extensión
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        $result['error'] = 'Extensión de archivo no permitida. Permitidas: ' . implode(', ', $allowedExts);
        return $result;
    }
    
    // Crear directorio si no existe
    $fullDir = UPLOADS_DIR . $destDir;
    if (!is_dir($fullDir)) {
        mkdir($fullDir, 0755, true);
    }
    
    // Generar nombre único
    $fileName = uniqid() . '_' . time() . '.' . $ext;
    $fullPath = $fullDir . '/' . $fileName;
    $relativePath = 'uploads/' . $destDir . '/' . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        $result['success'] = true;
        $result['path'] = $relativePath;
    } else {
        $result['error'] = 'No se pudo guardar el archivo.';
    }
    
    return $result;
}

/**
 * Genera un token CSRF y lo guarda en sesión
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida un token CSRF
 */
function validateCsrfToken(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Valida que una contraseña sea segura (mínimo 8 caracteres, al menos una letra y un número)
 */
function validarPassword(string $password): bool {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Za-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    return true;
}
