<?php
  $titulo_pagina = 'DKBZA Barber Studio | Contacto';

  function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
  }

  $nombre = '';
  $email = '';
  $mensaje = '';
  $errores = [];
  $exito = false;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre  = isset($_POST['nombre'])  ? limpiar($_POST['nombre'])  : '';
    $email   = isset($_POST['email'])   ? limpiar($_POST['email'])   : '';
    $mensaje = isset($_POST['mensaje']) ? limpiar($_POST['mensaje']) : '';

    if (empty($nombre)) {
      $errores[] = 'El nombre es obligatorio.';
    }

    if (empty($email)) {
      $errores[] = 'El email es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errores[] = 'El email no tiene un formato válido (ej: nombre@correo.com).';
    }

    if (empty($mensaje)) {
      $errores[] = 'El mensaje es obligatorio.';
    } elseif (strlen($mensaje) < 10) {
      $errores[] = 'El mensaje debe tener al menos 10 caracteres.';
    }

    if (empty($errores)) {
      $exito = true;
      $nombre_enviado = $nombre;
      $nombre = '';
      $email = '';
      $mensaje = '';
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
            ¡Gracias, <?php echo $nombre_enviado; ?>! Recibimos tu mensaje y te vamos a responder a la brevedad.
          </div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
          <div class="alerta alerta-error">
            <strong>Revisá los siguientes errores:</strong>
            <ul>
              <?php foreach ($errores as $error): ?>
                <li><?php echo $error; ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <label for="nombre">
          Nombre
        </label>

        <input
          type="text"
          id="nombre"
          name="nombre"
          class="wireframe-input"
          value="<?php echo $nombre; ?>"
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
          value="<?php echo $email; ?>"
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
          required
        ><?php echo $mensaje; ?></textarea>


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
