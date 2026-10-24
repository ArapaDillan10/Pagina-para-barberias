<?php
  $titulo_pagina = 'DKBZA Barber Studio | Staff';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  $stmt = $pdo->prepare('SELECT nombre, especialidad, foto FROM barberos WHERE estado = :estado ORDER BY id');
  $stmt->execute([':estado' => 1]);
  $barberos = $stmt->fetchAll();

  include('includes/header.php');
  include('includes/nav.php');
?>

  <!-- STAFF -->

  <section class="section-dark">

    <div class="container">

      <span class="section-tag">
        EQUIPO PROFESIONAL
      </span>

      <h1>
        Nuestros Barberos
      </h1>

      <div class="grid-staff">

        <?php if (!$barberos): ?>
          <p>Muy pronto vas a conocer a nuestro equipo.</p>
        <?php endif; ?>

        <?php foreach ($barberos as $barbero): ?>
          <div class="card-staff">

            <?php if (!empty($barbero['foto'])): ?>
              <img src="img/<?php echo e($barbero['foto']); ?>" alt="Foto de <?php echo e($barbero['nombre']); ?>" class="staff-foto">
            <?php else: ?>
              <div class="avatar-placeholder">
                <?php echo e(iniciales($barbero['nombre'])); ?>
              </div>
            <?php endif; ?>

            <h3>
              <?php echo e($barbero['nombre']); ?>
            </h3>

            <p>
              <?php echo e($barbero['especialidad']); ?>
            </p>

          </div>
        <?php endforeach; ?>

      </div>

    </div>

  </section>

<?php include('includes/footer.php'); ?>
