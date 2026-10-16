<?php
  // Se incluye al principio de TODAS las páginas del panel.
  require_once __DIR__ . '/sesion.php';

  const MINUTOS_INACTIVIDAD = 30;

  // Que el navegador no guarde copias del panel (botón "atrás" después de salir)
  header('Cache-Control: no-store, no-cache, must-revalidate');
  header('Pragma: no-cache');

  if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
  }

  if (time() - ($_SESSION['ultima_actividad'] ?? 0) > MINUTOS_INACTIVIDAD * 60) {
    cerrar_sesion();
    header('Location: login.php?msg=expirada');
    exit;
  }

  $_SESSION['ultima_actividad'] = time();
?>
