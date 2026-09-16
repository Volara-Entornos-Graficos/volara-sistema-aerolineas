-- VOLARA - Esquema de base de datos
-- Trabajo Práctico Entornos Gráficos UTN FR Rosario

CREATE DATABASE IF NOT EXISTS volara_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE volara_db;

-- ─── Usuarios ───────────────────────────────────────────────
CREATE TABLE usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(80)  NOT NULL,
    apellido        VARCHAR(80)  NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    rol             ENUM('admin','ceo','pasajero') NOT NULL DEFAULT 'pasajero',
    telefono        VARCHAR(30)  DEFAULT NULL,
    documento       VARCHAR(20)  DEFAULT NULL,
    aerolinea_id    INT UNSIGNED DEFAULT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    email_verificado TINYINT(1)   NOT NULL DEFAULT 0,
    estado_aprobacion ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'aprobado',
    token_activacion VARCHAR(100) DEFAULT NULL,
    token_activacion_expira DATETIME DEFAULT NULL,
    token_reset     VARCHAR(100) DEFAULT NULL,
    token_expira    DATETIME     DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuario_token_activacion (token_activacion),
    KEY idx_usuario_rol_activo (rol, activo),
    KEY idx_usuario_aerolinea_rol (aerolinea_id, rol)
) ENGINE=InnoDB;

-- ─── Aerolíneas ─────────────────────────────────────────────
CREATE TABLE aerolineas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(10)  NOT NULL UNIQUE,
    nombre          VARCHAR(120) NOT NULL,
    descripcion     TEXT         DEFAULT NULL,
    logo            VARCHAR(255) DEFAULT NULL,
    estado          ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_aerolinea_estado (estado)
) ENGINE=InnoDB;

ALTER TABLE usuarios
    ADD CONSTRAINT fk_usuario_aerolinea
    FOREIGN KEY (aerolinea_id) REFERENCES aerolineas(id) ON DELETE SET NULL;

-- ─── Vuelos ─────────────────────────────────────────────────
CREATE TABLE vuelos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(15)  NOT NULL UNIQUE,
    aerolinea_id        INT UNSIGNED NOT NULL,
    origen              VARCHAR(80)  NOT NULL,
    origen_codigo       VARCHAR(5)   NOT NULL,
    destino             VARCHAR(80)  NOT NULL,
    destino_codigo      VARCHAR(5)   NOT NULL,
    fecha_salida        DATETIME     NOT NULL,
    fecha_llegada       DATETIME     NOT NULL,
    precio              DECIMAL(10,2) NOT NULL,
    asientos_total      INT UNSIGNED NOT NULL,
    asientos_disponibles INT UNSIGNED NOT NULL,
    clase               ENUM('economica','premium','business') NOT NULL DEFAULT 'economica',
    avion_modelo        VARCHAR(80)  DEFAULT NULL,
    avion_distancia     VARCHAR(50)  DEFAULT NULL,
    avion_velocidad     VARCHAR(50)  DEFAULT NULL,
    estado              ENUM('programado','cancelado','completado') NOT NULL DEFAULT 'programado',
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_vuelo_busqueda (origen_codigo, destino_codigo, fecha_salida),
    KEY idx_vuelo_estado_fecha (estado, fecha_salida),
    KEY idx_vuelo_aerolinea_estado (aerolinea_id, estado),
    CONSTRAINT fk_vuelo_aerolinea FOREIGN KEY (aerolinea_id) REFERENCES aerolineas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Asientos ───────────────────────────────────────────────
CREATE TABLE asientos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vuelo_id    INT UNSIGNED NOT NULL,
    fila        INT UNSIGNED NOT NULL,
    columna     CHAR(1)      NOT NULL,
    clase       ENUM('economica','premium','business') NOT NULL DEFAULT 'economica',
    estado      ENUM('disponible','ocupado','bloqueado') NOT NULL DEFAULT 'disponible',
    UNIQUE KEY uk_asiento_vuelo (vuelo_id, fila, columna),
    KEY idx_asiento_estado (vuelo_id, estado),
    CONSTRAINT fk_asiento_vuelo FOREIGN KEY (vuelo_id) REFERENCES vuelos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Promociones ────────────────────────────────────────────
