-- ==========================================
-- SCHEMA DE BASE DE DATOS - MESA DE AYUDA
-- ==========================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS mesa_ayuda
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE mesa_ayuda;

-- ==========================================
-- TABLA: usuarios
-- ==========================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'tecnico', 'usuario') DEFAULT 'usuario',
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: categorias
-- ==========================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    color VARCHAR(7) DEFAULT '#6366f1',
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: tickets
-- ==========================================
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    prioridad ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
    estado ENUM('abierto', 'en_progreso', 'resuelto', 'cerrado') DEFAULT 'abierto',
    categoria_id INT,
    usuario_id INT NOT NULL,
    tecnico_id INT DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_estado (estado),
    INDEX idx_prioridad (prioridad),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tecnico (tecnico_id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_fecha_creacion (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: comentarios
-- ==========================================
CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    contenido TEXT NOT NULL,
    es_interno BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: adjuntos
-- ==========================================
CREATE TABLE IF NOT EXISTS adjuntos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tipo_mime VARCHAR(100),
    tamanio INT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: historial_tickets
-- ==========================================
CREATE TABLE IF NOT EXISTS historial_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    valor_anterior TEXT,
    valor_nuevo TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- DATOS INICIALES
-- ==========================================

-- Insertar categorías por defecto
INSERT INTO categorias (nombre, descripcion, color) VALUES
('Hardware', 'Problemas relacionados con equipos físicos', '#ef4444'),
('Software', 'Problemas con aplicaciones y sistemas operativos', '#3b82f6'),
('Red', 'Problemas de conectividad y red', '#10b981'),
('Acceso', 'Problemas de acceso y permisos', '#f59e0b'),
('Otro', 'Otras consultas generales', '#6b7280')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Insertar usuario administrador por defecto
-- Password: admin123 (cambiar en producción)
INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES
('Admin', 'Sistema', 'admin@mesadeayuda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Insertar usuario técnico de prueba
-- Password: tecnico123
INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES
('Juan', 'Pérez', 'tecnico@mesadeayuda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Insertar usuario normal de prueba
-- Password: usuario123
INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES
('María', 'González', 'usuario@mesadeayuda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- ==========================================
-- VISTAS ÚTILES
-- ==========================================

-- Vista de tickets con información completa
CREATE OR REPLACE VIEW vista_tickets_completa AS
SELECT 
    t.id,
    t.titulo,
    t.descripcion,
    t.prioridad,
    t.estado,
    c.nombre AS categoria,
    c.color AS categoria_color,
    CONCAT(u.nombre, ' ', u.apellido) AS usuario,
    u.email AS usuario_email,
    CONCAT(tec.nombre, ' ', tec.apellido) AS tecnico,
    t.fecha_creacion,
    t.fecha_actualizacion,
    t.fecha_cierre,
    DATEDIFF(COALESCE(t.fecha_cierre, NOW()), t.fecha_creacion) AS dias_abierto
FROM tickets t
LEFT JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN usuarios tec ON t.tecnico_id = tec.id
LEFT JOIN categorias c ON t.categoria_id = c.id;

-- Vista de estadísticas por usuario
CREATE OR REPLACE VIEW vista_estadisticas_usuario AS
SELECT 
    u.id,
    CONCAT(u.nombre, ' ', u.apellido) AS usuario,
    u.rol,
    COUNT(DISTINCT t.id) AS total_tickets,
    COUNT(DISTINCT CASE WHEN t.estado = 'abierto' THEN t.id END) AS tickets_abiertos,
    COUNT(DISTINCT CASE WHEN t.estado = 'en_progreso' THEN t.id END) AS tickets_en_progreso,
    COUNT(DISTINCT CASE WHEN t.estado = 'resuelto' THEN t.id END) AS tickets_resueltos,
    COUNT(DISTINCT CASE WHEN t.estado = 'cerrado' THEN t.id END) AS tickets_cerrados
FROM usuarios u
LEFT JOIN tickets t ON u.id = t.usuario_id
GROUP BY u.id;

-- ==========================================
-- TRIGGERS
-- ==========================================

DELIMITER //

-- Trigger para registrar cambios en el historial
CREATE TRIGGER after_ticket_update
AFTER UPDATE ON tickets
FOR EACH ROW
BEGIN
    IF OLD.estado != NEW.estado THEN
        INSERT INTO historial_tickets (ticket_id, usuario_id, accion, valor_anterior, valor_nuevo)
        VALUES (NEW.id, NEW.tecnico_id, 'cambio_estado', OLD.estado, NEW.estado);
    END IF;
    
    IF OLD.prioridad != NEW.prioridad THEN
        INSERT INTO historial_tickets (ticket_id, usuario_id, accion, valor_anterior, valor_nuevo)
        VALUES (NEW.id, NEW.tecnico_id, 'cambio_prioridad', OLD.prioridad, NEW.prioridad);
    END IF;
    
    IF OLD.tecnico_id != NEW.tecnico_id OR (OLD.tecnico_id IS NULL AND NEW.tecnico_id IS NOT NULL) THEN
        INSERT INTO historial_tickets (ticket_id, usuario_id, accion, valor_anterior, valor_nuevo)
        VALUES (NEW.id, NEW.tecnico_id, 'asignacion', OLD.tecnico_id, NEW.tecnico_id);
    END IF;
END//

-- Trigger para actualizar fecha de cierre
CREATE TRIGGER before_ticket_close
BEFORE UPDATE ON tickets
FOR EACH ROW
BEGIN
    IF NEW.estado = 'cerrado' AND OLD.estado != 'cerrado' THEN
        SET NEW.fecha_cierre = NOW();
    END IF;
END//

DELIMITER ;

-- ==========================================
-- PROCEDIMIENTOS ALMACENADOS
-- ==========================================

DELIMITER //

-- Procedimiento para obtener estadísticas generales
CREATE PROCEDURE obtener_estadisticas_generales()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM tickets) AS total_tickets,
        (SELECT COUNT(*) FROM tickets WHERE estado = 'abierto') AS tickets_abiertos,
        (SELECT COUNT(*) FROM tickets WHERE estado = 'en_progreso') AS tickets_en_progreso,
        (SELECT COUNT(*) FROM tickets WHERE estado = 'resuelto') AS tickets_resueltos,
        (SELECT COUNT(*) FROM tickets WHERE estado = 'cerrado') AS tickets_cerrados,
        (SELECT COUNT(*) FROM usuarios WHERE activo = TRUE) AS usuarios_activos,
        (SELECT AVG(DATEDIFF(fecha_cierre, fecha_creacion)) 
         FROM tickets 
         WHERE fecha_cierre IS NOT NULL) AS tiempo_promedio_resolucion;
END//

DELIMITER ;

-- ==========================================
-- PERMISOS (opcional, ajustar según necesidad)
-- ==========================================

-- GRANT SELECT, INSERT, UPDATE, DELETE ON mesa_ayuda.* TO 'mesa_ayuda_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ==========================================
-- ÍNDICES ADICIONALES PARA RENDIMIENTO
-- ==========================================

-- Índice compuesto para búsquedas frecuentes
CREATE INDEX idx_tickets_estado_prioridad ON tickets(estado, prioridad);
CREATE INDEX idx_tickets_usuario_estado ON tickets(usuario_id, estado);
CREATE INDEX idx_tickets_tecnico_estado ON tickets(tecnico_id, estado);

-- ==========================================
-- FIN DEL SCHEMA
-- ==========================================

SELECT '✅ Base de datos creada exitosamente' AS mensaje;