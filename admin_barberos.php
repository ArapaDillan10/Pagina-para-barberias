<?php
  $titulo_pagina = 'DKBZA | Gestionar Barberos';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  // READ: todos los barberos, visibles y ocultos, con su cantidad de turnos
  $stmt = $pdo->prepare('SELECT b.id, b.nombre, b.especialidad, b.foto, b.estado, COUNT(t.id) AS turnos
                         FROM barberos b
                         LEFT JOIN turnos t ON t.barbero_id = b.id
                         GROUP BY b.id, b.nombre, b.especialidad, b.foto, b.estado
                         ORDER BY b.estado DESC, b.nombre ASC');
  $stmt->execute();
  $barberos = $stmt->fetchAll();

  $mensajes = [
    'creado'        => ['exito', 'El barbero se agregó correctamente.'],
    'editado'       => ['exito', 'El barbero se actualizó correctamente.'],
    'eliminado'     => ['exito', 'El barbero se eliminó correctamente.'],
    'ocultado'      => ['exito', 'El barbero tiene turnos, así que se ocultó en lugar de borrarse.'],
    'no_encontrado' => ['error', 'El barbero que buscás no existe.'],
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
          Gestionar barberos
        </h1>
        <p class="text-subtle">
          Los barberos visibles aparecen en Staff y se pueden elegir al reservar.
        </p>
      </div>

      <a href="crear_barbero.php" class="btn-blue">
        + Agregar barbero
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
              <th>Foto</th>
              <th>Barbero</th>
              <th>Turnos</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($barberos) === 0): ?>
              <tr>
                <td colspan="5">
                  Todavía no hay barberos cargados.
                </td>
              </tr>
            <?php endif; ?>

            <?php foreach ($barberos as $barbero): ?>
              <tr>
                <td>
                  <?php if (!empty($barbero['foto'])): ?>
                    <img src="img/<?php echo e($barbero['foto']); ?>" alt="<?php echo e($barbero['nombre']); ?>" class="admin-thumb">
                  <?php else: ?>
                    <span class="avatar-iniciales"><?php echo e(iniciales($barbero['nombre'])); ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?php echo e($barbero['nombre']); ?></strong>
                  <br>
                  <small class="text-subtle"><?php echo e($barbero['especialidad']); ?></small>
                </td>
                <td>
                  <?php echo (int) $barbero['turnos']; ?>
                </td>
                <td>
                  <?php if ($barbero['estado']): ?>
                    <span class="booking-status confirmed">Visible</span>
                  <?php else: ?>
                    <span class="booking-status pending">Oculto</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="admin-acciones">
                    <a href="editar_barbero.php?id=<?php echo (int) $barbero['id']; ?>" class="btn-ghost-sm">
                      Editar
                    </a>
                    <a href="eliminar_barbero.php?id=<?php echo (int) $barbero['id']; ?>" class="btn-danger">
                      <?php echo $barbero['turnos'] > 0 ? 'Ocultar' : 'Eliminar'; ?>
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
