<?php
  // Devuelve en JSON los horarios que ya no se pueden reservar para un barbero y una fecha.
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/includes/turnos.php';

  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');

  if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Tenés que iniciar sesión.']));
  }

  $barbero_id = filter_input(INPUT_GET, 'barbero', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
  $fecha      = (string) filter_input(INPUT_GET, 'fecha', FILTER_UNSAFE_RAW);

  if (!$barbero_id || !fecha_turno_valida($fecha)) {
    exit(json_encode(['ocupados' => HORARIOS_TURNOS, 'cerrado' => true]));
  }

  require_once __DIR__ . '/config/conexion.php';

  $ocupados = horas_ocupadas($pdo, $barbero_id, $fecha);

  foreach (HORARIOS_TURNOS as $hora) {
    if (hora_ya_paso($fecha, $hora)) {
      $ocupados[] = $hora;
    }
  }

  echo json_encode(['ocupados' => array_values(array_unique($ocupados)), 'cerrado' => false]);
