<?php
  $pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!-- =========================================================
        1. NAVBAR
        ========================================================= -->
  <header class="navbar">

    <div class="nav-container">

      <!-- LOGO -->
      <a href="index.php" class="brand">

        <span class="brand-icon">💈</span>

        <div>
          <span class="brand-name">DKBZA</span>
          <span class="brand-sub">BARBER STUDIO</span>
        </div>

      </a>


      <!-- MENÚ -->
      <nav class="nav-links">

        <a href="nosotros.php" class="<?php echo $pagina_actual == 'nosotros.php' ? 'active' : ''; ?>">Nosotros</a>

        <a href="servicios.php" class="<?php echo $pagina_actual == 'servicios.php' ? 'active' : ''; ?>">Servicios</a>

        <a href="staff.php" class="<?php echo $pagina_actual == 'staff.php' ? 'active' : ''; ?>">Staff</a>

        <a href="galeria.php" class="<?php echo $pagina_actual == 'galeria.php' ? 'active' : ''; ?>">Galería</a>

        <a href="reservas.php" class="<?php echo $pagina_actual == 'reservas.php' ? 'active' : ''; ?>">Reservas</a>

        <a href="resenas.php" class="<?php echo $pagina_actual == 'resenas.php' ? 'active' : ''; ?>">Reseñas</a>

        <a href="tienda.php" class="<?php echo $pagina_actual == 'tienda.php' ? 'active' : ''; ?>">Tienda</a>

        <a href="contacto.php" class="<?php echo $pagina_actual == 'contacto.php' ? 'active' : ''; ?>">Contacto</a>

      </nav>


      <!-- BOTÓN RESERVAR -->
      <a href="reservas.php" class="btn-blue nav-btn">
        Reservar Turno
      </a>

    </div>

  </header>