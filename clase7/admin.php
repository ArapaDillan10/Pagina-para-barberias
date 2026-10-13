<?php
  $titulo_pagina = 'DKBZA | Panel de Administración';
  require_once __DIR__ . '/config/conexion.php';

  $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM servicios WHERE estado = :estado');
  $stmt->execute([':estado' => 1]);
  $total_servicios = (int) $stmt->fetch()['total'];

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
            8
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
            124
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
              8 turnos pendientes
            </span>

          </div>

          <button
            type="button"
            class="btn-blue admin-action">
            Ver reservas
          </button>

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
              4 barberos
            </span>

          </div>

          <button
            type="button"
            class="btn-blue admin-action">
            Ver barberos
          </button>

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
        Últimas reservas
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


            <!-- RESERVA 1 -->

            <tr>

              <td>
                Juan García
              </td>

              <td>
                Corte + Barba
              </td>

              <td>
                Mateo
              </td>

              <td>
                23/09/2026
              </td>

              <td>
                09:00
              </td>

              <td>
                <span class="booking-status confirmed">
                  Confirmado
                </span>
              </td>

            </tr>


            <!-- RESERVA 2 -->

            <tr>

              <td>
                Gonzalo M.
              </td>

              <td>
                Corte Clásico
              </td>

              <td>
                Dillan
              </td>

              <td>
                23/09/2026
              </td>

              <td>
                10:00
              </td>

              <td>
                <span class="booking-status pending">
                  Pendiente
                </span>
              </td>

            </tr>


            <!-- RESERVA 3 -->

            <tr>

              <td>
                Marcos R.
              </td>

              <td>
                Barba Completa
              </td>

              <td>
                Ezequiel
              </td>

              <td>
                23/09/2026
              </td>

              <td>
                11:00
              </td>

              <td>
                <span class="booking-status confirmed">
                  Confirmado
                </span>

              </td>

            </tr>


          </tbody>

        </table>

      </div>

    </section>



  </main>



<?php include('includes/admin_footer.php'); ?>
