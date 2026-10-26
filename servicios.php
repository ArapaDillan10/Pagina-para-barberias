<?php
  $titulo_pagina = 'DKBZA Barber Studio | Servicios';
  require_once __DIR__ . '/config/conexion.php';

  $busqueda = '';

  if (isset($_GET['buscar'])) {
    $busqueda = trim(filter_input(INPUT_GET, 'buscar', FILTER_UNSAFE_RAW) ?? '');
  }

  if (!empty($busqueda)) {
    $sql = "SELECT id, nombre, descripcion, precio, imagen
            FROM servicios
            WHERE estado = 1
              AND (nombre LIKE :busqueda_nombre OR descripcion LIKE :busqueda_descripcion)
            ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':busqueda_nombre'      => '%' . $busqueda . '%',
      ':busqueda_descripcion' => '%' . $busqueda . '%'
    ]);
  } else {
    $sql = "SELECT id, nombre, descripcion, precio, imagen
            FROM servicios
            WHERE estado = :estado
            ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':estado' => 1]);
  }

  $resultados = $stmt->fetchAll();

  function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
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

      <form method="get" action="servicios.php" class="search-form" id="form-buscar-servicios">

        <label for="buscar" class="visually-hidden">
          Buscar servicio
        </label>

        <input
          type="text"
          id="buscar"
          name="buscar"
          class="wireframe-input"
          placeholder="Buscar servicio (ej: barba, corte, combo)"
          value="<?php echo e($busqueda); ?>"
        >

        <button type="submit" class="btn-blue">
          Buscar
        </button>

      </form>

      <?php if (!empty($busqueda)): ?>
        <?php if (count($resultados) > 0): ?>
          <div class="alerta alerta-exito">
            Resultados para "<?php echo e($busqueda); ?>": <?php echo count($resultados); ?> servicio(s) encontrado(s).
            <a href="servicios.php">Ver todos</a>
          </div>
        <?php else: ?>
          <div class="alerta alerta-error">
            No se encontraron servicios para "<?php echo e($busqueda); ?>".
            <a href="servicios.php">Ver todos</a>
          </div>
        <?php endif; ?>
      <?php elseif (count($resultados) === 0): ?>
        <div class="alerta alerta-error">
          Todavía no hay servicios cargados.
        </div>
      <?php endif; ?>


      <div id="aviso-busqueda"></div>

      <div class="grid-services" id="grid-servicios">

        <?php foreach ($resultados as $servicio): ?>

          <div class="card-service">

            <?php if (!empty($servicio['imagen'])): ?>
              <img src="img/<?php echo e($servicio['imagen']); ?>" alt="<?php echo e($servicio['nombre']); ?>" class="service-img" loading="lazy">
            <?php endif; ?>

            <h3>
              <?php echo e($servicio['nombre']); ?>
            </h3>

            <p>
              <?php echo e($servicio['descripcion']); ?>
            </p>

            <span class="price">
              $<?php echo e(number_format((float) $servicio['precio'], 0, ',', '.')); ?>
            </span>

          </div>

        <?php endforeach; ?>

      </div>

    </div>

  </section>

<?php include('includes/footer.php'); ?>
