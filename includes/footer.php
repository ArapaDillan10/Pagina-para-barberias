<!-- =========================================================
        FOOTER
        ========================================================= -->
  <footer class="footer">

    <div class="container footer-content">

        <span>
          © 2026 <?php echo htmlspecialchars(env('APP_NAME')); ?> · Todos los derechos reservados
          · <?php echo htmlspecialchars(env('APP_EMAIL')); ?>
        </span>
      <a href="<?php echo isset($_SESSION['usuario_id']) ? 'admin.php' : 'login.php?admin=1'; ?>" class="btn-ghost-sm">
        🔒 Panel admin
      </a>

    </div>

  </footer>



  <!-- JAVASCRIPT -->
  <script src="./js/script.js"></script>

</body>

</html>
