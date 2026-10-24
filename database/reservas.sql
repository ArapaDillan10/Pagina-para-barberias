-- =========================================================
-- Reservas reales: un turno cancelado deja libre su horario.
-- Ejecutar UNA vez en phpMyAdmin sobre dkbza_db (después de schema.sql).
-- =========================================================

USE dkbza_db;

-- ocupa = 1 si el turno está activo, NULL si está cancelado.
-- Como UNIQUE permite varios NULL, puede haber turnos cancelados en el mismo
-- horario, pero solo uno activo por barbero, fecha y hora.
ALTER TABLE turnos
  ADD COLUMN ocupa TINYINT AS (IF(estado = 'cancelado', NULL, 1)) STORED;

ALTER TABLE turnos
  ADD CONSTRAINT uq_turnos_horario_activo UNIQUE (barbero_id, fecha, hora, ocupa);

ALTER TABLE turnos
  DROP INDEX uq_turnos_barbero_horario;

-- WhatsApp del cliente para avisarle del turno
ALTER TABLE turnos
  ADD COLUMN telefono VARCHAR(30) NULL AFTER estado;

-- Seña: monto que pagó el cliente y ruta del comprobante (archivo privado)
ALTER TABLE turnos
  ADD COLUMN sena_monto DECIMAL(10,2) NULL AFTER telefono,
  ADD COLUMN sena_comprobante VARCHAR(255) NULL AFTER sena_monto;

-- =========================================================
-- Barberos con foto (se cargan desde el panel: admin_barberos.php)
-- =========================================================

ALTER TABLE barberos
  ADD COLUMN foto VARCHAR(255) NULL AFTER especialidad;

UPDATE barberos SET nombre = 'Dillan Arapa', especialidad = 'Master Barber & Founder'  WHERE id = 1;
UPDATE barberos SET nombre = 'Mateo Nieva',  especialidad = 'Fade Specialist & Senior' WHERE id = 2;
