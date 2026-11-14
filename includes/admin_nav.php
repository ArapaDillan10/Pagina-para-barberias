<?php
  // Aviso de reseñas que el admin todavía no vio (se apaga al entrar a admin_resenas.php)
  $nav_resenas_nuevas = (int) $pdo->query('SELECT COUNT(*) FROM resenas WHERE vista = 0')->fetchColumn();
?>
  <!-- =========================================================
       NAVBAR DEL ADMIN
       ========================================================= -->

  <header class="navbar">

    <div class="nav-container">

      <!-- LOGO -->

      <a href="admin.php" class="brand">

        <img src="./img/logo.png" alt="Logo DKBZA" class="brand-logo" />

        <div>

          <span class="brand-name">
            DKBZA
          </span>

          <span class="brand-sub">
            ADMIN PANEL
          </span>

        </div>

      </a>


      <!-- ACCESOS: en el celular el menú se abre con ☰ y Cerrar sesión queda siempre a la vista -->

      <div class="admin-nav-actions">

        <nav class="admin-nav-links" id="adminNav">

          <a href="admin.php" class="btn-ghost-sm">
            Dashboard
          </a>

          <a href="admin_turnos.php" class="btn-ghost-sm">
            Turnos
          </a>

          <a href="admin_mensajes.php" class="btn-ghost-sm">
            Mensajes
          </a>

          <a href="admin_resenas.php" class="btn-ghost-sm">
            Reseñas<?php if ($nav_resenas_nuevas > 0): ?> <span class="nav-badge"><?php echo $nav_resenas_nuevas; ?></span><?php endif; ?>
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

        </nav>

        <form method="post" action="logout.php" class="form-logout">
          <?php echo csrf_campo(); ?>
          <button type="submit" class="btn-danger">
            Cerrar sesión<span class="logout-nombre"> (<?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>)</span>
          </button>
        </form>

        <button type="button" class="nav-toggle" id="adminNavToggle" aria-label="Abrir menú" aria-expanded="false">☰<?php if ($nav_resenas_nuevas > 0): ?><span class="nav-badge-punto"></span><?php endif; ?></button>

      </div>

    </div>

  </header>

  <script>
    document.getElementById('adminNavToggle').addEventListener('click', function () {
      var abierto = document.getElementById('adminNav').classList.toggle('abierto');
      this.setAttribute('aria-expanded', abierto);
    });
  </script>