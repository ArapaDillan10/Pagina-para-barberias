-- =========================================================
-- Reseñas de clientes: cualquier usuario logueado puede publicar las que quiera.
-- Se publican al toque (visible = 1) y el admin las puede ocultar.
-- vista = 0 marca las que el admin todavía no vio (aviso en el panel).
-- Ejecutar UNA vez en phpMyAdmin (local y hosting) ANTES de subir los archivos.
-- =========================================================
CREATE TABLE IF NOT EXISTS resenas (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT          NOT NULL,
  puntaje     TINYINT      NOT NULL,
  comentario  VARCHAR(500) NOT NULL,
  visible     TINYINT      NOT NULL DEFAULT 1,
  vista       TINYINT      NOT NULL DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_resenas_puntaje CHECK (puntaje BETWEEN 1 AND 5),
  INDEX idx_resenas_visible (visible, created_at),
  INDEX idx_resenas_vista (vista),
  INDEX idx_resenas_usuario (usuario_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;