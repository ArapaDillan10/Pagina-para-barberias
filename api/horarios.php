<?php
  // API (TP9): devuelve en JSON los horarios no disponibles para un barbero y una fecha.
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');

  require_once __DIR__ . '/../includes/sesion.php';
  require_once __DIR__ . '/../includes/turnos.php';

  if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit(json_encode(['status' => 'error', 'message' => 'Tenés que iniciar sesión.']));
  }

  $barbero_id = filter_input(INPUT_GET, 'barbero', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
  $fecha      = trim((string) filter_input(INPUT_GET, 'fecha', FILTER_UNSAFE_RAW));

  if (!$barbero_id || $fecha === '') {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Faltan parámetros: barbero y fecha.']));
  }

  require_once __DIR__ . '/../config/conexion.php';

  try {
    $stmt = $pdo->prepare('SELECT id FROM barberos WHERE id = :id AND estado = 1');
    $stmt->execute([':id' => $barbero_id]);

    if (!$stmt->fetch()) {
      http_response_code(404);
      exit(json_encode(['status' => 'error', 'message' => 'Barbero no encontrado.']));
    }

    // Día no atendible (domingo, pasado o +60 días): se devuelve todo ocupado.
    if (!fecha_turno_valida($fecha)) {
      http_response_code(200);
      exit(json_encode([
        'status' => 'success',
        'data'   => ['ocupados' => HORARIOS_TURNOS, 'cerrado' => true]
      ]));
    }

    $ocupados = horas_ocupadas($pdo, $barbero_id, $fecha);

    foreach (HORARIOS_TURNOS as $hora) {
      if (hora_ya_paso($fecha, $hora)) {
        $ocupados[] = $hora;
      }
    }

    http_response_code(200);
    echo json_encode([
      'status' => 'success',
      'data'   => ['ocupados' => array_values(array_unique($ocupados)), 'cerrado' => false]
    ]);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al consultar la base de datos.']);
  }
