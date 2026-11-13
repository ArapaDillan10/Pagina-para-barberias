-- =========================================================
-- Mensajes del formulario de contacto (se ven en admin_mensajes.php).
-- Ejecutar UNA vez en phpMyAdmin con la base del sitio seleccionada.
-- =========================================================

CREATE TABLE IF NOT EXISTS mensajes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL,
  mensaje     TEXT         NOT NULL,
  leido       TINYINT      NOT NULL DEFAULT 0,
  ip          VARCHAR(45)  NULL,               -- para frenar el spam (máx. 3 por hora)
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_mensajes_leido (leido, created_at),
  INDEX idx_mensajes_ip (ip, created_at)
) ENGINE=InnoDB;