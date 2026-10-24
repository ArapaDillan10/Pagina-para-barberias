-- =========================================================
-- DKBZA Barber Studio - Consultas de prueba 
-- Ejecutar en phpMyAdmin DESPUÉS de schema.sql.
-- =========================================================

USE dkbza_db;

-- ---------------------------------------------------------
-- CREATE: datos iniciales
-- ---------------------------------------------------------

-- Tabla maestra: categorias
INSERT INTO categorias (nombre, descripcion) VALUES
('Corte', 'Cortes de cabello clásicos y modernos'),
('Barba', 'Perfilado y cuidado de barba'),
('Combo', 'Corte y barba en un mismo turno');

-- Tabla maestra: barberos
INSERT INTO barberos (nombre, especialidad) VALUES
('Dillan Arapa', 'Master Barber & Founder'),
('Mateo Nieva', 'Fade Specialist & Senior');

-- servicios (los mismos que ya mostraba el sitio, ahora con su categoría)
INSERT INTO servicios (categoria_id, nombre, descripcion, precio, imagen) VALUES
(1, 'Corte', 'Asesoramiento en referencia al tipo de rostro y eleccion del corte ideal.', 18000, 'corte1.png'),
(2, 'Barba Completa', 'Toalla caliente, perfilado con navaja y aceites esenciales.', 9000, 'corte2.png'),
(3, 'Combo DKBZA (Corte + Barba)', 'Servicio completo VIP con asesoramiento y bebida incluida a eleccion.', 25000, 'combo3.png');

-- Clientes de ejemplo. El hash corresponde a una clave aleatoria
-- que no se guardó: sirven como datos de prueba, no para iniciar sesión.
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Cliente Demo', 'cliente@ejemplo.com', '$2y$10$fX6sdqx.6R3ThcoO92AvL.RwE4OIozWLUSAZpzIwVkhjLe11jcoXe', 'usuario'),
('Cliente Prueba', 'prueba@ejemplo.com', '$2y$10$fX6sdqx.6R3ThcoO92AvL.RwE4OIozWLUSAZpzIwVkhjLe11jcoXe', 'usuario');

-- Tabla principal: turnos
INSERT INTO turnos (usuario_id, barbero_id, fecha, hora, observaciones) VALUES
(1, 1, '2026-11-05', '10:00', 'Primera vez en la barbería'),
(2, 2, '2026-11-05', '11:30', NULL),
(1, 2, '2026-11-12', '17:00', 'Cancelar si llueve');

-- Detalle N:M: qué servicios incluye cada turno
INSERT INTO turno_servicio (turno_id, servicio_id, precio) VALUES
(1, 1, 18000),
(1, 2, 9000),
(2, 3, 25000),
(3, 2, 9000);

-- ---------------------------------------------------------
-- READ
-- ---------------------------------------------------------

-- Servicios activos, del más caro al más barato
SELECT id, nombre, precio
FROM servicios
WHERE estado = 1
ORDER BY precio DESC;

-- Turnos con cliente y barbero (INNER JOIN)
SELECT t.id, t.fecha, t.hora, u.nombre AS cliente, b.nombre AS barbero, t.estado
FROM turnos t
INNER JOIN usuarios u ON u.id = t.usuario_id
INNER JOIN barberos b ON b.id = t.barbero_id
ORDER BY t.fecha, t.hora;

-- Servicios de cada turno y total a cobrar (relación N:M)
SELECT t.id AS turno, t.fecha, s.nombre AS servicio, c.nombre AS categoria, ts.precio
FROM turno_servicio ts
INNER JOIN turnos t     ON t.id = ts.turno_id
INNER JOIN servicios s  ON s.id = ts.servicio_id
INNER JOIN categorias c ON c.id = s.categoria_id
ORDER BY t.id;

SELECT t.id AS turno, u.nombre AS cliente, SUM(ts.precio) AS total
FROM turnos t
INNER JOIN usuarios u        ON u.id = t.usuario_id
INNER JOIN turno_servicio ts ON ts.turno_id = t.id
GROUP BY t.id, u.nombre
ORDER BY t.id;

-- ---------------------------------------------------------
-- UPDATE (siempre con WHERE)
-- ---------------------------------------------------------
UPDATE turnos SET estado = 'confirmado' WHERE id = 1;
UPDATE turnos SET estado = 'cancelado'  WHERE id = 3;

-- ---------------------------------------------------------
-- DELETE (siempre con WHERE)
-- Al borrar el turno cancelado, ON DELETE CASCADE borra también su detalle.
-- ---------------------------------------------------------
DELETE FROM turnos WHERE id = 3 AND estado = 'cancelado';

-- Comprobación final
SELECT id, estado FROM turnos ORDER BY id;
SELECT turno_id, servicio_id FROM turno_servicio ORDER BY turno_id;
