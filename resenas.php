<?php
  $titulo_pagina = 'DKBZA Barber Studio | Reseñas';
  $descripcion_pagina = 'Opiniones reales de clientes de DKBZA Barber Studio en Salta Capital.';
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';

  const RESENA_MINIMO = 10;
  const RESENA_MAXIMO = 500;
  const RESENAS_POR_HORA = 3;

  // "Lionel Andrés Messi" -> "Lionel M."
  function nombre_corto($nombre) {
    $partes = preg_split('/\s+/', trim($nombre));

    if (count($partes) > 1 && preg_match('/^\p{L}/u', end($partes), $letra)) {
      return $partes[0] . ' ' . strtoupper($letra[0]) . '.';
    }

    return $partes[0];
  }

  function estrellas_html($puntaje) {
    $puntaje = max(0, min(5, (int) $puntaje));
    return '<span class="estrellas-fijas" aria-label="' . $puntaje . ' de 5 estrellas">'
         . str_repeat('★', $puntaje)
         . '<span class="estrella-vacia">' . str_repeat('★', 5 - $puntaje) . '</span>'
         . '</span>';
  }

  $logueado   = isset($_SESSION['usuario_id']);
  $errores    = [];
  $puntaje    = 0;
  $comentario = '';
  $publicada  = ($_GET['publicada'] ?? '') === '1';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    if (!$logueado) {
      header('Location: login.php');
      exit;
    }

    $puntaje    = (int) filter_input(INPUT_POST, 'puntaje', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
    $comentario = trim((string) ($_POST['comentario'] ?? ''));

    if ($puntaje < 1) {
      $errores[] = 'Elegí de 1 a 5 estrellas.';
    }

    if (strlen($comentario) < RESENA_MINIMO) {
      $errores[] = 'La reseña debe tener al menos ' . RESENA_MINIMO . ' caracteres.';
    } elseif (strlen($comentario) > RESENA_MAXIMO) {
      $errores[] = 'La reseña no puede superar los ' . RESENA_MAXIMO . ' caracteres.';
    }

    // Freno contra el spam: como mucho RESENAS_POR_HORA por cuenta
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM resenas WHERE usuario_id = :usuario AND created_at > NOW() - INTERVAL 1 HOUR');
    $stmt->execute([':usuario' => $_SESSION['usuario_id']]);
    if ((int) $stmt->fetchColumn() >= RESENAS_POR_HORA) {
      $errores[] = 'Ya publicaste varias reseñas en la última hora. Probá de nuevo más tarde.';
    }

    if (!$errores) {
      $stmt = $pdo->prepare('INSERT INTO resenas (usuario_id, puntaje, comentario) VALUES (:usuario, :puntaje, :comentario)');
      $stmt->execute([':usuario' => $_SESSION['usuario_id'], ':puntaje' => $puntaje, ':comentario' => $comentario]);

      header('Location: resenas.php?publicada=1#lista-resenas');
      exit;
    }
  }

  $resumen = $pdo->query('SELECT COUNT(*) AS total, AVG(puntaje) AS promedio FROM resenas WHERE visible = 1')->fetch();
  $total_resenas = (int) $resumen['total'];

  $resenas = $pdo->query(
    'SELECT r.puntaje, r.comentario, r.created_at, u.nombre AS cliente
     FROM resenas r
     INNER JOIN usuarios u ON u.id = r.usuario_id
     WHERE r.visible = 1
     ORDER BY r.created_at DESC'
  )->fetchAll();

  include('includes/header.php');
  include('includes/nav.php');
?>

  <!-- RESEÑAS -->

  <section class="section-card">

    <div class="container">

      <span class="section-tag">
        FEEDBACK DE CLIENTES
      </span>

      <h1>
        Lo que dicen de DKBZA
      </h1>

      <?php if ($total_resenas > 0): ?>
        <div class="resenas-resumen">
          <span class="resenas-promedio"><?php echo number_format((float) $resumen['promedio'], 1, ',', ''); ?></span>
          <div>
            <?php echo estrellas_html(round((float) $resumen['promedio'])); ?>
            <p class="text-subtle"><?php echo $total_resenas; ?> <?php echo $total_resenas === 1 ? 'reseña' : 'reseñas'; ?></p>
          </div>
        </div>
      <?php endif; ?>


      <!-- DEJAR RESEÑA -->

      <div class="resena-box" id="dejar-resena">

        <?php if (!$logueado): ?>

          <h2>¿Querés dejar tu reseña?</h2>
          <p class="text-subtle"><a href="login.php">Iniciá sesión</a> o <a href="registro.php">creá tu cuenta</a> para contarnos cómo te fue.</p>

        <?php else: ?>

          <?php if ($publicada): ?>
            <div class="alerta alerta-exito">¡Gracias! Tu reseña ya está publicada.</div>
          <?php endif; ?>

          <h2>¿Cómo fue tu experiencia?</h2>
          <p class="text-subtle">Publicás como <strong><?php echo e(nombre_corto($_SESSION['usuario_nombre'])); ?></strong></p>

          <?php if ($errores): ?>
            <div class="alerta alerta-error">
              <ul>
                <?php foreach ($errores as $error): ?>
                  <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" action="resenas.php#dejar-resena" class="resena-form">

            <?php echo csrf_campo(); ?>

            <fieldset class="estrellas-input">
              <legend>Tu puntaje</legend>
              <div class="estrellas-opciones">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                  <input type="radio" id="estrella-<?php echo $i; ?>" name="puntaje" value="<?php echo $i; ?>" <?php echo $puntaje === $i ? 'checked' : ''; ?> required>
                  <label for="estrella-<?php echo $i; ?>" title="<?php echo $i; ?> de 5">★</label>
                <?php endfor; ?>
              </div>
            </fieldset>

            <label for="comentario">Tu reseña</label>
            <textarea id="comentario" name="comentario" class="wireframe-input" rows="4"
                      minlength="<?php echo RESENA_MINIMO; ?>" maxlength="<?php echo RESENA_MAXIMO; ?>"
                      placeholder="Contá cómo te atendieron, qué te hiciste, qué te pareció..." required><?php echo e($comentario); ?></textarea>

            <div class="resena-form-pie">
              <small class="text-subtle">Mínimo <?php echo RESENA_MINIMO; ?> caracteres</small>
              <button type="submit" class="btn-blue">Publicar</button>
            </div>

          </form>

        <?php endif; ?>

      </div>


      <!-- LISTA DE RESEÑAS -->

      <div class="reviews-grid" id="lista-resenas">

        <?php if (!$resenas): ?>
          <p class="text-subtle">Todavía no hay reseñas. ¡Sé el primero en dejar la tuya!</p>
        <?php endif; ?>

        <?php foreach ($resenas as $r): ?>
          <article class="review-card">

            <div class="review-head">
              <span class="review-avatar"><?php echo e(iniciales($r['cliente'])); ?></span>
              <div>
                <strong><?php echo e(nombre_corto($r['cliente'])); ?></strong>
                <small><?php echo e(date('d/m/Y', strtotime($r['created_at']))); ?></small>
              </div>
            </div>

            <?php echo estrellas_html($r['puntaje']); ?>

            <p><?php echo nl2br(e($r['comentario'])); ?></p>

          </article>
        <?php endforeach; ?>

      </div>

    </div>

  </section>

<?php include('includes/footer.php'); ?>