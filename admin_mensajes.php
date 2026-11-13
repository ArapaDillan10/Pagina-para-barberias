<?php
  $titulo_pagina = 'DKBZA | Mensajes';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $mensaje_id = filter_input(INPUT_POST, 'mensaje_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $accion     = (string) ($_POST['accion'] ?? '');
    $msg        = 'error';

    if ($mensaje_id && $accion === 'eliminar') {
      $stmt = $pdo->prepare('DELETE FROM mensajes WHERE id = :id');
      $stmt->execute([':id' => $mensaje_id]);
      $msg = $stmt->rowCount() === 1 ? 'eliminado' : 'error';
    } elseif ($mensaje_id && in_array($accion, ['leido', 'no_leido'], true)) {
      $stmt = $pdo->prepare('UPDATE mensajes SET leido = :leido WHERE id = :id');
      $stmt->execute([':leido' => $accion === 'leido' ? 1 : 0, ':id' => $mensaje_id]);
      $msg = 'ok';
    }

    header('Location: admin_mensajes.php?msg=' . $msg);
    exit;
  }

  // Primero los no leídos, y dentro de cada grupo los más nuevos
  $mensajes = $pdo->query('SELECT id, nombre, email, mensaje, leido, created_at FROM mensajes ORDER BY leido, created_at DESC')->fetchAll();
  $sin_leer = count(array_filter($mensajes, fn($m) => !$m['leido']));

  $msg = filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? '';

  include('includes/header.php');
  include('includes/admin_nav.php');
?>

  <main class="admin-dashboard">

    <section class="admin-header">
      <div>
        <span class="section-tag">PANEL DE ADMINISTRACIÓN</span>
        <h1>Mensajes</h1>
        <p><?php echo $sin_leer; ?> sin leer de <?php echo count($mensajes); ?> en total</p>
      </div>
    </section>

    <?php if ($msg === 'ok'): ?>
      <div class="alerta alerta-exito">El mensaje se actualizó.</div>
    <?php elseif ($msg === 'eliminado'): ?>
      <div class="alerta alerta-exito">El mensaje se eliminó.</div>
    <?php elseif ($msg === 'error'): ?>
      <div class="alerta alerta-error">No se pudo hacer el cambio en ese mensaje.</div>
    <?php endif; ?>

    <section class="admin-recent">

      <div class="admin-table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>De</th>
              <th>Mensaje</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$mensajes): ?>
              <tr><td colspan="5">Todavía no llegó ningún mensaje.</td></tr>
            <?php endif; ?>

            <?php foreach ($mensajes as $m): ?>
              <tr>
                <td><?php echo e(date('d/m/Y H:i', strtotime($m['created_at']))); ?></td>
                <td>
                  <?php echo e($m['nombre']); ?><br>
                  <small><?php echo e($m['email']); ?></small>
                </td>
                <td><?php echo nl2br(e($m['mensaje'])); ?></td>
                <td>
                  <span class="booking-status estado-<?php echo $m['leido'] ? 'completado' : 'pendiente'; ?>">
                    <?php echo $m['leido'] ? 'Leído' : 'Nuevo'; ?>
                  </span>
                </td>
                <td>
                  <div class="form-inline">
                    <a href="mailto:<?php echo e($m['email']); ?>?subject=<?php echo rawurlencode('Re: tu consulta en DKBZA Barber Studio'); ?>" class="btn-mini">Responder</a>

                    <form method="post" action="admin_mensajes.php">
                      <?php echo csrf_campo(); ?>
                      <input type="hidden" name="mensaje_id" value="<?php echo e($m['id']); ?>">
                      <input type="hidden" name="accion" value="<?php echo $m['leido'] ? 'no_leido' : 'leido'; ?>">
                      <button type="submit" class="btn-mini"><?php echo $m['leido'] ? 'Marcar no leído' : 'Marcar leído'; ?></button>
                    </form>

                    <form method="post" action="admin_mensajes.php" onsubmit="return confirm('¿Eliminar este mensaje?');">
                      <?php echo csrf_campo(); ?>
                      <input type="hidden" name="mensaje_id" value="<?php echo e($m['id']); ?>">
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