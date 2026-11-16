<?php
  // Guardián de rutas: se incluye al principio de toda página que requiera sesión.
  //   require_once __DIR__ . '/includes/auth_guard.php';   -> cualquier usuario logueado
  //   verificarRol('admin');                               -> además, solo administradores
  require_once __DIR__ . '/sesion.php';
  require_once __DIR__ . '/../config/conexion.php';

  const MINUTOS_INACTIVIDAD = 30;

  // Que el navegador no guarde copias de páginas privadas (botón "atrás" después de salir)
  header('Cache-Control: no-store, no-cache, must-revalidate');
  header('Pragma: no-cache');

  function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
      header('Location: login.php?error=acceso_denegado');
      exit;
    }

    if (time() - ($_SESSION['ultima_actividad'] ?? 0) > MINUTOS_INACTIVIDAD * 60) {
      cerrar_sesion();
      header('Location: login.php?msg=expirada');
      exit;
    }

    $_SESSION['ultima_actividad'] = time();

    // El rol y el estado se leen de la base en cada página: si el admin cambia el rol
    // o desactiva la cuenta, toma al instante aunque la sesión esté abierta.
    global $pdo;
    $stmt = $pdo->prepare('SELECT nombre, rol FROM usuarios WHERE id = :id AND estado = 1');
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
    $actual = $stmt->fetch();

    if (!$actual) {
      cerrar_sesion();
      header('Location: login.php?error=cuenta_inactiva');
      exit;
    }

    $_SESSION['rol']            = $actual['rol'];
    $_SESSION['usuario_nombre'] = $actual['nombre'];
  }

  function verificarRol($rol) {
    verificarSesion();

    if (($_SESSION['rol'] ?? '') !== $rol) {
      http_response_code(403);
      header('Location: mi_cuenta.php?error=sin_permiso');
      exit;
    }
  }

  verificarSesion();
?>