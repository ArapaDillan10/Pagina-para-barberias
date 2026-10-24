<?php
  $titulo_pagina = 'DKBZA | Turnos';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';
  require_once __DIR__ . '/includes/turnos.php';

  // Desde qué estados se puede pasar a cada uno.
  $transiciones = [
    'confirmado' => ['pendiente'],
    'completado' => ['pendiente', 'confirmado'],
    'cancelado'  => ['pendiente', 'confirmado'],
  ];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $turno_id = filter_input(INPUT_POST, 'turno_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $estado   = (string) ($_POST['estado'] ?? '');
    $fecha    = (string) ($_POST['fecha'] ?? '');
    $msg      = 'error';

    if ($turno_id && isset($transiciones[$estado])) {
      $marcas = implode(',', array_fill(0, count($transiciones[$estado]), '?'));
      $stmt = $pdo->prepare("UPDATE turnos SET estado = ? WHERE id = ? AND estado IN ($marcas)");

      try {
        $stmt->execute(array_merge([$estado, $turno_id], $transiciones[$estado]));
        $msg = $stmt->rowCount() === 1 ? 'ok' : 'error';
      } catch (PDOException $ex) {
        if ($ex->getCode() !== '23000') {
          throw $ex;
        }
      }
    }

    $volver = fecha_valida_filtro($fecha) ? '&fecha=' . urlencode($fecha) : '';
    header('Location: admin_turnos.php?msg=' . $msg . $volver);
    exit;
  }

  function fecha_valida_filtro($fecha) {
    $dia = DateTime::createFromFormat('!Y-m-d', $fecha);
    return $dia && $dia->format('Y-m-d') === $fecha;
  }

  $fecha_filtro = (string) (filter_input(INPUT_GET, 'fecha', FILTER_UNSAFE_RAW) ?? '');
  if (!fecha_valida_filtro($fecha_filtro)) {
    $fecha_filtro = '';
  }

  $sql = "SELECT t.id, t.fecha, t.hora, t.estado, t.telefono, t.sena_monto, t.sena_comprobante, t.observaciones,
                 u.nombre AS cliente, u.email, b.nombre AS barbero,
                 GROUP_CONCAT(s.nombre ORDER BY s.nombre SEPARATOR ' + ') AS servicios,
                 SUM(ts.precio) AS total
          FROM turnos t
          INNER JOIN usuarios u        ON u.id = t.usuario_id
          INNER JOIN barberos b        ON b.id = t.barbero_id
          INNER JOIN turno_servicio ts ON ts.turno_id = t.id
          INNER JOIN servicios s       ON s.id = ts.servicio_id
          WHERE " . ($fecha_filtro !== '' ? 't.fecha = :fecha' : 't.fecha >= CURDATE()') . "
          GROUP BY t.id, t.fecha, t.hora, t.estado, t.telefono, t.sena_monto, t.sena_comprobante, t.observaciones, u.nombre, u.email, b.nombre
          ORDER BY t.fecha, t.hora, b.nombre";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($fecha_filtro !== '' ? [':fecha' => $fecha_filtro] : []);
  $turnos = $stmt->fetchAll();

  $msg = filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? '';

  $acciones = ['confirmado' => 'Confirmar', 'completado' => 'Completar', 'cancelado' => 'Cancelar'];

  include('includes/header.php');
  include('includes/admin_nav.php');
?>

  <main class="admin-dashboard">

    <section class="admin-header">
      <div>
        <span class="section-tag">PANEL DE ADMINISTRACIÓN</span>
        <h1>Turnos</h1>
        <p>
          <?php echo $fecha_filtro !== ''
            ? 'Turnos del ' . e(date('d/m/Y', strtotime($fecha_filtro)))
            : 'Próximos turnos (desde hoy)'; ?>
        </p>
      </div>
    </section>

    <?php if ($msg === 'ok'): ?>
      <div class="alerta alerta-exito">El estado del turno se actualizó.</div>
    <?php elseif ($msg === 'error'): ?>
      <div class="alerta alerta-error">No se pudo cambiar el estado de ese turno.</div>
    <?php endif; ?>

    <section class="admin-recent">

      <form method="get" action="admin_turnos.php" class="filtro-turnos">
        <label for="fecha">Ver un día:</label>
        <input type="date" id="fecha" name="fecha" class="wireframe-input" value="<?php echo e($fecha_filtro); ?>">
        <button type="submit" class="btn-blue">Filtrar</button>
        <?php if ($fecha_filtro !== ''): ?>
          <a href="admin_turnos.php" class="btn-ghost-sm">Ver próximos</a>
        <?php endif; ?>
      </form>

      <div class="admin-table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Cliente</th>
              <th>Barbero</th>
              <th>Servicios</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$turnos): ?>
              <tr><td colspan="8">No hay turnos para mostrar.</td></tr>
            <?php endif; ?>

            <?php foreach ($turnos as $turno): ?>
              <tr>
                <td><?php echo e(date('d/m/Y', strtotime($turno['fecha']))); ?></td>
                <td><?php echo e(substr($turno['hora'], 0, 5)); ?></td>
                <td>
                  <?php echo e($turno['cliente']); ?><br>
                  <small><?php echo e($turno['email']); ?></small>
                  <?php if ($turno['telefono']): ?>
                    <br><small>📱 <?php echo e($turno['telefono']); ?></small>
                  <?php endif; ?>
                  <?php if ($turno['observaciones']): ?>
                    <br><small>📝 <?php echo e($turno['observaciones']); ?></small>
                  <?php endif; ?>
                </td>
                <td><?php echo e($turno['barbero']); ?></td>
                <td><?php echo e($turno['servicios']); ?></td>
                <td>
                  <?php echo e(precio_formateado($turno['total'])); ?><br>
                  <?php if ($turno['sena_monto'] !== null): ?>
                    <small>Seña <?php echo e(precio_formateado($turno['sena_monto'])); ?></small><br>
                  <?php endif; ?>
                  <?php if ($turno['sena_comprobante']): ?>
                    <a href="comprobante.php?turno=<?php echo e($turno['id']); ?>" target="_blank" class="link-comprobante">Ver comprobante</a>
                  <?php endif; ?>
                </td>
                <td><span class="<?php echo e(clase_estado($turno['estado'])); ?>"><?php echo e(ESTADOS_TURNO[$turno['estado']]); ?></span></td>
                <td>
                  <div class="form-inline">
                    <?php foreach ($acciones as $estado => $texto): ?>
                      <?php if (in_array($turno['estado'], $transiciones[$estado], true)): ?>
                        <form method="post" action="admin_turnos.php"
                          <?php echo $estado === 'cancelado' ? 'onsubmit="return confirm(\'¿Cancelar este turno?\');"' : ''; ?>>
                          <?php echo csrf_campo(); ?>
                          <input type="hidden" name="turno_id" value="<?php echo e($turno['id']); ?>">
                          <input type="hidden" name="estado" value="<?php echo $estado; ?>">
                          <input type="hidden" name="fecha" value="<?php echo e($fecha_filtro); ?>">
                          <button type="submit" class="btn-mini<?php echo $estado === 'cancelado' ? ' peligro' : ''; ?>"><?php echo $texto; ?></button>
                        </form>
                      <?php endif; ?>
                    <?php endforeach; ?>
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
