<?php
  // API (TP9): búsqueda de servicios en JSON para el buscador en vivo.
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');

  require_once __DIR__ . '/../config/conexion.php';

  $q = trim((string) filter_input(INPUT_GET, 'q', FILTER_UNSAFE_RAW));

  try {
    if ($q !== '') {
      $stmt = $pdo->prepare(
        'SELECT id, nombre, descripcion, precio, imagen
         FROM servicios
         WHERE estado = 1
           AND (nombre LIKE :q_nombre OR descripcion LIKE :q_descripcion)
         ORDER BY id'
      );
      $stmt->execute([':q_nombre' => "%{$q}%", ':q_descripcion' => "%{$q}%"]);
    } else {
      $stmt = $pdo->query(
        'SELECT id, nombre, descripcion, precio, imagen
         FROM servicios
         WHERE estado = 1
         ORDER BY id
         LIMIT 10'
      );
    }

    http_response_code(200);
    echo json_encode([
      'status' => 'success',
      'data'   => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al consultar la base de datos.']);
  }