CREATE TABLE promociones (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aerolinea_id        INT UNSIGNED NOT NULL,
    titulo              VARCHAR(120) NOT NULL,
    descripcion         TEXT         DEFAULT NULL,
    descuento_porcentaje DECIMAL(5,2) NOT NULL,
    estado              ENUM('pendiente','aprobada','denegada','vigente','vencida') NOT NULL DEFAULT 'pendiente',
    fecha_inicio        DATE         DEFAULT NULL,
    fecha_fin           DATE         DEFAULT NULL,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_promo_aerolinea_estado (aerolinea_id, estado),
    KEY idx_promo_vigencia (estado, fecha_inicio, fecha_fin),
    CONSTRAINT fk_promo_aerolinea FOREIGN KEY (aerolinea_id) REFERENCES aerolineas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Reservas ───────────────────────────────────────────────
CREATE TABLE reservas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(20)  NOT NULL UNIQUE,
    usuario_id      INT UNSIGNED NOT NULL,
    vuelo_id        INT UNSIGNED NOT NULL,
    asiento_id      INT UNSIGNED DEFAULT NULL,
    asiento_label   VARCHAR(10)  DEFAULT NULL,
    estado          ENUM('pendiente_pago','confirmada','cancelada') NOT NULL DEFAULT 'pendiente_pago',
    precio_original DECIMAL(10,2) NOT NULL,
    descuento       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_final    DECIMAL(10,2) NOT NULL,
    promocion_id    INT UNSIGNED DEFAULT NULL,
    fecha_reserva   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_pago      DATETIME     DEFAULT NULL,
    fecha_cancelacion DATETIME   DEFAULT NULL,
    KEY idx_reserva_usuario_estado (usuario_id, estado),
    KEY idx_reserva_vuelo_estado (vuelo_id, estado),
    KEY idx_reserva_fecha (fecha_reserva),
    CONSTRAINT fk_reserva_usuario   FOREIGN KEY (usuario_id)   REFERENCES usuarios(id),
    CONSTRAINT fk_reserva_vuelo     FOREIGN KEY (vuelo_id)     REFERENCES vuelos(id),
    CONSTRAINT fk_reserva_asiento   FOREIGN KEY (asiento_id)   REFERENCES asientos(id) ON DELETE SET NULL,
    CONSTRAINT fk_reserva_promocion  FOREIGN KEY (promocion_id) REFERENCES promociones(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─── Novedades ──────────────────────────────────────────────
CREATE TABLE novedades (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(200) NOT NULL,
    contenido   TEXT         NOT NULL,
    imagen      VARCHAR(255) DEFAULT NULL,
    activa      TINYINT(1)   NOT NULL DEFAULT 1,
    fecha_inicio DATE         DEFAULT NULL,
    fecha_expiracion DATE     DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_novedad_publicacion (activa, fecha_inicio, fecha_expiracion),
    KEY idx_novedad_fecha (created_at)
) ENGINE=InnoDB;

-- ─── Datos iniciales ────────────────────────────────────────
INSERT INTO aerolineas (codigo, nombre, descripcion, estado) VALUES
('VOL', 'Volara Airways', 'Aerolínea principal del sistema VOLARA.', 'activa'),
('AR',  'Aerolíneas Argentinas', 'La aerolínea de bandera de Argentina.', 'activa'),
('LA',  'LATAM Airlines', 'Conectando Sudamérica con el mundo.', 'activa');

INSERT INTO usuarios (nombre, apellido, email, password, rol, email_verificado) VALUES
('Admin', 'Sistema', 'admin@volara.com', '$2y$10$92IXUNpkj0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

INSERT INTO usuarios (nombre, apellido, email, password, rol, aerolinea_id, email_verificado) VALUES
('Carlos', 'Mendoza', 'ceo@volara.com', '$2y$10$92IXUNpkj0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ceo', 1, 1);

INSERT INTO usuarios (nombre, apellido, email, password, rol, email_verificado) VALUES
('María', 'González', 'maria@email.com', '$2y$10$92IXUNpkj0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pasajero', 1);

INSERT INTO vuelos (codigo, aerolinea_id, origen, origen_codigo, destino, destino_codigo,
    fecha_salida, fecha_llegada, precio, asientos_total, asientos_disponibles, clase, avion_modelo, avion_distancia, avion_velocidad) VALUES
('VOL101', 1, 'Rosario', 'ROS', 'Buenos Aires', 'AEP', '2026-09-15 08:00:00', '2026-09-15 09:15:00', 45000.00, 30, 28, 'economica', 'Airbus A320', '300 km', '840 km/h'),
('VOL102', 1, 'Buenos Aires', 'AEP', 'Rosario', 'ROS', '2026-09-15 18:00:00', '2026-09-15 19:15:00', 42000.00, 30, 30, 'economica', 'Airbus A320', '300 km', '840 km/h'),
('VOL201', 1, 'Rosario', 'ROS', 'Córdoba', 'COR', '2026-09-20 10:30:00', '2026-09-20 11:45:00', 38000.00, 24, 24, 'economica', 'Boeing 737', '400 km', '820 km/h'),
('VOL301', 1, 'Rosario', 'ROS', 'Madrid', 'MAD', '2026-10-01 22:00:00', '2026-10-02 14:30:00', 850000.00, 20, 18, 'business', 'Airbus A350', '10.200 km', '900 km/h'),
('AR1501', 2, 'Buenos Aires', 'EZE', 'Bariloche', 'BRC', '2026-09-18 07:00:00', '2026-09-18 09:30:00', 95000.00, 36, 32, 'premium', 'Boeing 737 MAX', '1.600 km', '830 km/h');

INSERT INTO promociones (aerolinea_id, titulo, descripcion, descuento_porcentaje, estado, fecha_inicio, fecha_fin) VALUES
(1, 'Verano VOLARA', '15% de descuento en vuelos nacionales.', 15.00, 'vigente', '2026-06-01', '2026-12-31'),
(2, 'Patagonia Express', '10% off en vuelos a Bariloche.', 10.00, 'pendiente', '2026-09-01', '2026-11-30');

INSERT INTO novedades (titulo, contenido, activa) VALUES
('Nueva ruta Rosario – Madrid', 'Volara inaugura su primera ruta intercontinental con vuelos directos a Madrid desde octubre 2026.', 1),
('Promoción de verano', 'Aprovechá un 15% de descuento en todos los vuelos nacionales de Volara Airways.', 1),
('Mejoras en la plataforma', 'Renovamos la experiencia de reserva con selección gráfica de asientos.', 1);
