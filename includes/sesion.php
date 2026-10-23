<?php
  // Sesión con cookie protegida + funciones de token CSRF.

  if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_name('DKBZA_SESION');
    session_set_cookie_params([
      'lifetime' => 0,
      'path'     => '/',
      'secure'   => !empty($_SERVER['HTTPS']),
      'httponly' => true,
      'samesite' => 'Strict',
    ]);

    session_start();
  }

  function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
  }

  function csrf_campo() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
  }

  function csrf_verificar() {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
      http_response_code(403);
      exit('Solicitud inválida. Volvé a cargar la página e intentá de nuevo.');
    }
  }

  function cerrar_sesion() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'],
      ]);
    }

    session_destroy();
  }

  function pagina_inicio_segun_rol() {
    return ($_SESSION['rol'] ?? '') === 'admin' ? 'admin.php' : 'mi_cuenta.php';
  }
?>
