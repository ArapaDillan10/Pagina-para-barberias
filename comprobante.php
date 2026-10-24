<?php
  // Sirve el comprobante de seña de un turno, solo a su dueño o al admin.
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=acceso_denegado');
    exit;
  }

  $turno_id = filter_input(INPUT_GET, 'turno', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

  $stmt = $pdo->prepare('SELECT usuario_id, sena_comprobante FROM turnos WHERE id = :id');
  $stmt->execute([':id' => $turno_id ?: 0]);
  $turno = $stmt->fetch();

  if (!$turno || !$turno['sena_comprobante']
      || ($_SESSION['rol'] !== 'admin' && (int) $turno['usuario_id'] !== (int) $_SESSION['usuario_id'])) {
    http_response_code(404);
    exit('Comprobante no encontrado.');
  }

  $archivo = __DIR__ . '/var/comprobantes/' . basename($turno['sena_comprobante']);

  if (!is_file($archivo)) {
    http_response_code(404);
    exit('Comprobante no encontrado.');
  }

  $mime = mime_content_type($archivo);
  if ($mime === 'application/pdf') {
    header('Content-Type: application/pdf');
  } else {
    header('Content-Type: image/jpeg');
  }
  header('Content-Disposition: inline; filename="comprobante-' . (int) $turno_id . '"');
  header('Cache-Control: private, no-store');
  readfile($archivo);
?>
