<?php
  $titulo_pagina = 'DKBZA | Editar Barbero';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

  if (!$id) {
    header('Location: admin_barberos.php?msg=no_encontrado');
    exit;
  }

  $stmt = $pdo->prepare('SELECT id, nombre, especialidad, foto, estado FROM barberos WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $actual = $stmt->fetch();

  if (!$actual) {
    header('Location: admin_barberos.php?msg=no_encontrado');
    exit;
  }

  $errores = [];
  $barbero = $actual;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $barbero['nombre']       = trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW) ?? '');
    $barbero['especialidad'] = trim(filter_input(INPUT_POST, 'especialidad', FILTER_UNSAFE_RAW) ?? '');
    $barbero['estado']       = isset($_POST['estado']) ? 1 : 0;

    $errores = validar_barbero($barbero['nombre'], $barbero['especialidad']);
    $foto    = $actual['foto'];

    if (empty($errores)) {
      $subida = subir_imagen($_FILES['foto'] ?? null, 'barberos');

      if (isset($subida['error'])) {
        $errores[] = $subida['error'];
      } elseif (isset($subida['ruta'])) {
        $foto = $subida['ruta'];
      }
    }

    if (empty($errores)) {
      // UPDATE con consulta preparada
      $sql = 'UPDATE barberos
              SET nombre = :nombre, especialidad = :especialidad, foto = :foto, estado = :estado
              WHERE id = :id';
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':nombre'       => $barbero['nombre'],
        ':especialidad' => $barbero['especialidad'] !== '' ? $barbero['especialidad'] : null,
        ':foto'         => $foto,
        ':estado'       => $barbero['estado'],
        ':id'           => $id,
      ]);

      // Si se subió una foto nueva, se borra la anterior
      if ($foto !== $actual['foto']) {
        borrar_imagen($actual['foto']);
      }

      header('Location: admin_barberos.php?msg=editado');
      exit;
    }

    $barbero['foto'] = $actual['foto'];
  }

  $accion_form = 'editar_barbero.php?id=' . (int) $id;
  $texto_boton = 'Guardar cambios';

  include('includes/header.php');
  include('includes/admin_nav.php');
?>

  <main class="admin-dashboard">

    <section class="admin-header">
      <div>
        <span class="section-tag">
          BARBEROS
        </span>
        <h1>
          Editar barbero
        </h1>
        <p class="text-subtle">
          Los campos con * son obligatorios.
        </p>
      </div>
    </section>

    <section class="admin-recent">
      <?php include('includes/form_barbero.php'); ?>
    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
