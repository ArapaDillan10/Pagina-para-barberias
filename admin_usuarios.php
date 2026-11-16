<?php
  $titulo_pagina = 'DKBZA | Usuarios';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $accion     = (string) ($_POST['accion'] ?? '');
    $msg        = 'error';

    // Con tu propia cuenta no se puede hacer nada desde acá: así nunca te quedás
    // afuera del panel y siempre queda al menos un admin (vos).
    if ($usuario_id && $usuario_id === (int) $_SESSION['usuario_id']) {
      $msg = 'propia';
    } elseif ($usuario_id && in_array($accion, ['hacer_admin', 'hacer_usuario'], true)) {
      $stmt = $pdo->prepare('UPDATE usuarios SET rol = :rol WHERE id = :id');
      $stmt->execute([':rol' => $accion === 'hacer_admin' ? 'admin' : 'usuario', ':id' => $usuario_id]);
      $msg = 'rol';
    } elseif ($usuario_id && $accion === 'activar') {
      $stmt = $pdo->prepare('UPDATE usuarios SET estado = 1 WHERE id = :id');
      $stmt->execute([':id' => $usuario_id]);
      $msg = 'activado';
    } elseif ($usuario_id && $accion === 'eliminar') {
      // Con turnos o reseñas no se borra (en el hosting no hay FK que borre en cascada):
      // se desactiva para no perder el historial, igual que con los barberos.
      $stmt = $pdo->prepare('SELECT (SELECT COUNT(*) FROM turnos  WHERE usuario_id = :id1)
                                  + (SELECT COUNT(*) FROM resenas WHERE usuario_id = :id2)');
      $stmt->execute([':id1' => $usuario_id, ':id2' => $usuario_id]);

      if ((int) $stmt->fetchColumn() > 0) {
        $stmt = $pdo->prepare('UPDATE usuarios SET estado = 0 WHERE id = :id');
        $stmt->execute([':id' => $usuario_id]);
        $msg = 'desactivado';
      } else {
        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => $usuario_id]);
        $msg = $stmt->rowCount() === 1 ? 'eliminado' : 'error';
      }
    }

    header('Location: admin_usuarios.php?msg=' . $msg);
    exit;
  }

  // El teléfono no está en usuarios: se toma el que el cliente puso en su último turno
  $usuarios = $pdo->query(
    "SELECT u.id, u.nombre, u.email, u.rol, u.estado, u.created_at,
            (SELECT COUNT(*) FROM turnos t WHERE t.usuario_id = u.id) AS turnos,
            (SELECT t.telefono FROM turnos t
             WHERE t.usuario_id = u.id AND t.telefono IS NOT NULL AND t.telefono <> ''
             ORDER BY t.created_at DESC, t.id DESC
             LIMIT 1) AS telefono
     FROM usuarios u
     ORDER BY u.id"
  )->fetchAll();

  $total_admins = count(array_filter($usuarios, fn($u) => $u['rol'] === 'admin'));

  $mensajes = [
    'rol'         => ['exito', 'Se cambió el rol. Toma al instante, aunque esa persona tenga la sesión abierta.'],
    'eliminado'   => ['exito', 'La cuenta se eliminó.'],
    'desactivado' => ['exito', 'La cuenta tenía turnos o reseñas, así que se desactivó: ya no puede entrar, pero su historial queda.'],
    'activado'    => ['exito', 'La cuenta se volvió a activar.'],
    'propia'      => ['error', 'No podés cambiar el rol ni eliminar tu propia cuenta.'],
    'error'       => ['error', 'No se pudo hacer el cambio en esa cuenta.'],
  ];
  $mensaje = $mensajes[filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? ''] ?? null;

  include('includes/header.php');
  include('includes/admin_nav.php');
