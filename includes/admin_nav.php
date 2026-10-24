  <!-- =========================================================
       NAVBAR DEL ADMIN
       ========================================================= -->

  <header class="navbar">

    <div class="nav-container">

      <!-- LOGO -->

      <a href="admin.php" class="brand">

        <span class="brand-icon">💈</span>

        <div>

          <span class="brand-name">
            DKBZA
          </span>

          <span class="brand-sub">
            ADMIN PANEL
          </span>

        </div>

      </a>


      <!-- ACCESOS -->

      <div class="admin-nav-links">

        <a href="admin.php" class="btn-ghost-sm">
          Dashboard
        </a>

        <a href="admin_turnos.php" class="btn-ghost-sm">
          Turnos
        </a>

        <a href="admin_barberos.php" class="btn-ghost-sm">
          Barberos
        </a>

        <a href="admin_servicios.php" class="btn-ghost-sm">
          Servicios
        </a>

        <a href="index.php" class="btn-ghost-sm">
          ← Volver al sitio
        </a>

        <form method="post" action="logout.php" class="form-logout">
          <?php echo csrf_campo(); ?>
          <button type="submit" class="btn-danger">
            Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>)
          </button>
        </form>

      </div>

    </div>

  </header>
