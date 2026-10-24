<?php
  $titulo_pagina = 'DKBZA | Panel de Administración';
  require_once __DIR__ . '/includes/auth_guard.php';
  verificarRol('admin');
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';
  require_once __DIR__ . '/includes/turnos.php';

  $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM servicios WHERE estado = :estado');
  $stmt->execute([':estado' => 1]);
  $total_servicios = (int) $stmt->fetch()['total'];

  $reservas_hoy = (int) $pdo->query("SELECT COUNT(*) FROM turnos WHERE fecha = CURDATE() AND estado <> 'cancelado'")->fetchColumn();
  $total_clientes = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'usuario'")->fetchColumn();
  $turnos_pendientes = (int) $pdo->query("SELECT COUNT(*) FROM turnos WHERE estado = 'pendiente' AND fecha >= CURDATE()")->fetchColumn();
  $total_barberos = (int) $pdo->query('SELECT COUNT(*) FROM barberos WHERE estado = 1')->fetchColumn();

  $proximos_turnos = $pdo->query(
    "SELECT t.fecha, t.hora, t.estado, u.nombre AS cliente, b.nombre AS barbero,
            GROUP_CONCAT(s.nombre ORDER BY s.nombre SEPARATOR ' + ') AS servicios
     FROM turnos t
     INNER JOIN usuarios u        ON u.id = t.usuario_id
     INNER JOIN barberos b        ON b.id = t.barbero_id
     INNER JOIN turno_servicio ts ON ts.turno_id = t.id
     INNER JOIN servicios s       ON s.id = ts.servicio_id
     WHERE t.fecha >= CURDATE() AND t.estado <> 'cancelado'
     GROUP BY t.id, t.fecha, t.hora, t.estado, u.nombre, b.nombre
     ORDER BY t.fecha, t.hora
     LIMIT 5"
  )->fetchAll();

  include('includes/header.php');
  include('includes/admin_nav.php');
