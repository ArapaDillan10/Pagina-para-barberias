<?php
  // El cliente cancela uno de sus turnos (solo los suyos, pendientes o confirmados y que no hayan pasado).
  require_once __DIR__ . '/includes/auth_guard.php';
  require_once __DIR__ . '/config/conexion.php';

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mi_cuenta.php');
    exit;
  }

  csrf_verificar();

  $turno_id = filter_input(INPUT_POST, 'turno_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

  $stmt = $pdo->prepare("UPDATE turnos SET estado = 'cancelado'
                         WHERE id = :id AND usuario_id = :usuario
                           AND estado IN ('pendiente', 'confirmado')
                           AND TIMESTAMP(fecha, hora) > NOW()");
  $stmt->execute([':id' => $turno_id ?: 0, ':usuario' => $_SESSION['usuario_id']]);

  header('Location: mi_cuenta.php?msg=' . ($stmt->rowCount() === 1 ? 'cancelado' : 'no_cancelado'));
  exit;
