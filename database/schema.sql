-- =========================================================
-- DKBZA Barber Studio - Esquema relacional 
-- Crea la base dkbza_db con todas sus tablas, claves e índices.
-- Ejecutar en phpMyAdmin (pestaña SQL). ATENCIÓN: borra y vuelve
-- a crear las tablas, así que se pierden los datos que tengan.
-- =========================================================

CREATE DATABASE IF NOT EXISTS dkbza_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE dkbza_db;

-- Se borran en orden inverso a sus dependencias (primero las hijas)
DROP TABLE IF EXISTS turno_servicio;
DROP TABLE IF EXISTS turnos;
DROP TABLE IF EXISTS servicios;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS barberos;
DROP TABLE IF EXISTS login_intentos;
DROP TABLE IF EXISTS usuarios;

-- ---------------------------------------------------------
-- 1) usuarios: clientes y administradores del sitio
-- ---------------------------------------------------------
CREATE TABLE usuarios (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  nombre         VARCHAR(100) NOT NULL,
  email          VARCHAR(150) NOT NULL UNIQUE,
  password       VARCHAR(255) NOT NULL,              -- hash de password_hash(), nunca la clave real
  rol            VARCHAR(20)  NOT NULL DEFAULT 'usuario',
  estado         TINYINT      NOT NULL DEFAULT 1,
  ultimo_acceso  TIMESTAMP NULL DEFAULT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_usuarios_rol CHECK (rol IN ('admin', 'usuario'))
) ENGINE=InnoDB;

-- Intentos fallidos de login (freno a la fuerza bruta)
CREATE TABLE login_intentos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  ip          VARCHAR(45) NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_fecha (ip, created_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2) Tablas maestras
-- ---------------------------------------------------------
CREATE TABLE categorias (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(50) NOT NULL UNIQUE,
  descripcion  VARCHAR(255),
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE barberos (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(100) NOT NULL,
  especialidad  VARCHAR(100),
  estado        TINYINT NOT NULL DEFAULT 1,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3) servicios: catálogo que se muestra en el sitio (CRUD del panel)
-- ---------------------------------------------------------
CREATE TABLE servicios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id  INT NULL,
  nombre        VARCHAR(100) NOT NULL,
  descripcion   TEXT,
  precio        DECIMAL(10, 2) NOT NULL,
  imagen        VARCHAR(255),
  estado        TINYINT NOT NULL DEFAULT 1,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_servicios_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4) turnos: tabla principal del negocio (reservas)
-- ---------------------------------------------------------
CREATE TABLE turnos (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id     INT NOT NULL,
  barbero_id     INT NOT NULL,
  fecha          DATE NOT NULL,
  hora           TIME NOT NULL,
  estado         VARCHAR(20) NOT NULL DEFAULT 'pendiente',
  observaciones  VARCHAR(255),
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_turnos_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_turnos_barbero
    FOREIGN KEY (barbero_id) REFERENCES barberos(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT uq_turnos_barbero_horario UNIQUE (barbero_id, fecha, hora),
  CONSTRAINT chk_turnos_estado CHECK (estado IN ('pendiente', 'confirmado', 'cancelado', 'completado'))
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5) turno_servicio: relación N:M entre turnos y servicios
-- ---------------------------------------------------------
CREATE TABLE turno_servicio (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  turno_id     INT NOT NULL,
  servicio_id  INT NOT NULL,
  precio       DECIMAL(10, 2) NOT NULL,              -- precio cobrado en ese turno
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ts_turno
    FOREIGN KEY (turno_id) REFERENCES turnos(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ts_servicio
    FOREIGN KEY (servicio_id) REFERENCES servicios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT uq_turno_servicio UNIQUE (turno_id, servicio_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 6) Índices para las búsquedas más frecuentes
-- ---------------------------------------------------------
CREATE INDEX idx_servicios_estado ON servicios (estado);
CREATE INDEX idx_turnos_fecha     ON turnos (fecha, hora);
