<?php
  $titulo_pagina = 'DKBZA | Gestionar Servicios';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  // READ: todos los servicios, visibles y ocultos
  $stmt = $pdo->prepare('SELECT id, nombre, descripcion, precio, imagen, estado FROM servicios ORDER BY id ASC');
  $stmt->execute();
  $servicios = $stmt->fetchAll();

  $mensajes = [
    'creado'        => ['exito', 'El servicio se agregó correctamente.'],
    'editado'       => ['exito', 'El servicio se actualizó correctamente.'],
    'eliminado'     => ['exito', 'El servicio se eliminó correctamente.'],
    'no_encontrado' => ['error', 'El servicio que buscás no existe.'],
  ];

  $msg = filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? '';
  $mensaje = $mensajes[$msg] ?? null;

  include('includes/header.php');
  include('includes/admin_nav.php');
?>

  <main class="admin-dashboard">

    <section class="admin-header">

      <div>

        <span class="section-tag">
          PANEL DE ADMINISTRACIÓN
        </span>

        <h1>
          Gestionar servicios
        </h1>

        <p class="text-subtle">
          Agregá, editá o eliminá los servicios que se muestran en la web.
        </p>

      </div>

      <a href="crear_servicio.php" class="btn-blue">
        + Agregar servicio
      </a>

    </section>

    <?php if ($mensaje): ?>
      <div class="alerta alerta-<?php echo e($mensaje[0]); ?>">
        <?php echo e($mensaje[1]); ?>
      </div>
    <?php endif; ?>

    <section class="admin-recent">

      <div class="admin-table-wrapper">

        <table class="admin-table">

          <thead>
            <tr>
              <th>Imagen</th>
              <th>Servicio</th>
              <th>Precio</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>

            <?php if (count($servicios) === 0): ?>
              <tr>
                <td colspan="5">
                  Todavía no hay servicios cargados.
                </td>
              </tr>
            <?php endif; ?>

            <?php foreach ($servicios as $servicio): ?>
              <tr>

                <td>
                  <?php if (!empty($servicio['imagen'])): ?>
                    <img src="img/<?php echo e($servicio['imagen']); ?>" alt="<?php echo e($servicio['nombre']); ?>" class="admin-thumb">
                  <?php else: ?>
                    <span class="text-subtle">Sin imagen</span>
                  <?php endif; ?>
                </td>

                <td>
                  <strong><?php echo e($servicio['nombre']); ?></strong>
                  <br>
                  <small class="text-subtle"><?php echo e($servicio['descripcion']); ?></small>
                </td>

                <td>
                  <?php echo e(precio_formateado($servicio['precio'])); ?>
                </td>

                <td>
                  <?php if ($servicio['estado']): ?>
                    <span class="booking-status confirmed">Visible</span>
                  <?php else: ?>
                    <span class="booking-status pending">Oculto</span>
                  <?php endif; ?>
                </td>

                <td>
                  <div class="admin-acciones">
                    <a href="editar_servicio.php?id=<?php echo (int) $servicio['id']; ?>" class="btn-ghost-sm">
                      Editar
                    </a>
                    <a href="eliminar_servicio.php?id=<?php echo (int) $servicio['id']; ?>" class="btn-danger">
                      Eliminar
                    </a>
                  </div>
                </td>

              </tr>
            <?php endforeach; ?>

          </tbody>

        </table>

      </div>

    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
