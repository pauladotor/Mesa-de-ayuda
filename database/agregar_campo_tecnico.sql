-- Script para agregar el campo tecnico_asignado_id a la tabla tickets existente
-- Ejecutar este script en tu base de datos PostgreSQL

-- Agregar columna tecnico_asignado_id si no existe
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'tickets' AND column_name = 'tecnico_asignado_id'
    ) THEN
        ALTER TABLE tickets ADD COLUMN tecnico_asignado_id INTEGER;
        ALTER TABLE tickets ADD CONSTRAINT fk_tecnico_asignado 
            FOREIGN KEY (tecnico_asignado_id) REFERENCES usuarios(id) ON DELETE SET NULL;
        CREATE INDEX idx_tecnico ON tickets(tecnico_asignado_id);
    END IF;
END $$;

-- Agregar columna fecha_resolucion si no existe
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'tickets' AND column_name = 'fecha_resolucion'
    ) THEN
        ALTER TABLE tickets ADD COLUMN fecha_resolucion TIMESTAMP NULL;
    END IF;
END $$;

-- Verificar que las columnas se agregaron correctamente
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'tickets' 
ORDER BY ordinal_position;
