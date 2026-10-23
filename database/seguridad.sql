-- =========================================================
-- DKBZA - Usuario de MySQL con permisos mínimos
-- Ejecutar en phpMyAdmin (pestaña SQL) después de schema.sql.
-- =========================================================

USE dkbza_db;

-- Las tablas usuarios y login_intentos se crean en schema.sql (TP6).

-- Usuario de MySQL con permisos mínimos para el sitio (no usar root).
-- Cambiá 'CAMBIAR_ESTA_CLAVE' por una contraseña propia y poné la misma en DB_PASS del .env
CREATE USER IF NOT EXISTS 'dkbza_app'@'localhost' IDENTIFIED BY 'CAMBIAR_ESTA_CLAVE';
CREATE USER IF NOT EXISTS 'dkbza_app'@'127.0.0.1' IDENTIFIED BY 'CAMBIAR_ESTA_CLAVE';

GRANT SELECT, INSERT, UPDATE, DELETE ON dkbza_db.* TO 'dkbza_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dkbza_db.* TO 'dkbza_app'@'127.0.0.1';

FLUSH PRIVILEGES;
