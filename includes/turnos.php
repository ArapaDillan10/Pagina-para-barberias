<?php
  const HORARIOS_TURNOS  = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
  const DIAS_CERRADO     = [0];   // 0 = domingo (formato de date('w'))
  const DIAS_ANTICIPACION = 60;
  const MAX_TURNOS_ACTIVOS = 3;

  const ESTADOS_TURNO = [
    'pendiente'  => 'Pendiente',
    'confirmado' => 'Confirmado',
    'completado' => 'Completado',
    'cancelado'  => 'Cancelado',
  ];

  // Devuelve un DateTime si $fecha es "AAAA-MM-DD", no pasó, no supera la anticipación y la barbería abre; si no, null.
  function fecha_turno_valida($fecha) {
    $dia = DateTime::createFromFormat('!Y-m-d', (string) $fecha);

    if (!$dia || $dia->format('Y-m-d') !== $fecha) {
      return null;
    }

    $hoy    = new DateTime('today');
    $limite = (new DateTime('today'))->modify('+' . DIAS_ANTICIPACION . ' days');

    if ($dia < $hoy || $dia > $limite || in_array((int) $dia->format('w'), DIAS_CERRADO, true)) {
      return null;
    }

    return $dia;
  }

  function hora_ya_paso($fecha, $hora) {
    return new DateTime("$fecha $hora") <= new DateTime();
  }

  function horas_ocupadas($pdo, $barbero_id, $fecha) {
    $stmt = $pdo->prepare("SELECT TIME_FORMAT(hora, '%H:%i') FROM turnos
                           WHERE barbero_id = :barbero AND fecha = :fecha AND estado <> 'cancelado'");
    $stmt->execute([':barbero' => $barbero_id, ':fecha' => $fecha]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  function clase_estado($estado) {
    return 'booking-status estado-' . $estado;
  }
?>
