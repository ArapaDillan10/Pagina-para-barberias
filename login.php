<?php
  $titulo_pagina = 'DKBZA | Ingreso al Panel';
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  const MAX_INTENTOS     = 5;
  const MINUTOS_BLOQUEO  = 15;

  header('Cache-Control: no-store, no-cache, must-revalidate');

  if (!empty($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
  }

  $ip      = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
  $usuario = '';
  $error   = '';

  // Limpia intentos viejos y cuenta los recientes de esta IP
  $stmt = $pdo->prepare('DELETE FROM login_intentos WHERE created_at < (NOW() - INTERVAL ' . MINUTOS_BLOQUEO . ' MINUTE)');
  $stmt->execute();

  $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM login_intentos WHERE ip = :ip');
  $stmt->execute([':ip' => $ip]);
  $intentos = (int) $stmt->fetch()['total'];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $usuario = trim(filter_input(INPUT_POST, 'usuario', FILTER_UNSAFE_RAW) ?? '');
    $clave   = filter_input(INPUT_POST, 'clave', FILTER_UNSAFE_RAW) ?? '';

    if ($intentos >= MAX_INTENTOS) {
      $error = 'Demasiados intentos fallidos. Esperá ' . MINUTOS_BLOQUEO . ' minutos y volvé a intentar.';
    } elseif (empty($usuario) || $clave === '') {
      $error = 'Completá el usuario y la contraseña.';
    } else {
      $stmt = $pdo->prepare('SELECT id, usuario, password_hash FROM usuarios WHERE usuario = :usuario AND estado = 1');
      $stmt->execute([':usuario' => $usuario]);
      $admin = $stmt->fetch();

      // Si el usuario no existe se verifica igual contra un hash falso, así la respuesta tarda lo mismo
      $hash = $admin['password_hash'] ?? '$2y$10$GUQ76oqQhle2LO59U1ngo.NdRN4pzqK92mGrgDOQjk5HZ5BfWaY2.';

      if (password_verify($clave, $hash) && $admin) {
        $stmt = $pdo->prepare('DELETE FROM login_intentos WHERE ip = :ip');
        $stmt->execute([':ip' => $ip]);

        session_regenerate_id(true);
        $_SESSION['admin_id']         = $admin['id'];
        $_SESSION['admin_usuario']    = $admin['usuario'];
        $_SESSION['ultima_actividad'] = time();
        unset($_SESSION['csrf_token']);

        $stmt = $pdo->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id');
        $stmt->execute([':id' => $admin['id']]);

        header('Location: admin.php');
        exit;
      }

      $stmt = $pdo->prepare('INSERT INTO login_intentos (ip) VALUES (:ip)');
      $stmt->execute([':ip' => $ip]);
      $intentos++;

      $restantes = MAX_INTENTOS - $intentos;
      $error = 'Usuario o contraseña incorrectos.';

      if ($restantes > 0) {
        $error .= ' Te quedan ' . $restantes . ' intento(s).';
      } else {
        $error = 'Demasiados intentos fallidos. Esperá ' . MINUTOS_BLOQUEO . ' minutos y volvé a intentar.';
      }
    }
  }

  $mensajes = [
    'salida'   => 'Cerraste sesión correctamente.',
    'expirada' => 'Tu sesión se cerró por inactividad. Volvé a ingresar.',
  ];

  $msg    = filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? '';
  $aviso  = $mensajes[$msg] ?? '';

  include('includes/header.php');
?>

  <header class="navbar">

    <div class="nav-container">

      <a href="index.php" class="brand">

        <span class="brand-icon">💈</span>

        <div>

          <span class="brand-name">
            DKBZA
          </span>

          <span class="brand-sub">
            ADMIN PANEL
          </span>

        </div>

      </a>

      <a href="index.php" class="btn-ghost-sm">
        ← Volver al sitio
      </a>

    </div>

  </header>

  <main class="admin-dashboard">

    <section class="login-card">

      <span class="section-tag">
        ACCESO RESTRINGIDO
      </span>

      <h1>
        Ingreso al panel
      </h1>

      <?php if ($aviso): ?>
        <div class="alerta alerta-exito">
          <?php echo e($aviso); ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alerta alerta-error">
          <?php echo e($error); ?>
        </div>
      <?php endif; ?>

      <form method="post" action="login.php" class="form-servicio" novalidate>

        <?php echo csrf_campo(); ?>

        <div class="form-grupo">
          <label for="usuario">Usuario</label>
          <input
            type="text"
            id="usuario"
            name="usuario"
            class="wireframe-input"
            autocomplete="username"
            value="<?php echo e($usuario); ?>"
          >
        </div>

        <div class="form-grupo">
          <label for="clave">Contraseña</label>
          <input
            type="password"
            id="clave"
            name="clave"
            class="wireframe-input"
            autocomplete="current-password"
          >
        </div>

        <button type="submit" class="btn-blue">
          Ingresar
        </button>

      </form>

    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
