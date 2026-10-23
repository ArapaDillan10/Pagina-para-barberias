<?php
  $titulo_pagina = 'DKBZA | Eliminar Servicio';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  // El borrado solo se hace por POST, después de confirmar
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
  } else {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
  }

  if (!$id) {
    header('Location: admin_servicios.php?msg=no_encontrado');
    exit;
  }

  $stmt = $pdo->prepare('SELECT id, nombre, precio, imagen FROM servicios WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $servicio = $stmt->fetch();

  if (!$servicio) {
    header('Location: admin_servicios.php?msg=no_encontrado');
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    // DELETE con consulta preparada
    $stmt = $pdo->prepare('DELETE FROM servicios WHERE id = :id');
    $stmt->execute([':id' => $id]);

    borrar_imagen($servicio['imagen']);

    header('Location: admin_servicios.php?msg=eliminado');
    exit;
  }

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
          Eliminar servicio
        </h1>
      </div>
    </section>

    <section class="admin-recent">

      <div class="confirmar-card">

        <?php if (!empty($servicio['imagen'])): ?>
          <img src="img/<?php echo e($servicio['imagen']); ?>" alt="<?php echo e($servicio['nombre']); ?>" class="admin-thumb">
        <?php endif; ?>

        <p>
          ¿Seguro que querés eliminar <strong><?php echo e($servicio['nombre']); ?></strong>
          (<?php echo e(precio_formateado($servicio['precio'])); ?>)?
          Esta acción no se puede deshacer.
        </p>

        <form method="post" action="eliminar_servicio.php" class="admin-acciones">
          <?php echo csrf_campo(); ?>
          <input type="hidden" name="id" value="<?php echo (int) $servicio['id']; ?>">

          <button type="submit" class="btn-danger">
            Sí, eliminar
          </button>

          <a href="admin_servicios.php" class="btn-ghost-sm">
            Cancelar
          </a>
        </form>

      </div>

    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
