<?php
  $titulo_pagina = 'DKBZA | Reseñas';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $resena_id = filter_input(INPUT_POST, 'resena_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $accion    = (string) ($_POST['accion'] ?? '');
    $msg       = 'error';

    if ($resena_id && $accion === 'eliminar') {
      $stmt = $pdo->prepare('DELETE FROM resenas WHERE id = :id');
      $stmt->execute([':id' => $resena_id]);
      $msg = $stmt->rowCount() === 1 ? 'eliminada' : 'error';
    } elseif ($resena_id && in_array($accion, ['ocultar', 'mostrar'], true)) {
      $stmt = $pdo->prepare('UPDATE resenas SET visible = :visible WHERE id = :id');
      $stmt->execute([':visible' => $accion === 'mostrar' ? 1 : 0, ':id' => $resena_id]);
      $msg = 'ok';
    }

    header('Location: admin_resenas.php?msg=' . $msg);
    exit;
  }

  $resenas = $pdo->query(
    'SELECT r.id, r.puntaje, r.comentario, r.visible, r.vista, r.created_at,
            u.nombre AS cliente, u.email
     FROM resenas r
     INNER JOIN usuarios u ON u.id = r.usuario_id
     ORDER BY r.created_at DESC'
  )->fetchAll();

  // Al abrir esta página el admin ya las vio: se apaga el aviso del menú y del Dashboard
  $pdo->exec('UPDATE resenas SET vista = 1 WHERE vista = 0');

  $nuevas = count(array_filter($resenas, fn($r) => !$r['vista']));
  $msg = filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? '';

  include('includes/header.php');
  include('includes/admin_nav.php');
?>

  <main class="admin-dashboard">

    <section class="admin-header">
      <div>
        <span class="section-tag">PANEL DE ADMINISTRACIÓN</span>
        <h1>Reseñas</h1>
        <p><?php echo count($resenas); ?> en total<?php echo $nuevas ? ' · ' . $nuevas . ($nuevas === 1 ? ' nueva' : ' nuevas') . ' desde tu última visita' : ''; ?></p>
      </div>
      <a href="resenas.php" class="btn-ghost-sm">Ver la página pública</a>
    </section>

    <?php if ($msg === 'ok'): ?>
      <div class="alerta alerta-exito">La reseña se actualizó.</div>
    <?php elseif ($msg === 'eliminada'): ?>
      <div class="alerta alerta-exito">La reseña se eliminó.</div>
    <?php elseif ($msg === 'error'): ?>
      <div class="alerta alerta-error">No se pudo hacer el cambio en esa reseña.</div>
    <?php endif; ?>

    <section class="admin-recent">

      <div class="admin-table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Cliente</th>
              <th>Puntaje</th>
              <th>Reseña</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$resenas): ?>
              <tr><td colspan="6">Todavía no hay reseñas.</td></tr>
            <?php endif; ?>

            <?php foreach ($resenas as $r): ?>
              <tr>
                <td>
                  <?php echo e(date('d/m/Y H:i', strtotime($r['created_at']))); ?>
                  <?php if (!$r['vista']): ?><br><span class="nav-badge">Nueva</span><?php endif; ?>
                </td>
                <td>
                  <?php echo e($r['cliente']); ?><br>
                  <small><?php echo e($r['email']); ?></small>
                </td>
                <td><span class="estrellas-fijas" aria-label="<?php echo (int) $r['puntaje']; ?> de 5 estrellas"><?php echo str_repeat('★', (int) $r['puntaje']); ?><span class="estrella-vacia"><?php echo str_repeat('★', 5 - (int) $r['puntaje']); ?></span></span></td>
                <td><?php echo nl2br(e($r['comentario'])); ?></td>
                <td>
                  <span class="booking-status estado-<?php echo $r['visible'] ? 'completado' : 'cancelado'; ?>">
                    <?php echo $r['visible'] ? 'Publicada' : 'Oculta'; ?>
                  </span>
                </td>
                <td>
                  <div class="form-inline">
                    <form method="post" action="admin_resenas.php">
                      <?php echo csrf_campo(); ?>
                      <input type="hidden" name="resena_id" value="<?php echo e($r['id']); ?>">
                      <input type="hidden" name="accion" value="<?php echo $r['visible'] ? 'ocultar' : 'mostrar'; ?>">
                      <button type="submit" class="btn-mini"><?php echo $r['visible'] ? 'Ocultar' : 'Publicar'; ?></button>
                    </form>

                    <form method="post" action="admin_resenas.php" onsubmit="return confirm('¿Eliminar esta reseña?');">
                      <?php echo csrf_campo(); ?>
                      <input type="hidden" name="resena_id" value="<?php echo e($r['id']); ?>">
                      <input type="hidden" name="accion" value="eliminar">
                      <button type="submit" class="btn-mini peligro">Eliminar</button>
                    </form>
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