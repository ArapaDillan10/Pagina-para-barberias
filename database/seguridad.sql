-- =========================================================
-- DKBZA - Seguridad del Panel Admin
-- Ejecutar en phpMyAdmin (pestaña SQL) con la base dkbza_db creada.
-- =========================================================

USE dkbza_db;

-- Usuarios del panel. La contraseña se guarda cifrada con password_hash().
CREATE TABLE IF NOT EXISTS usuarios (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  usuario        VARCHAR(50) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  estado         TINYINT NOT NULL DEFAULT 1,
  ultimo_acceso  TIMESTAMP NULL DEFAULT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Intentos fallidos de login, para frenar ataques de fuerza bruta.
CREATE TABLE IF NOT EXISTS login_intentos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  ip          VARCHAR(45) NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_fecha (ip, created_at)
) ENGINE=InnoDB;

-- Usuario de MySQL con permisos mínimos para el sitio (no usar root).
-- Cambiá 'CAMBIAR_ESTA_CLAVE' por una contraseña propia y poné la misma en DB_PASS del .env
CREATE USER IF NOT EXISTS 'dkbza_app'@'localhost' IDENTIFIED BY 'CAMBIAR_ESTA_CLAVE';
CREATE USER IF NOT EXISTS 'dkbza_app'@'127.0.0.1' IDENTIFIED BY 'CAMBIAR_ESTA_CLAVE';

GRANT SELECT, INSERT, UPDATE, DELETE ON dkbza_db.* TO 'dkbza_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dkbza_db.* TO 'dkbza_app'@'127.0.0.1';

FLUSH PRIVILEGES;
