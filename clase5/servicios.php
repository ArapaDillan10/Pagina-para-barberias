<?php
  $titulo_pagina = 'DKBZA Barber Studio | Servicios';

  $servicios = [
    [
      'nombre'      => 'Corte',
      'descripcion' => 'Asesoramiento en referencia al tipo de rostro y eleccion del corte ideal.',
      'precio'      => '$18.000',
      'imagen'      => './img/corte1.png',
      'alt'         => 'Corte Clasico / Fade'
    ],
    [
      'nombre'      => 'Barba Completa',
      'descripcion' => 'Toalla caliente, perfilado con navaja y aceites esenciales.',
      'precio'      => '$9.000',
      'imagen'      => './img/corte2.png',
      'alt'         => 'Barba Completa / Ritual'
    ],
    [
      'nombre'      => 'Combo DKBZA (Corte + Barba)',
      'descripcion' => 'Servicio completo VIP con asesoramiento y bebida incluida a eleccion.',
      'precio'      => '$25.000',
      'imagen'      => './img/combo3.png',
      'alt'         => 'Combo DKBZA (Corte + Barba)'
    ]
  ];

  $busqueda = '';
  $resultados = $servicios;

  if (isset($_GET['buscar'])) {
    $busqueda = filter_input(INPUT_GET, 'buscar', FILTER_UNSAFE_RAW);
    $busqueda = htmlspecialchars(trim($busqueda), ENT_QUOTES, 'UTF-8');

    if (!empty($busqueda)) {
      $resultados = [];

      foreach ($servicios as $servicio) {
        if (stripos($servicio['nombre'], $busqueda) !== false ||
            stripos($servicio['descripcion'], $busqueda) !== false) {
          $resultados[] = $servicio;
        }
      }
    }
  }

  include('includes/header.php');
  include('includes/nav.php');
?>

  <!-- SERVICIOS -->

  <section id="servicios" class="section-card">

    <div class="container">

      <span class="section-tag">
        MENÚ DE SERVICIOS
      </span>

      <h1>
        Cortes & Estilo
      </h1>


      <!-- BUSCADOR (GET) -->

      <form method="get" action="servicios.php" class="search-form">

        <label for="buscar" class="visually-hidden">
          Buscar servicio
        </label>

        <input
          type="text"
          id="buscar"
          name="buscar"
          class="wireframe-input"
          placeholder="Buscar servicio (ej: barba, corte, combo)"
          value="<?php echo $busqueda; ?>"
        >

        <button type="submit" class="btn-blue">
          Buscar
        </button>

      </form>

      <?php if (!empty($busqueda)): ?>
        <?php if (count($resultados) > 0): ?>
          <div class="alerta alerta-exito">
            Resultados para "<?php echo $busqueda; ?>": <?php echo count($resultados); ?> servicio(s) encontrado(s).
            <a href="servicios.php">Ver todos</a>
          </div>
        <?php else: ?>
          <div class="alerta alerta-error">
            No se encontraron servicios para "<?php echo $busqueda; ?>".
            <a href="servicios.php">Ver todos</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>


      <div class="grid-services">

        <?php foreach ($resultados as $servicio): ?>

          <div class="card-service">

            <img src="<?php echo $servicio['imagen']; ?>" alt="<?php echo $servicio['alt']; ?>" class="service-img" loading="lazy">

            <h3>
              <?php echo $servicio['nombre']; ?>
            </h3>

            <p>
              <?php echo $servicio['descripcion']; ?>
            </p>

            <span class="price">
              <?php echo $servicio['precio']; ?>
            </span>

          </div>

        <?php endforeach; ?>

      </div>

    </div>

  </section>

<?php include('includes/footer.php'); ?>
