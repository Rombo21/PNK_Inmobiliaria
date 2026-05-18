-- =====================================================
-- PNK INMOBILIARIA — Base de Datos Completa
-- Motor: MySQL 8.x
-- Codificación: UTF-8 (utf8mb4)
-- =====================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = 'utf8mb4_unicode_ci';

DROP DATABASE IF EXISTS pnk_inmobiliaria;
CREATE DATABASE pnk_inmobiliaria
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE pnk_inmobiliaria;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
CREATE TABLE usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    rut             VARCHAR(12) NOT NULL UNIQUE,
    nombre_completo VARCHAR(150) NOT NULL,
    fecha_nacimiento DATE NULL,
    correo          VARCHAR(150) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    sexo            ENUM('M','F') NULL,
    telefono        VARCHAR(20) NULL,
    tipo_usuario    ENUM('administrador','propietario','gestor') NOT NULL DEFAULT 'propietario',
    estado          ENUM('pendiente','activo','inactivo') NOT NULL DEFAULT 'pendiente',
    penka_id        VARCHAR(20) NULL COMMENT 'Solo para gestores, formato PNK-YYYY-XXXX',
    certificado_antecedentes VARCHAR(255) NULL COMMENT 'Ruta del archivo PDF/imagen',
    num_propiedad_bbr VARCHAR(50) NULL COMMENT 'Nro. propiedad Bienes Raíces (propietarios)',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo_usuario (tipo_usuario),
    INDEX idx_estado (estado),
    INDEX idx_correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: propiedades
