<?php
  $titulo_pagina = 'DKBZA | Agregar Servicio';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  $errores  = [];
  $servicio = ['nombre' => '', 'descripcion' => '', 'precio' => '', 'imagen' => '', 'estado' => 1];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $servicio['nombre']      = trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW) ?? '');
    $servicio['descripcion'] = trim(filter_input(INPUT_POST, 'descripcion', FILTER_UNSAFE_RAW) ?? '');
    $servicio['precio']      = trim(filter_input(INPUT_POST, 'precio', FILTER_UNSAFE_RAW) ?? '');
    $servicio['estado']      = isset($_POST['estado']) ? 1 : 0;

    $precio  = normalizar_precio($servicio['precio']);
    $errores = validar_servicio($servicio['nombre'], $precio);

    if (empty($errores)) {
      $subida = subir_imagen($_FILES['imagen'] ?? null);

      if (isset($subida['error'])) {
        $errores[] = $subida['error'];
      }
    }

    if (empty($errores)) {
      // CREATE con consulta preparada
      $sql = 'INSERT INTO servicios (nombre, descripcion, precio, imagen, estado)
              VALUES (:nombre, :descripcion, :precio, :imagen, :estado)';
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':nombre'      => $servicio['nombre'],
        ':descripcion' => $servicio['descripcion'],
        ':precio'      => $precio,
        ':imagen'      => $subida['ruta'] ?? null,
        ':estado'      => $servicio['estado'],
      ]);

      header('Location: admin_servicios.php?msg=creado');
      exit;
    }
  }

  $accion_form = 'crear_servicio.php';
  $texto_boton = 'Guardar servicio';

  include('includes/header.php');
  include('includes/admin_nav.php');
?>

  <main class="admin-dashboard">

    <section class="admin-header">
      <div>
        <span class="section-tag">
          SERVICIOS
        </span>
        <h1>
          Agregar servicio
        </h1>
        <p class="text-subtle">
          Los campos con * son obligatorios.
        </p>
      </div>
    </section>

    <section class="admin-recent">
      <?php include('includes/form_servicio.php'); ?>
    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