?>

  <main class="admin-dashboard">

    <section class="admin-header">
      <div>
        <span class="section-tag">PANEL DE ADMINISTRACIÓN</span>
        <h1>Usuarios</h1>
        <p><?php echo count($usuarios); ?> cuentas en total, <?php echo $total_admins; ?> admin(s)</p>
      </div>
    </section>

    <?php if ($mensaje): ?>
      <div class="alerta alerta-<?php echo $mensaje[0]; ?>"><?php echo e($mensaje[1]); ?></div>
    <?php endif; ?>

    <section class="admin-recent">

      <div class="admin-table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Usuario</th>
              <th>Teléfono</th>
              <th>Turnos</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Alta</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
              <?php $es_vos = (int) $u['id'] === (int) $_SESSION['usuario_id']; ?>
              <tr>
                <td><?php echo (int) $u['id']; ?></td>
                <td>
                  <?php echo e($u['nombre']); ?><?php if ($es_vos): ?> <small>(vos)</small><?php endif; ?><br>
                  <small><?php echo e($u['email']); ?></small>
                </td>
                <td><?php echo $u['telefono'] ? e($u['telefono']) : '—'; ?></td>
                <td><?php echo (int) $u['turnos']; ?></td>
                <td>
                  <span class="booking-status estado-<?php echo $u['rol'] === 'admin' ? 'confirmado' : 'pendiente'; ?>">
                    <?php echo $u['rol'] === 'admin' ? 'Admin' : 'Usuario'; ?>
                  </span>
                </td>
                <td>
                  <span class="booking-status estado-<?php echo $u['estado'] ? 'completado' : 'cancelado'; ?>">
                    <?php echo $u['estado'] ? 'Activa' : 'Desactivada'; ?>
                  </span>
                </td>
                <td><?php echo e(date('d/m/Y', strtotime($u['created_at']))); ?></td>
                <td>
                  <?php if ($es_vos): ?>
                    <small>Es tu cuenta</small>
                  <?php else: ?>
                    <div class="form-inline">
                      <form method="post" action="admin_usuarios.php"
                        data-confirmar="<?php echo $u['rol'] === 'admin' ? '¿Sacarle el rol de admin a ' . e($u['nombre']) . '? Ya no va a poder entrar al panel.' : '¿Darle el rol de admin a ' . e($u['nombre']) . '? Va a poder entrar al panel.'; ?>"
                        data-confirmar-boton="<?php echo $u['rol'] === 'admin' ? 'Sí, hacer usuario' : 'Sí, hacer admin'; ?>">
                        <?php echo csrf_campo(); ?>
                        <input type="hidden" name="usuario_id" value="<?php echo (int) $u['id']; ?>">
                        <input type="hidden" name="accion" value="<?php echo $u['rol'] === 'admin' ? 'hacer_usuario' : 'hacer_admin'; ?>">
                        <button type="submit" class="btn-mini"><?php echo $u['rol'] === 'admin' ? 'Hacer usuario' : 'Hacer admin'; ?></button>
                      </form>

                      <?php if ($u['estado']): ?>
                        <form method="post" action="admin_usuarios.php"
                          data-confirmar="¿Eliminar la cuenta de <?php echo e($u['nombre']); ?>? Si tiene turnos o reseñas, se desactiva en vez de borrarse."
                          data-confirmar-boton="Sí, eliminar" data-confirmar-peligro>
                          <?php echo csrf_campo(); ?>
                          <input type="hidden" name="usuario_id" value="<?php echo (int) $u['id']; ?>">
                          <input type="hidden" name="accion" value="eliminar">
                          <button type="submit" class="btn-mini peligro">Eliminar</button>
                        </form>
                      <?php else: ?>
                        <form method="post" action="admin_usuarios.php">
                          <?php echo csrf_campo(); ?>
                          <input type="hidden" name="usuario_id" value="<?php echo (int) $u['id']; ?>">
                          <input type="hidden" name="accion" value="activar">
                          <button type="submit" class="btn-mini">Reactivar</button>
                        </form>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>