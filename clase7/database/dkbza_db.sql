CREATE DATABASE IF NOT EXISTS dkbza_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE dkbza_db;

CREATE TABLE servicios (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(100) NOT NULL,
  descripcion TEXT,
  precio      DECIMAL(10, 2) NOT NULL,
  imagen      VARCHAR(255),
  estado      TINYINT NOT NULL DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO servicios (nombre, descripcion, precio, imagen) VALUES
('Corte', 'Asesoramiento en referencia al tipo de rostro y eleccion del corte ideal.', 18000, 'corte1.png'),
('Barba Completa', 'Toalla caliente, perfilado con navaja y aceites esenciales.', 9000, 'corte2.png'),
('Combo DKBZA (Corte + Barba)', 'Servicio completo VIP con asesoramiento y bebida incluida a eleccion.', 25000, 'combo3.png');
