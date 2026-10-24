<?php
  $titulo_pagina = 'DKBZA | Eliminar Barbero';
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
    header('Location: admin_barberos.php?msg=no_encontrado');
    exit;
  }

  $stmt = $pdo->prepare('SELECT b.id, b.nombre, b.especialidad, b.foto, COUNT(t.id) AS turnos
                         FROM barberos b
                         LEFT JOIN turnos t ON t.barbero_id = b.id
                         WHERE b.id = :id
                         GROUP BY b.id, b.nombre, b.especialidad, b.foto');
  $stmt->execute([':id' => $id]);
  $barbero = $stmt->fetch();

  if (!$barbero) {
    header('Location: admin_barberos.php?msg=no_encontrado');
    exit;
  }

  // Un barbero con turnos no se borra (la FK lo impide): se oculta para no perder el historial
  $tiene_turnos = (int) $barbero['turnos'] > 0;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    if ($tiene_turnos) {
      $stmt = $pdo->prepare('UPDATE barberos SET estado = 0 WHERE id = :id');
      $stmt->execute([':id' => $id]);

      header('Location: admin_barberos.php?msg=ocultado');
      exit;
    }

    // DELETE con consulta preparada
    $stmt = $pdo->prepare('DELETE FROM barberos WHERE id = :id');
    $stmt->execute([':id' => $id]);

    borrar_imagen($barbero['foto']);

    header('Location: admin_barberos.php?msg=eliminado');
    exit;
  }

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
          <?php echo $tiene_turnos ? 'Ocultar barbero' : 'Eliminar barbero'; ?>
        </h1>
      </div>
    </section>

    <section class="admin-recent">

      <div class="confirmar-card">

        <?php if (!empty($barbero['foto'])): ?>
          <img src="img/<?php echo e($barbero['foto']); ?>" alt="<?php echo e($barbero['nombre']); ?>" class="admin-thumb">
        <?php endif; ?>

        <?php if ($tiene_turnos): ?>
          <p>
            <strong><?php echo e($barbero['nombre']); ?></strong> tiene <?php echo (int) $barbero['turnos']; ?> turno(s)
            en el historial, así que no se puede borrar. Lo podés <strong>ocultar</strong>: deja de aparecer
            en Staff y en Reservas, pero sus turnos se conservan.
          </p>
        <?php else: ?>
          <p>
            ¿Seguro que querés eliminar a <strong><?php echo e($barbero['nombre']); ?></strong>?
            Esta acción no se puede deshacer.
          </p>
        <?php endif; ?>

        <form method="post" action="eliminar_barbero.php" class="admin-acciones">
          <?php echo csrf_campo(); ?>
          <input type="hidden" name="id" value="<?php echo (int) $barbero['id']; ?>">

          <button type="submit" class="btn-danger">
            <?php echo $tiene_turnos ? 'Sí, ocultar' : 'Sí, eliminar'; ?>
          </button>

          <a href="admin_barberos.php" class="btn-ghost-sm">
            Cancelar
          </a>
        </form>

      </div>

    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
