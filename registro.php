<?php
  $titulo_pagina = 'DKBZA | Crear cuenta';
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';
  require_once __DIR__ . '/includes/mail.php';

  // Verificación del email: la cuenta recién se crea en `usuarios` cuando el PIN es correcto.
  const PIN_MINUTOS       = 15;   // el PIN vence a los 15 minutos
  const PIN_INTENTOS      = 5;    // PIN mal puestos antes de anular el código
  const REENVIO_SEGUNDOS  = 60;   // espera entre un envío y otro
  const ENVIOS_POR_HORA   = 5;    // códigos por email en una hora
  const REGISTROS_POR_IP  = 10;   // registros nuevos por IP en una hora (freno al spam)

  header('Cache-Control: no-store, no-cache, must-revalidate');

  if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . pagina_inicio_segun_rol());
    exit;
  }

  function generar_pin() {
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
  }

  function enviar_pin($email, $nombre, $pin) {
    $asunto = 'Tu código para DKBZA Barber Studio: ' . $pin;
    $html   = '<div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;padding:24px;background:#0b1220;color:#f9fafb;border-radius:8px">'
            . '<h2 style="margin-top:0">Hola, ' . e($nombre) . '</h2>'
            . '<p>Usá este código para confirmar tu email y crear tu cuenta en DKBZA Barber Studio:</p>'
            . '<p style="font-size:32px;font-weight:bold;letter-spacing:8px;text-align:center;background:#111a2e;padding:16px;border-radius:6px">' . $pin . '</p>'
            . '<p>Vence en ' . PIN_MINUTOS . ' minutos. Si no fuiste vos, ignorá este mail.</p>'
            . '</div>';
    $texto  = "Hola, $nombre\n\nTu código para crear la cuenta en DKBZA Barber Studio es: $pin\n"
            . 'Vence en ' . PIN_MINUTOS . " minutos. Si no fuiste vos, ignorá este mail.\n";

    return enviar_mail($email, $nombre, $asunto, $html, $texto);
  }

  // Trae el registro en espera con los cálculos de tiempo hechos por MySQL (misma hora que NOW())
  function buscar_pendiente($pdo, $email) {
    $stmt = $pdo->prepare('SELECT *,
                                  expira_at < NOW() AS vencido,
                                  TIMESTAMPDIFF(SECOND, ultimo_envio, NOW()) AS segundos_desde_envio,
                                  primer_envio < (NOW() - INTERVAL 1 HOUR) AS ventana_vieja
                           FROM registros_pendientes WHERE email = :email');
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
  }

  // Revisa la espera de 60 s y el máximo por hora. Devuelve el error o '' si se puede mandar.
  function error_reenvio($pendiente) {
    if ($pendiente && (int) $pendiente['segundos_desde_envio'] < REENVIO_SEGUNDOS) {
      $faltan = REENVIO_SEGUNDOS - (int) $pendiente['segundos_desde_envio'];
      return 'Esperá ' . $faltan . ' segundos para pedir otro código.';
    }

    if ($pendiente && !$pendiente['ventana_vieja'] && (int) $pendiente['envios'] >= ENVIOS_POR_HORA) {
      return 'Ya pediste ' . ENVIOS_POR_HORA . ' códigos en la última hora. Probá de nuevo más tarde.';
    }

    return '';
  }

  // Los registros que nadie confirmó se borran solos al día
  $pdo->exec('DELETE FROM registros_pendientes WHERE created_at < (NOW() - INTERVAL 1 DAY)');

  // "Cambiar email": vuelve al paso 1
  if (isset($_GET['cambiar'])) {
    unset($_SESSION['registro_email']);
    header('Location: registro.php');
    exit;
  }

  $ip        = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
  $nombre    = '';
  $email     = '';
  $errores   = [];
  $pendiente = null;

  if (!empty($_SESSION['registro_email'])) {
    $pendiente = buscar_pendiente($pdo, $_SESSION['registro_email']);

    if (!$pendiente) {
      unset($_SESSION['registro_email']);
    }
  }

  $avisos = [
    'enviado'    => 'Te mandamos un código nuevo. Revisá también la carpeta de spam.',
    'no_enviado' => 'No pudimos enviar el código. Revisá que el email esté bien escrito o tocá "Reenviar código" en un minuto.',
  ];
  $msg        = filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? '';
  $aviso      = $avisos[$msg] ?? '';
  $aviso_tipo = $msg === 'no_enviado' ? 'alerta-error' : 'alerta-exito';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $accion = $_POST['accion'] ?? 'datos';

    // ---------- Paso 1: datos de la cuenta ----------
    if ($accion === 'datos') {
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

      $previo = $errores ? null : buscar_pendiente($pdo, $email);

      if (!$errores && $previo) {
        $error = error_reenvio($previo);
        if ($error) {
          $errores[] = $error;
        }
      }

      if (!$errores && !$previo) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM registros_pendientes WHERE ip = :ip AND created_at > (NOW() - INTERVAL 1 HOUR)');
        $stmt->execute([':ip' => $ip]);

        if ((int) $stmt->fetchColumn() >= REGISTROS_POR_IP) {
          $errores[] = 'Hubo demasiados registros desde tu conexión. Probá de nuevo en una hora.';
        }
      }

      if (!$errores) {
        $pin   = generar_pin();
        $datos = [
          ':nombre'   => $nombre,
          ':email'    => $email,
          ':password' => password_hash($clave, PASSWORD_BCRYPT),
          ':codigo'   => password_hash($pin, PASSWORD_BCRYPT),
          ':ip'       => $ip,
        ];

        if ($previo) {
          // Ya había un registro en espera con ese email: se actualizan los datos y sale un PIN nuevo
          $stmt = $pdo->prepare('UPDATE registros_pendientes
                                 SET nombre = :nombre, password = :password, codigo = :codigo, intentos = 0,
                                     envios = IF(primer_envio < (NOW() - INTERVAL 1 HOUR), 1, envios + 1),
                                     primer_envio = IF(primer_envio < (NOW() - INTERVAL 1 HOUR), NOW(), primer_envio),
                                     ultimo_envio = NOW(), expira_at = NOW() + INTERVAL ' . PIN_MINUTOS . ' MINUTE, ip = :ip
                                 WHERE email = :email');
        } else {
          $stmt = $pdo->prepare('INSERT INTO registros_pendientes
                                   (nombre, email, password, codigo, primer_envio, ultimo_envio, expira_at, ip)
                                 VALUES (:nombre, :email, :password, :codigo, NOW(), NOW(), NOW() + INTERVAL ' . PIN_MINUTOS . ' MINUTE, :ip)');
        }
        $stmt->execute($datos);

        $_SESSION['registro_email'] = $email;
        $enviado = enviar_pin($email, $nombre, $pin);

        header('Location: registro.php' . ($enviado ? '' : '?msg=no_enviado'));
        exit;
      }
    }

    // ---------- Paso 2: reenviar el código ----------
    if ($accion === 'reenviar' && $pendiente) {
      $error = error_reenvio($pendiente);

      if ($error) {
        $errores[] = $error;
      } else {
        $pin  = generar_pin();
        $stmt = $pdo->prepare('UPDATE registros_pendientes
                               SET codigo = :codigo, intentos = 0,
                                   envios = IF(primer_envio < (NOW() - INTERVAL 1 HOUR), 1, envios + 1),
                                   primer_envio = IF(primer_envio < (NOW() - INTERVAL 1 HOUR), NOW(), primer_envio),
                                   ultimo_envio = NOW(), expira_at = NOW() + INTERVAL ' . PIN_MINUTOS . ' MINUTE
                               WHERE id = :id');
        $stmt->execute([':codigo' => password_hash($pin, PASSWORD_BCRYPT), ':id' => $pendiente['id']]);

        $enviado = enviar_pin($pendiente['email'], $pendiente['nombre'], $pin);

        header('Location: registro.php?msg=' . ($enviado ? 'enviado' : 'no_enviado'));
        exit;
      }
    }

    // ---------- Paso 2: confirmar el PIN ----------
    if ($accion === 'verificar' && $pendiente) {
      // El PIN llega en 6 cuadraditos (pin[]): se juntan y se dejan solo los números
      $pin = $_POST['pin'] ?? '';
      $pin = is_array($pin) ? implode('', array_filter($pin, 'is_string')) : (string) $pin;
      $pin = preg_replace('/\D/', '', $pin);

      if ((int) $pendiente['intentos'] >= PIN_INTENTOS) {
        $errores[] = 'Te equivocaste ' . PIN_INTENTOS . ' veces. Tocá "Reenviar código" para recibir uno nuevo.';
      } elseif ($pendiente['vencido']) {
        $errores[] = 'El código venció. Tocá "Reenviar código" para recibir uno nuevo.';
      } elseif (strlen($pin) !== 6) {
        $errores[] = 'El código tiene 6 números.';
      } elseif (!password_verify($pin, $pendiente['codigo'])) {
        $stmt = $pdo->prepare('UPDATE registros_pendientes SET intentos = intentos + 1 WHERE id = :id');
        $stmt->execute([':id' => $pendiente['id']]);

        $restantes = PIN_INTENTOS - ((int) $pendiente['intentos'] + 1);
        $errores[] = $restantes > 0
          ? 'Código incorrecto. Te quedan ' . $restantes . ' intento(s).'
          : 'Código incorrecto. Tocá "Reenviar código" para recibir uno nuevo.';
      } else {
        // PIN correcto: recién ahora se crea la cuenta (el rol siempre es 'usuario')
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, 'usuario')");

        try {
          $stmt->execute([
            ':nombre'   => $pendiente['nombre'],
            ':email'    => $pendiente['email'],
            ':password' => $pendiente['password'],
          ]);
          $nuevo_id = (int) $pdo->lastInsertId();
        } catch (PDOException $ex) {
          if ($ex->getCode() !== '23000') {
            throw $ex;
          }
          $errores[] = 'Ya existe una cuenta con ese email. Iniciá sesión.';
        }

        $stmt = $pdo->prepare('DELETE FROM registros_pendientes WHERE id = :id');
        $stmt->execute([':id' => $pendiente['id']]);
        unset($_SESSION['registro_email']);

        if (!$errores) {
          // Entra logueado directo, igual que en login.php
          session_regenerate_id(true);
          $_SESSION['usuario_id']       = $nuevo_id;
          $_SESSION['usuario_nombre']   = $pendiente['nombre'];
          $_SESSION['rol']              = 'usuario';
          $_SESSION['ultima_actividad'] = time();
          unset($_SESSION['csrf_token']);

          $stmt = $pdo->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id');
          $stmt->execute([':id' => $_SESSION['usuario_id']]);

          header('Location: mi_cuenta.php?msg=bienvenido');
          exit;
        }

        $pendiente = null;
      }
    }
  }

  include('includes/header.php');
?>

  <header class="navbar">

    <div class="nav-container">

      <a href="index.php" class="brand">

        <img src="./img/logo.png" alt="Logo DKBZA" class="brand-logo" />

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
        <?php echo $pendiente ? 'Confirmá tu email' : 'Crear cuenta'; ?>
      </h1>

      <?php if ($aviso): ?>
        <div class="alerta <?php echo $aviso_tipo; ?>">
          <?php echo e($aviso); ?>
        </div>
      <?php endif; ?>

      <?php if ($errores): ?>
        <div class="alerta alerta-error">
          <ul>
            <?php foreach ($errores as $error): ?>
              <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($pendiente): ?>

        <p class="form-ayuda">
          Te mandamos un código de 6 números a <strong><?php echo e($pendiente['email']); ?></strong>.
          Vence en <?php echo PIN_MINUTOS; ?> minutos. Si no lo ves, revisá la carpeta de spam.
        </p>

        <form method="post" action="registro.php" class="form-servicio" novalidate>

          <?php echo csrf_campo(); ?>
          <input type="hidden" name="accion" value="verificar">

          <div class="form-grupo">
            <label for="pin-1">Código de verificación</label>
            <div class="pin-cajas">
              <?php for ($i = 1; $i <= 6; $i++): ?>
                <input
                  type="text"
                  id="pin-<?php echo $i; ?>"
                  name="pin[]"
                  class="pin-caja"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  autocomplete="<?php echo $i === 1 ? 'one-time-code' : 'off'; ?>"
                  aria-label="Número <?php echo $i; ?> del código"
                  <?php echo $i === 1 ? 'autofocus' : ''; ?>
                >
              <?php endfor; ?>
            </div>
          </div>

          <button type="submit" class="btn-blue">
            Confirmar y crear cuenta
          </button>

        </form>

        <form method="post" action="registro.php" class="form-ayuda">
          <?php echo csrf_campo(); ?>
          <input type="hidden" name="accion" value="reenviar">
          ¿No te llegó?
          <button type="submit" class="btn-ghost-sm">Reenviar código</button>
        </form>

        <p class="form-ayuda">
          ¿Te equivocaste de email? <a href="registro.php?cambiar=1">Cambiarlo</a>
        </p>

        <script>
          // Cuadraditos del PIN: solo números, salta solo al siguiente y acepta pegar el código entero
          (function () {
            var cajas = document.querySelectorAll('.pin-caja');

            function repartir(desde, digitos) {
              for (var i = 0; i < digitos.length && desde + i < cajas.length; i++) {
                cajas[desde + i].value = digitos[i];
              }
              cajas[Math.min(desde + digitos.length, cajas.length - 1)].focus();
            }

            cajas.forEach(function (caja, i) {
              caja.addEventListener('input', function () {
                var digitos = caja.value.replace(/\D/g, '');
                caja.value = '';
                if (digitos) {
                  repartir(i, digitos);
                }
              });

              caja.addEventListener('paste', function (ev) {
                ev.preventDefault();
                var digitos = (ev.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                if (digitos) {
                  repartir(i, digitos);
                }
              });

              caja.addEventListener('keydown', function (ev) {
                if (ev.key === 'Backspace' && caja.value === '' && i > 0) {
                  ev.preventDefault();
                  cajas[i - 1].value = '';
                  cajas[i - 1].focus();
                } else if (ev.key === 'ArrowLeft' && i > 0) {
                  cajas[i - 1].focus();
                } else if (ev.key === 'ArrowRight' && i < cajas.length - 1) {
                  cajas[i + 1].focus();
                }
              });

              caja.addEventListener('focus', function () {
                caja.select();
              });
            });
          })();
        </script>

      <?php else: ?>

        <form method="post" action="registro.php" class="form-servicio" novalidate>

          <?php echo csrf_campo(); ?>
          <input type="hidden" name="accion" value="datos">

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
          Te vamos a mandar un código a tu email para confirmar que es tuyo.
        </p>

        <p class="form-ayuda">
          ¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a>
        </p>

      <?php endif; ?>

    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
