<?php
  $titulo_pagina = 'DKBZA | Agregar Barbero';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  $errores = [];
  $barbero = ['nombre' => '', 'especialidad' => '', 'foto' => '', 'estado' => 1];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $barbero['nombre']       = trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW) ?? '');
    $barbero['especialidad'] = trim(filter_input(INPUT_POST, 'especialidad', FILTER_UNSAFE_RAW) ?? '');
    $barbero['estado']       = isset($_POST['estado']) ? 1 : 0;

    $errores = validar_barbero($barbero['nombre'], $barbero['especialidad']);

    if (empty($errores)) {
      $subida = subir_imagen($_FILES['foto'] ?? null, 'barberos');

      if (isset($subida['error'])) {
        $errores[] = $subida['error'];
      }
    }

    if (empty($errores)) {
      // CREATE con consulta preparada
      $sql = 'INSERT INTO barberos (nombre, especialidad, foto, estado)
              VALUES (:nombre, :especialidad, :foto, :estado)';
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':nombre'       => $barbero['nombre'],
        ':especialidad' => $barbero['especialidad'] !== '' ? $barbero['especialidad'] : null,
        ':foto'         => $subida['ruta'] ?? null,
        ':estado'       => $barbero['estado'],
      ]);

      header('Location: admin_barberos.php?msg=creado');
      exit;
    }
  }

  $accion_form = 'crear_barbero.php';
  $texto_boton = 'Guardar barbero';

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
          Agregar barbero
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
