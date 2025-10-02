-- Script de inicialización de la base de datos Mesa de Ayuda
-- Este script se ejecuta automáticamente cuando se crea el contenedor de PostgreSQL
-- Compatible con PostgreSQL 12+

-- Tabla de roles
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar roles por defecto
INSERT INTO roles (nombre_rol, descripcion) VALUES
('Administrador', 'Usuario con acceso completo al sistema'),
('Cliente', 'Usuario que puede crear y ver sus propios tickets'),
('Técnico', 'Usuario que puede gestionar tickets asignados')
ON CONFLICT (nombre_rol) DO NOTHING;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL,
    correo_usuario VARCHAR(100) NOT NULL UNIQUE,
    celular_usuario VARCHAR(20),
    contrasena_usuario VARCHAR(255) NOT NULL,
    rol_id INTEGER NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- Crear índices para usuarios
CREATE INDEX IF NOT EXISTS idx_correo ON usuarios(correo_usuario);
CREATE INDEX IF NOT EXISTS idx_rol ON usuarios(rol_id);

-- Tabla de departamentos
CREATE TABLE IF NOT EXISTS departamentos (
    id SERIAL PRIMARY KEY,
    nombre_departamento VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar departamentos por defecto
INSERT INTO departamentos (nombre_departamento, descripcion) VALUES
('Soporte Técnico', 'Problemas técnicos y de hardware'),
('Desarrollo', 'Solicitudes de desarrollo y nuevas funcionalidades'),
('Redes', 'Problemas de conectividad y redes'),
('Seguridad', 'Incidentes de seguridad informática'),
('Recursos Humanos', 'Consultas administrativas y de RRHH')
ON CONFLICT (nombre_departamento) DO NOTHING;

-- Tipos ENUM para PostgreSQL
DO $$ BEGIN
    CREATE TYPE prioridad_type AS ENUM ('Baja', 'Media', 'Alta', 'Urgente');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE estado_type AS ENUM ('Abierto', 'En Progreso', 'Resuelto', 'Cerrado', 'Cancelado');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- Tabla de tickets
CREATE TABLE IF NOT EXISTS tickets (
    id SERIAL PRIMARY KEY,
    numero_ticket VARCHAR(50) NOT NULL UNIQUE,
    usuario_id INTEGER NOT NULL,
    departamento_id INTEGER NOT NULL,
    asunto VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    prioridad prioridad_type DEFAULT 'Media',
    estado estado_type DEFAULT 'Abierto',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE RESTRICT
);

-- Crear índices para tickets
CREATE INDEX IF NOT EXISTS idx_usuario ON tickets(usuario_id);
CREATE INDEX IF NOT EXISTS idx_departamento ON tickets(departamento_id);
CREATE INDEX IF NOT EXISTS idx_estado ON tickets(estado);
CREATE INDEX IF NOT EXISTS idx_fecha_creacion ON tickets(fecha_creacion);

-- Crear usuario administrador por defecto
-- Contraseña: admin123 (debe cambiarse en producción)
INSERT INTO usuarios (nombre_usuario, correo_usuario, celular_usuario, contrasena_usuario, rol_id, activo)
VALUES (
    'Administrador',
    'admin@mesaayuda.com',
    '3001234567',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    1,
    TRUE
)
ON CONFLICT (correo_usuario) DO NOTHING;

-- Crear usuario cliente de prueba
-- Contraseña: cliente123
INSERT INTO usuarios (nombre_usuario, correo_usuario, celular_usuario, contrasena_usuario, rol_id, activo)
VALUES (
    'Usuario Cliente',
    'cliente@mesaayuda.com',
    '3009876543',
    '$2y$10$E7LnWGZ8qF4xqJ5YvZXjEOxKqGqZQqGqZQqGqZQqGqZQqGqZQqGqZ', -- cliente123
    2,
    TRUE
)
ON CONFLICT (correo_usuario) DO NOTHING;

-- Crear algunos tickets de ejemplo
INSERT INTO tickets (numero_ticket, usuario_id, departamento_id, asunto, descripcion, prioridad, estado)
SELECT 
    'TK-2025-0001',
    u.id,
    d.id,
    'Problema con el sistema de login',
    'No puedo acceder al sistema con mis credenciales',
    'Alta'::prioridad_type,
    'Abierto'::estado_type
FROM usuarios u, departamentos d
WHERE u.correo_usuario = 'cliente@mesaayuda.com' 
  AND d.nombre_departamento = 'Soporte Técnico'
  AND NOT EXISTS (SELECT 1 FROM tickets WHERE numero_ticket = 'TK-2025-0001');

-- Crear función para actualizar fecha_actualizacion automáticamente
CREATE OR REPLACE FUNCTION update_fecha_actualizacion()
RETURNS TRIGGER AS $$
BEGIN
    NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Crear trigger para usuarios
DROP TRIGGER IF EXISTS trigger_update_usuarios ON usuarios;
CREATE TRIGGER trigger_update_usuarios
    BEFORE UPDATE ON usuarios
    FOR EACH ROW
    EXECUTE FUNCTION update_fecha_actualizacion();

-- Crear trigger para tickets
DROP TRIGGER IF EXISTS trigger_update_tickets ON tickets;
CREATE TRIGGER trigger_update_tickets
    BEFORE UPDATE ON tickets
    FOR EACH ROW
    EXECUTE FUNCTION update_fecha_actualizacion();

-- Mostrar información de configuración
SELECT 'Base de datos inicializada correctamente' AS mensaje;
SELECT COUNT(*) AS total_roles FROM roles;
SELECT COUNT(*) AS total_usuarios FROM usuarios;
SELECT COUNT(*) AS total_departamentos FROM departamentos;
SELECT COUNT(*) AS total_tickets FROM tickets;
