<?php
  $titulo_pagina = 'DKBZA | Mi cuenta';
  require_once __DIR__ . '/includes/auth_guard.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';
  require_once __DIR__ . '/includes/turnos.php';

  $stmt = $pdo->prepare('SELECT nombre, email, rol, ultimo_acceso, created_at FROM usuarios WHERE id = :id');
  $stmt->execute([':id' => $_SESSION['usuario_id']]);
  $usuario = $stmt->fetch();

  $sql = "SELECT t.id, t.fecha, t.hora, t.estado, t.sena_monto, t.sena_comprobante, b.nombre AS barbero,
                 (t.estado IN ('pendiente', 'confirmado') AND TIMESTAMP(t.fecha, t.hora) > NOW()) AS cancelable,
                 GROUP_CONCAT(s.nombre ORDER BY s.nombre SEPARATOR ', ') AS servicios,
                 SUM(ts.precio) AS total
          FROM turnos t
          INNER JOIN barberos b        ON b.id = t.barbero_id
          INNER JOIN turno_servicio ts ON ts.turno_id = t.id
          INNER JOIN servicios s       ON s.id = ts.servicio_id
          WHERE t.usuario_id = :usuario_id
          GROUP BY t.id, t.fecha, t.hora, t.estado, t.sena_monto, t.sena_comprobante, b.nombre
          ORDER BY t.fecha DESC, t.hora DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':usuario_id' => $_SESSION['usuario_id']]);
  $turnos = $stmt->fetchAll();

  $sin_permiso = (filter_input(INPUT_GET, 'error', FILTER_UNSAFE_RAW) ?? '') === 'sin_permiso';

  $mensajes = [
    'reservado'    => ['exito', '¡Listo! Tu turno quedó reservado. Te esperamos.'],
    'cancelado'    => ['exito', 'Cancelaste el turno.'],
    'no_cancelado' => ['error', 'Ese turno ya no se puede cancelar.'],
  ];
  $mensaje = $mensajes[filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? ''] ?? null;

  include('includes/header.php');
?>

  <header class="navbar">

    <div class="nav-container">

      <a href="index.php" class="brand">

        <span class="brand-icon">💈</span>

        <div>

          <span class="brand-name">
            DKBZA
          </span>

          <span class="brand-sub">
            MI CUENTA
          </span>

        </div>

      </a>

      <div class="admin-nav-links">

        <?php if ($_SESSION['rol'] === 'admin'): ?>
          <a href="admin.php" class="btn-ghost-sm">
            Panel admin
          </a>
        <?php endif; ?>

        <a href="index.php" class="btn-ghost-sm">
          ← Volver al sitio
        </a>

        <form method="post" action="logout.php" class="form-logout">
          <?php echo csrf_campo(); ?>
          <button type="submit" class="btn-danger">
            Cerrar sesión
          </button>
        </form>

      </div>

    </div>

  </header>

  <main class="admin-dashboard">

    <section class="admin-header">

      <div>

        <span class="section-tag">
          MI CUENTA
        </span>

        <h1>
          Hola, <?php echo e($_SESSION['usuario_nombre']); ?>
        </h1>

      </div>

    </section>

    <?php if ($sin_permiso): ?>
      <div class="alerta alerta-error">
        No tenés permiso para entrar al panel de administración.
      </div>
    <?php endif; ?>

    <?php if ($mensaje): ?>
      <div class="alerta alerta-<?php echo $mensaje[0]; ?>">
        <?php echo e($mensaje[1]); ?>
      </div>
    <?php endif; ?>

    <section class="admin-recent">

      <h2>Mis datos</h2>

      <div class="admin-table-wrapper">
        <table class="admin-table">
          <tbody>
            <tr><th>Nombre</th><td><?php echo e($usuario['nombre']); ?></td></tr>
            <tr><th>Email</th><td><?php echo e($usuario['email']); ?></td></tr>
            <tr><th>Rol</th><td><?php echo e($usuario['rol']); ?></td></tr>
            <tr><th>Cuenta creada</th><td><?php echo e(date('d/m/Y', strtotime($usuario['created_at']))); ?></td></tr>
          </tbody>
        </table>
      </div>

    </section>

    <section class="admin-recent">

      <h2>Mis turnos</h2>

      <p class="margin-top"><a href="reservas.php" class="btn-blue">Reservar turno</a></p>

      <?php if (!$turnos): ?>
        <p>Todavía no tenés turnos reservados.</p>
      <?php else: ?>
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Barbero</th>
                <th>Servicios</th>
                <th>Total</th>
                <th>Estado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($turnos as $turno): ?>
                <tr>
                  <td><?php echo e(date('d/m/Y', strtotime($turno['fecha']))); ?></td>
                  <td><?php echo e(substr($turno['hora'], 0, 5)); ?></td>
                  <td><?php echo e($turno['barbero']); ?></td>
                  <td><?php echo e($turno['servicios']); ?></td>
                  <td>
                    <?php echo e(precio_formateado($turno['total'])); ?><br>
                    <?php if ($turno['sena_monto'] !== null): ?>
                      <small>Seña <?php echo e(precio_formateado($turno['sena_monto'])); ?></small>
                    <?php endif; ?>
                  </td>
                  <td><span class="<?php echo e(clase_estado($turno['estado'])); ?>"><?php echo e(ESTADOS_TURNO[$turno['estado']] ?? $turno['estado']); ?></span></td>
                  <td>
                    <?php if ($turno['cancelable']): ?>
                      <form method="post" action="cancelar_turno.php" onsubmit="return confirm('¿Cancelar este turno?');">
                        <?php echo csrf_campo(); ?>
                        <input type="hidden" name="turno_id" value="<?php echo e($turno['id']); ?>">
                        <button type="submit" class="btn-mini peligro">Cancelar</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    </section>

  </main>

<?php include('includes/admin_footer.php'); ?>