-- =====================================================
CREATE TABLE propiedades (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    codigo           VARCHAR(20) NOT NULL UNIQUE COMMENT 'Formato: C/D/T + año + correlativo',
    propietario_id   INT NOT NULL,
    tipo             ENUM('casa','departamento','terreno') NOT NULL,
    descripcion      TEXT NULL,
    banos            TINYINT UNSIGNED DEFAULT 0,
    dormitorios      TINYINT UNSIGNED DEFAULT 0,
    area_terreno     DECIMAL(10,2) DEFAULT 0 COMMENT 'Metros cuadrados',
    area_construida  DECIMAL(10,2) DEFAULT 0 COMMENT 'Metros cuadrados',
    precio_clp       BIGINT UNSIGNED DEFAULT 0,
    precio_uf        DECIMAL(10,2) DEFAULT 0,
    fecha_publicacion DATE NULL,
    provincia        VARCHAR(50) NOT NULL,
    comuna           VARCHAR(50) NOT NULL,
    sector           VARCHAR(100) NULL,
    latitud          DECIMAL(10,7) NULL,
    longitud         DECIMAL(10,7) NULL,
    bodega           BOOLEAN DEFAULT FALSE,
    estacionamiento  BOOLEAN DEFAULT FALSE,
    logia            BOOLEAN DEFAULT FALSE,
    cocina_amoblada  BOOLEAN DEFAULT FALSE,
    antejardin       BOOLEAN DEFAULT FALSE,
    patio_trasero    BOOLEAN DEFAULT FALSE,
    piscina          BOOLEAN DEFAULT FALSE,
    estado           ENUM('activo','inactivo','vendido') NOT NULL DEFAULT 'activo',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (propietario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado),
    INDEX idx_comuna (comuna),
    INDEX idx_provincia (provincia),
    INDEX idx_propietario (propietario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: fotos_propiedad
-- =====================================================
CREATE TABLE fotos_propiedad (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    propiedad_id  INT NOT NULL,
    ruta_imagen   VARCHAR(255) NOT NULL,
    orden         TINYINT UNSIGNED DEFAULT 1 COMMENT 'Orden 1-10',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    INDEX idx_propiedad (propiedad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: visitas
-- =====================================================
CREATE TABLE visitas (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    propiedad_id      INT NOT NULL,
    nombre_visitante  VARCHAR(150) NOT NULL,
    correo            VARCHAR(150) NOT NULL,
    telefono          VARCHAR(20) NULL,
    fecha_solicitada  DATE NOT NULL,
    mensaje           TEXT NULL,
    estado            ENUM('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    INDEX idx_propiedad_visita (propiedad_id),
    INDEX idx_estado_visita (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: gestiones
-- =====================================================
CREATE TABLE gestiones (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    gestor_id     INT NOT NULL,
    propiedad_id  INT NOT NULL,
    estado        ENUM('pendiente','activo','finalizado') NOT NULL DEFAULT 'pendiente',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gestor_id)    REFERENCES usuarios(id)    ON DELETE CASCADE,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    INDEX idx_gestor (gestor_id),
    INDEX idx_propiedad_gestion (propiedad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE PRUEBA
-- =====================================================

-- ————————————————————————————————————————
-- 1. USUARIOS
-- ————————————————————————————————————————
-- Password para todos los usuarios de prueba: Admin123!
-- Hash generado con password_hash('Admin123!', PASSWORD_BCRYPT)

INSERT INTO usuarios (rut, nombre_completo, fecha_nacimiento, correo, password, sexo, telefono, tipo_usuario, estado, penka_id, certificado_antecedentes, num_propiedad_bbr) VALUES
-- Administrador
('11111111-1', 'Administrador PNK', '1985-03-15', 'admin@pnkinmobiliaria.cl',
'$2y$10$sEewgfGbX9AOcnDyfI/bSOZkE6fwg58VB20HaGGaxdHFqwJ0k9iGa',
'M', '+56912345678', 'administrador', 'activo', NULL, NULL, NULL),

-- Propietarios activos
('12345678-9', 'María González Rojas', '1978-07-22', 'maria.gonzalez@email.cl',
'$2y$10$sEewgfGbX9AOcnDyfI/bSOZkE6fwg58VB20HaGGaxdHFqwJ0k9iGa',
'F', '+56987654321', 'propietario', 'activo', NULL, NULL, 'BR-45231'),

('15678432-1', 'Carlos Muñoz Tapia', '1982-11-03', 'carlos.munoz@email.cl',
'$2y$10$sEewgfGbX9AOcnDyfI/bSOZkE6fwg58VB20HaGGaxdHFqwJ0k9iGa',
'M', '+56976543210', 'propietario', 'activo', NULL, NULL, 'BR-78123'),

-- Propietario pendiente
('18234567-8', 'Ana Castillo Herrera', '1990-05-10', 'ana.castillo@email.cl',
'$2y$10$sEewgfGbX9AOcnDyfI/bSOZkE6fwg58VB20HaGGaxdHFqwJ0k9iGa',
'F', '+56965432109', 'propietario', 'pendiente', NULL, NULL, 'BR-99012'),

-- Gestor activo con PENKA_ID
('16789012-3', 'Roberto Pizarro Leiva', '1988-01-25', 'roberto.pizarro@email.cl',
'$2y$10$sEewgfGbX9AOcnDyfI/bSOZkE6fwg58VB20HaGGaxdHFqwJ0k9iGa',
'M', '+56954321098', 'gestor', 'activo', 'PNK-2025-0001', 'uploads/certificados/cert_roberto.pdf', NULL),

-- Gestor pendiente
('17890123-4', 'Fernanda Soto Mena', '1995-09-18', 'fernanda.soto@email.cl',
'$2y$10$sEewgfGbX9AOcnDyfI/bSOZkE6fwg58VB20HaGGaxdHFqwJ0k9iGa',
'F', '+56943210987', 'gestor', 'pendiente', NULL, 'uploads/certificados/cert_fernanda.pdf', NULL);

-- ————————————————————————————————————————
-- 2. PROPIEDADES (6 propiedades realistas)
-- ————————————————————————————————————————

INSERT INTO propiedades (codigo, propietario_id, tipo, descripcion, banos, dormitorios, area_terreno, area_construida, precio_clp, precio_uf, fecha_publicacion, provincia, comuna, sector, latitud, longitud, bodega, estacionamiento, logia, cocina_amoblada, antejardin, patio_trasero, piscina, estado) VALUES
-- Propiedad 1: Casa en La Serena
('C2025-0001', 2, 'casa',
'Hermosa casa de 2 pisos ubicada en el sector El Milagro, La Serena. Cuenta con amplios espacios, terminaciones de primer nivel, cocina americana equipada, living-comedor con salida a terraza y vista al cerro. Jardín delantero y trasero con riego automático. Estacionamiento para 2 vehículos.',
3, 4, 250.00, 180.00, 154000000, 4158.00, '2025-01-15', 'Elqui', 'La Serena', 'El Milagro',
-29.9027, -71.2519, TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, FALSE, 'activo'),

-- Propiedad 2: Departamento en Coquimbo
('D2025-0002', 2, 'departamento',
'Moderno departamento en condominio Santa Margarita, sector La Herradura, Coquimbo. Vista parcial al mar, 2 dormitorios, 1 baño, logia, estacionamiento subterráneo y bodega. Edificio con conserje 24/7, áreas verdes y juegos infantiles.',
1, 2, 0, 65.00, 89000000, 2405.00, '2025-02-20', 'Elqui', 'Coquimbo', 'La Herradura',
-29.9700, -71.3400, TRUE, TRUE, TRUE, FALSE, FALSE, FALSE, FALSE, 'activo'),

-- Propiedad 3: Terreno en Ovalle
('T2025-0003', 3, 'terreno',
'Amplio terreno plano en sector residencial de Ovalle, ideal para proyecto habitacional o casa familiar. Cuenta con factibilidad de agua, luz y alcantarillado. Acceso pavimentado, cercano a colegios y comercio. Excelente plusvalía.',
0, 0, 500.00, 0, 45000000, 1216.00, '2025-03-10', 'Limarí', 'Ovalle', 'Centro',
-30.5983, -71.1990, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, 'activo'),

-- Propiedad 4: Casa en Coquimbo
('C2025-0004', 3, 'casa',
'Casa sólida de un piso en Tierras Blancas, Coquimbo. 3 dormitorios, 2 baños completos, cocina amoblada, living-comedor espacioso. Patio trasero de gran tamaño con quincho y piscina. Estacionamiento techado para 1 vehículo. Barrio tranquilo.',
2, 3, 200.00, 120.00, 105000000, 2838.00, '2025-04-05', 'Elqui', 'Coquimbo', 'Tierras Blancas',
-29.9300, -71.2800, FALSE, TRUE, FALSE, TRUE, TRUE, TRUE, TRUE, 'activo'),

-- Propiedad 5: Parcela en La Serena
('T2025-0005', 2, 'terreno',
'Parcela de 5000 m² en sector Las Compañías, La Serena. Terreno semiplano con excelente conectividad a Ruta 5 Norte. Ideal para proyecto agrícola o condominio. Cuenta con pozo de agua y cerco perimetral.',
0, 0, 5000.00, 0, 120000000, 3243.00, '2025-05-01', 'Elqui', 'La Serena', 'Las Compañías',
-29.8800, -71.2700, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, 'activo'),

-- Propiedad 6: Departamento en La Serena
('D2025-0006', 3, 'departamento',
'Departamento amoblado en pleno centro de La Serena, a pasos de la Plaza de Armas. 1 dormitorio, living-comedor, cocina equipada, baño completo. Edificio con ascensor y estacionamiento de visitas. Ideal para inversión o arriendo.',
1, 1, 0, 42.00, 62000000, 1675.00, '2025-05-10', 'Elqui', 'La Serena', 'Centro',
-29.9060, -71.2520, FALSE, FALSE, FALSE, TRUE, FALSE, FALSE, FALSE, 'activo');

-- ————————————————————————————————————————
-- 3. FOTOS DE PROPIEDADES
-- ————————————————————————————————————————

INSERT INTO fotos_propiedad (propiedad_id, ruta_imagen, orden) VALUES
-- Fotos Propiedad 1 (Casa El Milagro)
(1, 'img/casa_destacada_1/Casadestacada1.webp', 1),
(1, 'img/casa_destacada_1/Foto-6899f6ff11a8a1.webp', 2),
(1, 'img/casa_destacada_1/Foto-6899f6ff96b9b7.webp', 3),
(1, 'img/casa_destacada_1/Foto-6899f70033ef89.webp', 4),
(1, 'img/casa_destacada_1/Foto-6899f700bf6ad8.webp', 5),

-- Fotos Propiedad 2 (Depto La Herradura)
(2, 'img/departamento_destacado_1/Fachadasantamargarita-681a1d40816728.webp', 1),
(2, 'img/departamento_destacado_1/w-686e56a44df500.webp', 2),
(2, 'img/departamento_destacado_1/w-686e56a73c5243.webp', 3),
(2, 'img/departamento_destacado_1/w-686e56a9e16b61.webp', 4),

-- Fotos Propiedad 3 (Terreno Ovalle)
(3, 'img/parcela_destacada_1_ls/porton.webp', 1),
(3, 'img/parcela_destacada_1_ls/fachada.webp', 2),
(3, 'img/parcela_destacada_1_ls/comedor.webp', 3),

-- Fotos Propiedad 4 (Casa Coquimbo/Limarí)
(4, 'img/casa_destacada_limari/fachada.webp', 1),
(4, 'img/casa_destacada_limari/comedor living.webp', 2),
(4, 'img/casa_destacada_limari/dormitorio grande.webp', 3),
(4, 'img/casa_destacada_limari/terreno.webp', 4),

-- Fotos Propiedad 5 (Parcela Las Compañías)
(5, 'img/parcela_destacada_1_ls/porton.webp', 1),
(5, 'img/parcela_destacada_1_ls/fachada.webp', 2),

-- Fotos Propiedad 6 (Depto Centro La Serena)
(6, 'img/departamento_destacado_1/Fachadasantamargarita-681a1d40816728.webp', 1),
(6, 'img/departamento_destacado_1/w-686e56a44df500.webp', 2);

-- ————————————————————————————————————————
-- 4. VISITAS DE PRUEBA
-- ————————————————————————————————————————

INSERT INTO visitas (propiedad_id, nombre_visitante, correo, telefono, fecha_solicitada, mensaje, estado) VALUES
(1, 'Pedro Álvarez', 'pedro.alvarez@email.cl', '+56912341234', '2025-06-01', 'Me interesa la propiedad, quisiera coordinar una visita presencial.', 'pendiente'),
(2, 'Laura Mendoza', 'laura.mendoza@email.cl', '+56998765432', '2025-06-05', 'Busco departamento para arriendo, me gustaría ver este.', 'confirmada');

-- ————————————————————————————————————————
-- 5. GESTIONES DE PRUEBA
-- ————————————————————————————————————————

INSERT INTO gestiones (gestor_id, propiedad_id, estado) VALUES
(5, 1, 'activo'),
(5, 4, 'pendiente');