?>

  <!-- =========================================================
       CONTENIDO PRINCIPAL DEL DASHBOARD
       ========================================================= -->

  <main class="admin-dashboard">


    <!-- ENCABEZADO -->

    <section class="admin-header">

      <div>

        <span class="section-tag">
          PANEL DE ADMINISTRACIÓN
        </span>

        <h1>
          Dashboard DKBZA
        </h1>

        <p class="text-subtle">
          Gestioná las principales áreas de DKBZA Barber Studio.
        </p>

      </div>


      <div class="admin-status">

        <span class="status-dot"></span>

        Sistema activo

      </div>

    </section>



    <!-- =========================================================
         TARJETAS DE RESUMEN
         ========================================================= -->

    <section class="admin-stats">


      <!-- RESERVAS -->

      <div class="admin-stat-card">

        <span class="admin-stat-icon">
          📅
        </span>

        <div>

          <span class="admin-stat-label">
            RESERVAS HOY
          </span>

          <strong>
            <?php echo $reservas_hoy; ?>
          </strong>

          <small>
            turnos programados
          </small>

        </div>

      </div>



      <!-- CLIENTES -->

      <div class="admin-stat-card">

        <span class="admin-stat-icon">
          👤
        </span>

        <div>

          <span class="admin-stat-label">
            CLIENTES
          </span>

          <strong>
            <?php echo $total_clientes; ?>
          </strong>

          <small>
            clientes registrados
          </small>

        </div>

      </div>



      <!-- SERVICIOS -->

      <div class="admin-stat-card">

        <span class="admin-stat-icon">
          ✂️
        </span>

        <div>

          <span class="admin-stat-label">
            SERVICIOS
          </span>

          <strong>
            <?php echo $total_servicios; ?>
          </strong>

          <small>
            servicios disponibles
          </small>

        </div>

      </div>



      <!-- PRODUCTOS -->

      <div class="admin-stat-card">

        <span class="admin-stat-icon">
          🛒
        </span>

        <div>

          <span class="admin-stat-label">
            PRODUCTOS
          </span>

          <strong>
            2
          </strong>

          <small>
            productos en tienda
          </small>

        </div>

      </div>


    </section>



    <!-- =========================================================
         MENÚ DE ADMINISTRACIÓN
         ========================================================= -->

    <section class="admin-menu">

      <span class="section-tag">
        ADMINISTRACIÓN
      </span>

      <h2>
        Gestionar DKBZA
      </h2>


      <div class="admin-menu-grid">


        <!-- =====================================================
             RESERVAS
             ===================================================== -->

        <div class="admin-card">

          <div class="admin-card-icon">
            📅
          </div>

          <div class="admin-card-content">

            <h3>
              Reservas
            </h3>

            <p>
              Consultá los turnos y reservas de los clientes.
            </p>

            <span class="admin-card-info">
              <?php echo $turnos_pendientes; ?> turnos pendientes
            </span>

          </div>

          <a href="admin_turnos.php" class="btn-blue admin-action">
            Ver reservas
          </a>

        </div>



        <!-- =====================================================
             BARBEROS
             ===================================================== -->

        <div class="admin-card">

          <div class="admin-card-icon">
            ✂️
          </div>

          <div class="admin-card-content">

            <h3>
              Barberos
            </h3>

            <p>
              Administrá el equipo profesional de DKBZA.
            </p>

            <span class="admin-card-info">
              <?php echo $total_barberos; ?> barberos
            </span>

          </div>

          <a href="admin_barberos.php" class="btn-blue admin-action">
            Gestionar barberos
          </a>

        </div>



        <!-- =====================================================
             SERVICIOS
             ===================================================== -->

        <div class="admin-card">

          <div class="admin-card-icon">
            💈
          </div>

          <div class="admin-card-content">

            <h3>
              Servicios
            </h3>

            <p>
              Administrá los cortes, barba y combos disponibles.
            </p>

            <span class="admin-card-info">
              <?php echo $total_servicios; ?> servicios
            </span>

          </div>

          <a href="admin_servicios.php" class="btn-blue admin-action">
            Gestionar servicios
          </a>

        </div>



        <!-- =====================================================
             TIENDA
             ===================================================== -->

        <div class="admin-card">

          <div class="admin-card-icon">
            🛒
          </div>

          <div class="admin-card-content">

            <h3>
              Tienda
            </h3>

            <p>
              Administrá los productos y precios de la tienda.
            </p>

            <span class="admin-card-info">
              2 productos
            </span>

          </div>

          <button
            type="button"
            class="btn-blue admin-action">
            Ver tienda
          </button>

        </div>



        <!-- =====================================================
             RESEÑAS
             ===================================================== -->

        <div class="admin-card">

          <div class="admin-card-icon">
            ⭐
          </div>

          <div class="admin-card-content">

            <h3>
              Reseñas
            </h3>

            <p>
              Consultá las opiniones y comentarios de los clientes.
            </p>

            <span class="admin-card-info">
              1 reseña nueva
            </span>

          </div>

          <button
            type="button"
            class="btn-blue admin-action">
            Ver reseñas
          </button>

        </div>



        <!-- =====================================================
             CONTACTO
             ===================================================== -->

        <div class="admin-card">

          <div class="admin-card-icon">
            📩
          </div>

          <div class="admin-card-content">

            <h3>
              Mensajes
            </h3>

            <p>
              Revisá los mensajes enviados desde el formulario.
            </p>

            <span class="admin-card-info">
              3 mensajes nuevos
            </span>

          </div>

          <button
            type="button"
            class="btn-blue admin-action">
            Ver mensajes
          </button>

        </div>


      </div>

    </section>



    <!-- =========================================================
         RESERVAS RECIENTES
         ========================================================= -->

    <section class="admin-recent">

      <span class="section-tag">
        ACTIVIDAD RECIENTE
      </span>

      <h2>
        Próximos turnos
      </h2>


      <div class="admin-table-wrapper">

        <table class="admin-table">

          <thead>

            <tr>

              <th>
                Cliente
              </th>

              <th>
                Servicio
              </th>

              <th>
                Barbero
              </th>

              <th>
                Fecha
              </th>

              <th>
                Hora
              </th>

              <th>
                Estado
              </th>

            </tr>

          </thead>


          <tbody>
            <?php if (!$proximos_turnos): ?>
              <tr><td colspan="6">No hay turnos próximos.</td></tr>
            <?php endif; ?>
            <?php foreach ($proximos_turnos as $turno): ?>
              <tr>
                <td><?php echo e($turno['cliente']); ?></td>
                <td><?php echo e($turno['servicios']); ?></td>
                <td><?php echo e($turno['barbero']); ?></td>
                <td><?php echo e(date('d/m/Y', strtotime($turno['fecha']))); ?></td>
                <td><?php echo e(substr($turno['hora'], 0, 5)); ?></td>
                <td><span class="<?php echo e(clase_estado($turno['estado'])); ?>"><?php echo e(ESTADOS_TURNO[$turno['estado']]); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>

        </table>

      </div>

    </section>



  </main>



<?php include('includes/admin_footer.php'); ?>
