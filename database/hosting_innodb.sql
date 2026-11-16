-- =========================================================
-- Hosting (InfinityFree): pasar las tablas viejas a InnoDB + utf8mb4.
-- En el hosting turnos y usuarios eran MyISAM + latin1_swedish_ci,
-- y mensajes era InnoDB pero latin1. Las demás ya estaban bien.
-- CONVERT TO CHARACTER SET convierte los datos que ya hay (no se pierden tildes ni ñ).
-- Antes de correrlo: backup con Exportar en phpMyAdmin.
-- Ejecutar UNA vez en phpMyAdmin con la base seleccionada (pestaña SQL).
-- Si se corre de nuevo no rompe nada: solo vuelve a armar las tablas.
-- =========================================================

ALTER TABLE usuarios ENGINE=InnoDB;
ALTER TABLE usuarios CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE turnos ENGINE=InnoDB;
ALTER TABLE turnos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE mensajes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;