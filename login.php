<?php
  $titulo_pagina = 'DKBZA | Iniciar sesión';
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  const MAX_INTENTOS     = 5;
  const MINUTOS_BLOQUEO  = 15;

  header('Cache-Control: no-store, no-cache, must-revalidate');

  if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . pagina_inicio_segun_rol());
    exit;
  }

  $ip      = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
  $email   = '';
  $error   = '';

  // Limpia intentos viejos y cuenta los recientes de esta IP
  $stmt = $pdo->prepare('DELETE FROM login_intentos WHERE created_at < (NOW() - INTERVAL ' . MINUTOS_BLOQUEO . ' MINUTE)');
  $stmt->execute();

  $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM login_intentos WHERE ip = :ip');
  $stmt->execute([':ip' => $ip]);
  $intentos = (int) $stmt->fetch()['total'];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $email = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW) ?? ''));
    $clave = filter_input(INPUT_POST, 'clave', FILTER_UNSAFE_RAW) ?? '';

    if ($intentos >= MAX_INTENTOS) {
      $error = 'Demasiados intentos fallidos. Esperá ' . MINUTOS_BLOQUEO . ' minutos y volvé a intentar.';
    } elseif (empty($email) || $clave === '') {
      $error = 'Completá el email y la contraseña.';
    } else {
      $stmt = $pdo->prepare('SELECT id, nombre, password, rol FROM usuarios WHERE email = :email AND estado = 1');
      $stmt->execute([':email' => $email]);
      $usuario = $stmt->fetch();

      // Si el email no existe se verifica igual contra un hash falso, así la respuesta tarda lo mismo
      $hash = $usuario['password'] ?? '$2y$10$GUQ76oqQhle2LO59U1ngo.NdRN4pzqK92mGrgDOQjk5HZ5BfWaY2.';

      if (password_verify($clave, $hash) && $usuario) {
        $stmt = $pdo->prepare('DELETE FROM login_intentos WHERE ip = :ip');
        $stmt->execute([':ip' => $ip]);

        session_regenerate_id(true);
        $_SESSION['usuario_id']       = $usuario['id'];
        $_SESSION['usuario_nombre']   = $usuario['nombre'];
        $_SESSION['rol']              = $usuario['rol'];
        $_SESSION['ultima_actividad'] = time();
        unset($_SESSION['csrf_token']);

        $stmt = $pdo->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id');
        $stmt->execute([':id' => $usuario['id']]);

        header('Location: ' . pagina_inicio_segun_rol());
        exit;
      }

      $stmt = $pdo->prepare('INSERT INTO login_intentos (ip) VALUES (:ip)');
      $stmt->execute([':ip' => $ip]);
      $intentos++;

      $restantes = MAX_INTENTOS - $intentos;
      $error = 'Email o contraseña incorrectos.';

      if ($restantes > 0) {
        $error .= ' Te quedan ' . $restantes . ' intento(s).';
      } else {
        $error = 'Demasiados intentos fallidos. Esperá ' . MINUTOS_BLOQUEO . ' minutos y volvé a intentar.';
      }
    }
  }

  $mensajes = [
    'salida'     => 'Cerraste sesión correctamente.',
    'expirada'   => 'Tu sesión se cerró por inactividad. Volvé a ingresar.',
    'registrado' => 'Tu cuenta se creó correctamente. Ya podés iniciar sesión.',
  ];

  $msg   = filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? '';
  $aviso = $mensajes[$msg] ?? '';

  if (!$error && (filter_input(INPUT_GET, 'error', FILTER_UNSAFE_RAW) ?? '') === 'acceso_denegado') {
    $error = 'Tenés que iniciar sesión para ver esa página.';
  }

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
            BARBER STUDIO
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
        MI CUENTA
      </span>

      <h1>
        Iniciar sesión
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
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            class="wireframe-input"
            autocomplete="username"
            maxlength="150"
            value="<?php echo e($email); ?>"
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

      <p class="form-ayuda">
        ¿No tenés cuenta? <a href="registro.php">Registrate</a>
      </p>

    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
