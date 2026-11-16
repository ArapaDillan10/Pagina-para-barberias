-- =========================================================
-- Verificación de email con PIN al registrarse.
-- La cuenta NO se crea en `usuarios` hasta que se confirma el PIN:
-- mientras tanto los datos esperan acá (nombre, email, hash de la clave y hash del PIN).
-- Las cuentas que ya existen (y el admin) no se tocan.
-- Sin FOREIGN KEY y en utf8mb4 para que funcione igual en el hosting.
-- Ejecutar UNA vez en phpMyAdmin (local y hosting) ANTES de subir los archivos.
-- =========================================================
CREATE TABLE IF NOT EXISTS registros_pendientes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  password      VARCHAR(255) NOT NULL,              -- hash de la contraseña elegida
  codigo        VARCHAR(255) NOT NULL,              -- hash del PIN de 6 dígitos, nunca el PIN real
  intentos      TINYINT      NOT NULL DEFAULT 0,    -- PIN mal puestos con el código actual
  envios        TINYINT      NOT NULL DEFAULT 1,    -- códigos mandados en la hora actual
  primer_envio  DATETIME     NOT NULL,              -- inicio de la ventana de 1 hora
  ultimo_envio  DATETIME     NOT NULL,              -- para esperar 60 s entre reenvíos
  expira_at     DATETIME     NOT NULL,              -- el PIN vence a los 15 minutos
  ip            VARCHAR(45)  NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_pendientes_email (email),
  INDEX idx_pendientes_ip (ip, created_at),
  INDEX idx_pendientes_expira (expira_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;