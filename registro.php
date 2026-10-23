<?php
  $titulo_pagina = 'DKBZA | Crear cuenta';
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  header('Cache-Control: no-store, no-cache, must-revalidate');

  if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . pagina_inicio_segun_rol());
    exit;
  }

  $nombre  = '';
  $email   = '';
  $errores = [];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $nombre    = trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW) ?? '');
    $email     = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW) ?? ''));
    $clave     = filter_input(INPUT_POST, 'clave', FILTER_UNSAFE_RAW) ?? '';
    $confirmar = filter_input(INPUT_POST, 'confirmar', FILTER_UNSAFE_RAW) ?? '';

    // Solo letras (con tildes y ñ) y espacios: no se aceptan etiquetas ni símbolos
    if (!preg_match('/^[\p{L} ]{2,100}$/u', $nombre)) {
      $errores[] = 'El nombre debe tener entre 2 y 100 letras (sin números ni símbolos).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
      $errores[] = 'Ingresá un email válido.';
    }

    if (strlen($clave) < 8 || strlen($clave) > 72) {
      $errores[] = 'La contraseña debe tener entre 8 y 72 caracteres.';
    } elseif (!preg_match('/[A-Za-z]/', $clave) || !preg_match('/[0-9]/', $clave)) {
      $errores[] = 'La contraseña debe combinar letras y números.';
    }

    if ($clave !== $confirmar) {
      $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
      $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
      $stmt->execute([':email' => $email]);

      if ($stmt->fetch()) {
        $errores[] = 'Ya existe una cuenta con ese email.';
      }
    }

    if (!$errores) {
      // El rol siempre es 'usuario': nunca se toma del formulario
      $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, 'usuario')");

      try {
        $stmt->execute([
          ':nombre'   => $nombre,
          ':email'    => $email,
          ':password' => password_hash($clave, PASSWORD_BCRYPT),
        ]);

        header('Location: login.php?msg=registrado');
        exit;
      } catch (PDOException $ex) {
        if ($ex->getCode() !== '23000') {
          throw $ex;
        }
        $errores[] = 'Ya existe una cuenta con ese email.';
      }
    }
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
        NUEVA CUENTA
      </span>

      <h1>
        Crear cuenta
      </h1>

      <?php if ($errores): ?>
        <div class="alerta alerta-error">
          <ul>
            <?php foreach ($errores as $error): ?>
              <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="registro.php" class="form-servicio" novalidate>

        <?php echo csrf_campo(); ?>

        <div class="form-grupo">
          <label for="nombre">Nombre</label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            class="wireframe-input"
            autocomplete="name"
            maxlength="100"
            value="<?php echo e($nombre); ?>"
          >
        </div>

        <div class="form-grupo">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            class="wireframe-input"
            autocomplete="email"
            maxlength="150"
            value="<?php echo e($email); ?>"
          >
        </div>

        <div class="form-grupo">
          <label for="clave">Contraseña (mínimo 8, con letras y números)</label>
          <input
            type="password"
            id="clave"
            name="clave"
            class="wireframe-input"
            autocomplete="new-password"
          >
        </div>

        <div class="form-grupo">
          <label for="confirmar">Repetir contraseña</label>
          <input
            type="password"
            id="confirmar"
            name="confirmar"
            class="wireframe-input"
            autocomplete="new-password"
          >
        </div>

        <button type="submit" class="btn-blue">
          Crear cuenta
        </button>

      </form>

      <p class="form-ayuda">
        ¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a>
      </p>

    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
