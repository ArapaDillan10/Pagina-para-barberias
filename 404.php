<?php
  http_response_code(404);

  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/includes/funciones.php';

  $titulo_pagina = 'DKBZA Barber Studio | Página no encontrada';

  // Carpeta donde está el sitio ("/" en el hosting, "/Barberias/" en XAMPP)
  $base_href = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

  include('includes/header.php');
  include('includes/nav.php');
?>

  <!-- ERROR 404 -->
  <section class="section-dark pagina-error">

    <span class="section-tag">ERROR 404</span>

    <h1 class="pagina-error-titulo">ESTA PÁGINA NO <span class="highlight">EXISTE</span></h1>

    <p class="pagina-error-texto">
      Puede que el link esté mal escrito o que la página se haya movido.
    </p>

    <div class="hero-buttons pagina-error-botones">
      <a href="index.php" class="btn-primary">VOLVER AL INICIO</a>
      <a href="reservas.php" class="btn-secondary">RESERVAR TURNO</a>
    </div>

  </section>

<?php include('includes/footer.php'); ?>