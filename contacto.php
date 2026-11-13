<?php
  $titulo_pagina = 'DKBZA Barber Studio | Contacto';
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  const MENSAJES_POR_HORA = 3;

  $nombre = '';
  $email = '';
  $mensaje = '';
  $errores = [];
  $exito = ($_GET['enviado'] ?? '') === '1';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    // Campo trampa: las personas no lo ven, los bots de spam sí lo completan
    if (!empty($_POST['sitio_web'])) {
      header('Location: contacto.php?enviado=1');
      exit;
    }

    $nombre  = trim((string) ($_POST['nombre'] ?? ''));
    $email   = trim((string) ($_POST['email'] ?? ''));
    $mensaje = trim((string) ($_POST['mensaje'] ?? ''));
    $ip      = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';

    if ($nombre === '') {
      $errores[] = 'El nombre es obligatorio.';
    } elseif (strlen($nombre) > 100) {
      $errores[] = 'El nombre es demasiado largo.';
    }

    if ($email === '') {
      $errores[] = 'El email es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
      $errores[] = 'El email no tiene un formato válido (ej: nombre@correo.com).';
    }

    if ($mensaje === '') {
      $errores[] = 'El mensaje es obligatorio.';
    } elseif (strlen($mensaje) < 10) {
      $errores[] = 'El mensaje debe tener al menos 10 caracteres.';
    } elseif (strlen($mensaje) > 2000) {
      $errores[] = 'El mensaje no puede superar los 2000 caracteres.';
    }

    if (empty($errores)) {
      $stmt = $pdo->prepare('SELECT COUNT(*) FROM mensajes WHERE ip = :ip AND created_at > (NOW() - INTERVAL 1 HOUR)');
      $stmt->execute([':ip' => $ip]);

      if ((int) $stmt->fetchColumn() >= MENSAJES_POR_HORA) {
        $errores[] = 'Ya enviaste varios mensajes en la última hora. Probá de nuevo más tarde.';
      }
    }

    if (empty($errores)) {
      $stmt = $pdo->prepare('INSERT INTO mensajes (nombre, email, mensaje, ip) VALUES (:nombre, :email, :mensaje, :ip)');
      $stmt->execute([
        ':nombre'  => $nombre,
        ':email'   => $email,
        ':mensaje' => $mensaje,
        ':ip'      => $ip,
      ]);

      // Redirige para que al recargar la página no se mande el mensaje otra vez
      header('Location: contacto.php?enviado=1');
      exit;
    }
  }

  include('includes/header.php');
  include('includes/nav.php');
?>

  <!-- CONTACTO -->

  <section class="section-card">

    <div class="container grid-contact">


      <!-- INFORMACIÓN -->

      <div>

        <span class="section-tag">
          UBICACIÓN Y CONTACTO
        </span>

        <h1>
          Encontranos en Salta
        </h1>

        <p class="text-subtle">
          calle siempre viva 123
        </p>

        <p class="text-subtle">
          Tel: +54 387 1111 111
        </p>

        <p class="text-subtle">
          Email: <?php echo htmlspecialchars(env('APP_EMAIL')); ?>
        </p>

      </div>


      <!-- FORMULARIO (POST) -->

      <form
        method="post"
        action="contacto.php"
        class="contact-form"
        novalidate
      >

        <?php if ($exito): ?>
          <div class="alerta alerta-exito">
            ¡Gracias! Recibimos tu mensaje y te vamos a responder a la brevedad.
          </div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
          <div class="alerta alerta-error">
            <strong>Revisá los siguientes errores:</strong>
            <ul>
              <?php foreach ($errores as $error): ?>
                <li><?php echo e($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php echo csrf_campo(); ?>

        <!-- Campo trampa para bots: queda fuera de la pantalla -->
        <div style="position: absolute; left: -9999px;" aria-hidden="true">
          <label for="sitio_web">No completar</label>
          <input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
        </div>

        <label for="nombre">
          Nombre
        </label>

        <input
          type="text"
          id="nombre"
          name="nombre"
          class="wireframe-input"
          value="<?php echo e($nombre); ?>"
          maxlength="100"
          required
        >


        <label for="email">
          Email
        </label>

        <input
          type="email"
          id="email"
          name="email"
          class="wireframe-input"
          value="<?php echo e($email); ?>"
          maxlength="150"
          required
        >


        <label for="mensaje">
          Mensaje
        </label>

        <textarea
          id="mensaje"
          name="mensaje"
          class="wireframe-input"
          rows="4"
          maxlength="2000"
          required
        ><?php echo e($mensaje); ?></textarea>


        <button
          type="submit"
          class="btn-blue"
        >
          Enviar Mensaje
        </button>

      </form>

    </div>

  </section>

<?php include('includes/footer.php'); ?>