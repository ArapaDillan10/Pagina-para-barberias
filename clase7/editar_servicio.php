<?php
  $titulo_pagina = 'DKBZA | Editar Servicio';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

  if (!$id) {
    header('Location: admin_servicios.php?msg=no_encontrado');
    exit;
  }

  $stmt = $pdo->prepare('SELECT id, nombre, descripcion, precio, imagen, estado FROM servicios WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $actual = $stmt->fetch();

  if (!$actual) {
    header('Location: admin_servicios.php?msg=no_encontrado');
    exit;
  }

  $errores  = [];
  $servicio = $actual;
  $servicio['precio'] = (string) (float) $actual['precio'];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicio['nombre']      = trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW) ?? '');
    $servicio['descripcion'] = trim(filter_input(INPUT_POST, 'descripcion', FILTER_UNSAFE_RAW) ?? '');
    $servicio['precio']      = trim(filter_input(INPUT_POST, 'precio', FILTER_UNSAFE_RAW) ?? '');
    $servicio['estado']      = isset($_POST['estado']) ? 1 : 0;

    $precio  = normalizar_precio($servicio['precio']);
    $errores = validar_servicio($servicio['nombre'], $precio);
    $imagen  = $actual['imagen'];

    if (empty($errores)) {
      $subida = subir_imagen($_FILES['imagen'] ?? null);

      if (isset($subida['error'])) {
        $errores[] = $subida['error'];
      } elseif (isset($subida['ruta'])) {
        $imagen = $subida['ruta'];
      }
    }

    if (empty($errores)) {
      // UPDATE con consulta preparada
      $sql = 'UPDATE servicios
              SET nombre = :nombre, descripcion = :descripcion, precio = :precio, imagen = :imagen, estado = :estado
              WHERE id = :id';
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':nombre'      => $servicio['nombre'],
        ':descripcion' => $servicio['descripcion'],
        ':precio'      => $precio,
        ':imagen'      => $imagen,
        ':estado'      => $servicio['estado'],
        ':id'          => $id,
      ]);

      if ($imagen !== $actual['imagen']) {
        borrar_imagen($actual['imagen']);
      }

      header('Location: admin_servicios.php?msg=editado');
      exit;
    }
  }

  $accion_form = 'editar_servicio.php?id=' . $id;
  $texto_boton = 'Guardar cambios';

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
          Editar servicio
        </h1>
        <p class="text-subtle">
          Estás editando: <strong><?php echo e($actual['nombre']); ?></strong>
        </p>
      </div>
    </section>

    <section class="admin-recent">
      <?php include('includes/form_servicio.php'); ?>
    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
