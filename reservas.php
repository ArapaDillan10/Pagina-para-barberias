<?php
  $titulo_pagina = 'DKBZA Barber Studio | Reservas';
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';
  require_once __DIR__ . '/includes/turnos.php';

  $logueado = isset($_SESSION['usuario_id']);

  $mp_alias        = env('MP_ALIAS');
  $sena_porcentaje = (int) env('SENA_PORCENTAJE', '50');

  $barberos  = $pdo->query('SELECT id, nombre, especialidad, foto FROM barberos WHERE estado = 1 ORDER BY id')->fetchAll();
  $servicios = $pdo->query('SELECT id, nombre, precio FROM servicios WHERE estado = 1 ORDER BY precio DESC')->fetchAll();

  $errores = [];
  $datos   = ['barbero' => '', 'fecha' => '', 'hora' => '', 'telefono' => '', 'servicios' => [], 'observaciones' => ''];

  if ($logueado && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $datos['barbero']       = (string) ($_POST['barbero'] ?? '');
    $datos['fecha']         = (string) ($_POST['fecha'] ?? '');
    $datos['hora']          = (string) ($_POST['hora'] ?? '');
    $datos['telefono']      = trim((string) ($_POST['telefono'] ?? ''));
    $datos['servicios']     = array_map('intval', (array) ($_POST['servicios'] ?? []));
    $datos['observaciones'] = trim((string) ($_POST['observaciones'] ?? ''));

    $ids_barberos  = array_column($barberos, 'id');
    $precios       = array_column($servicios, 'precio', 'id');
    $elegidos      = array_values(array_unique($datos['servicios']));

    if (!in_array((int) $datos['barbero'], array_map('intval', $ids_barberos), true)) {
      $errores[] = 'Elegí un barbero.';
    }

    if (!fecha_turno_valida($datos['fecha'])) {
      $errores[] = 'Elegí una fecha válida: desde hoy hasta ' . DIAS_ANTICIPACION . ' días, de lunes a sábado.';
    } elseif (!in_array($datos['hora'], HORARIOS_TURNOS, true) || hora_ya_paso($datos['fecha'], $datos['hora'])) {
      $errores[] = 'Elegí un horario disponible.';
    }

    if (!$elegidos || array_diff($elegidos, array_keys($precios))) {
      $errores[] = 'Elegí al menos un servicio.';
    }

    if (!preg_match('/^\+?[0-9 ()-]{8,20}$/', $datos['telefono'])) {
      $errores[] = 'Ingresá un WhatsApp válido, por ejemplo +54 9 11 1234-5678.';
    }

    if (!preg_match('/^.{0,255}$/us', $datos['observaciones'])) {
      $errores[] = 'Las observaciones no pueden superar los 255 caracteres.';
    }

    if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] === UPLOAD_ERR_NO_FILE) {
      $errores[] = 'Subí el comprobante de la seña.';
    } elseif ($_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
      $errores[] = 'No se pudo subir el comprobante (máximo 4 MB).';
    }

    if (!$errores) {
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM turnos
                             WHERE usuario_id = :usuario AND estado IN ('pendiente', 'confirmado')
                               AND TIMESTAMP(fecha, hora) > NOW()");
      $stmt->execute([':usuario' => $_SESSION['usuario_id']]);

      if ((int) $stmt->fetchColumn() >= MAX_TURNOS_ACTIVOS) {
        $errores[] = 'Ya tenés ' . MAX_TURNOS_ACTIVOS . ' turnos activos. Cancelá alguno desde Mi cuenta para reservar otro.';
      }
    }

    if (!$errores) {
      try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO turnos (usuario_id, barbero_id, fecha, hora, telefono, observaciones)
                               VALUES (:usuario, :barbero, :fecha, :hora, :telefono, :observaciones)');
        $stmt->execute([
          ':usuario'       => $_SESSION['usuario_id'],
          ':barbero'       => (int) $datos['barbero'],
          ':fecha'         => $datos['fecha'],
          ':hora'          => $datos['hora'],
          ':telefono'      => $datos['telefono'],
          ':observaciones' => $datos['observaciones'] !== '' ? $datos['observaciones'] : null,
        ]);
        $turno_id = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO turno_servicio (turno_id, servicio_id, precio) VALUES (:turno, :servicio, :precio)');
        foreach ($elegidos as $servicio_id) {
          $stmt->execute([':turno' => $turno_id, ':servicio' => $servicio_id, ':precio' => $precios[$servicio_id]]);
        }

        // La seña se calcula en el servidor sobre los precios reales de la base,
        // nunca con lo que mande el navegador.
        $total      = array_sum(array_map(fn($id) => (float) $precios[$id], $elegidos));
        $sena_monto = $total * $sena_porcentaje / 100;

        $subida = guardar_comprobante($_FILES['comprobante'], $turno_id);
        if (isset($subida['error'])) {
          throw new RuntimeException($subida['error']);
        }

        $stmt = $pdo->prepare('UPDATE turnos SET sena_monto = :monto, sena_comprobante = :archivo WHERE id = :id');
        $stmt->execute([':monto' => $sena_monto, ':archivo' => $subida['archivo'], ':id' => $turno_id]);

        $pdo->commit();
        header('Location: mi_cuenta.php?msg=reservado');
        exit;
      } catch (Throwable $ex) {
        $pdo->rollBack();

        if (!empty($subida['archivo'])) {
          $archivo = __DIR__ . '/var/comprobantes/' . basename($subida['archivo']);
          if (is_file($archivo)) {
            unlink($archivo);
          }
        }

        if ($ex instanceof PDOException && $ex->getCode() === '23000') {
          $errores[] = 'Ese horario se acaba de ocupar. Elegí otro.';
        } elseif ($ex instanceof RuntimeException) {
          $errores[] = $ex->getMessage();
        } else {
          throw $ex;
        }
      }
    }
  }

  $paso_inicial = $errores ? 3 : 1;
  $hoy          = date('Y-m-d');
  $limite = date('Y-m-d', strtotime('+' . DIAS_ANTICIPACION . ' days'));

  include('includes/header.php');
  include('includes/nav.php');
?>

  <section class="section-dark">
    <div class="container text-center">

      <h1 class="stepper-title">Reservá tu turno</h1>
      <p class="stepper-sub">3 pasos · Elegí barbero, día y horario</p>

      <?php if ($logueado && $barberos && $servicios): ?>
        <!-- INDICADOR DE PASOS -->
        <div class="stepper-header">
          <div class="step-indicator active" id="step-dot-1">1</div>
          <div class="step-line"></div>
          <div class="step-indicator" id="step-dot-2">2</div>
          <div class="step-line"></div>
          <div class="step-indicator" id="step-dot-3">3</div>

          <div class="step-labels">
            <span class="active" id="step-lbl-1">BARBERO</span>
            <span id="step-lbl-2">FECHA & HORA</span>
            <span id="step-lbl-3">TUS DATOS</span>
          </div>
        </div>
      <?php endif; ?>

      <div class="booking-card">

        <?php if (!$logueado): ?>

          <div class="reserva-login">
            <p>Para reservar un turno tenés que iniciar sesión. Así podés ver y cancelar tus turnos desde <strong>Mi cuenta</strong>.</p>
            <div class="step-actions flex-center">
              <a href="login.php" class="btn-blue">Ir a Mi cuenta</a>
            </div>
          </div>

        <?php elseif (!$barberos || !$servicios): ?>

          <p>Por ahora no hay turnos disponibles. Escribinos desde <a href="contacto.php">Contacto</a>.</p>

        <?php else: ?>

          <?php if ($errores): ?>
            <div class="alerta alerta-error">
              <ul>
                <?php foreach ($errores as $error): ?>
                  <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" action="reservas.php" id="form-reserva" class="form-reserva" novalidate
            enctype="multipart/form-data"
            data-paso="<?php echo $paso_inicial; ?>" data-hoy="<?php echo $hoy; ?>" data-limite="<?php echo $limite; ?>"
            data-cerrados="<?php echo e(implode(',', DIAS_CERRADO)); ?>"
            data-sena="<?php echo $sena_porcentaje; ?>">
            <?php echo csrf_campo(); ?>
            <input type="hidden" name="fecha" id="fecha" value="<?php echo e($datos['fecha']); ?>">

            <!-- =====================================================
                 PASO 1: BARBERO
            ====================================================== -->
            <div class="step-content" id="step-1">
              <span class="step-hint">SELECCIONÁ TU BARBERO</span>

              <div class="barbers-grid">
                <?php foreach ($barberos as $barbero): ?>
                  <label class="barber-card">
                    <input type="radio" name="barbero" value="<?php echo e($barbero['id']); ?>"
                      data-nombre="<?php echo e($barbero['nombre']); ?>"
                      <?php echo $datos['barbero'] === (string) $barbero['id'] ? 'checked' : ''; ?>>
                    <?php if (!empty($barbero['foto'])): ?>
                      <img src="img/<?php echo e($barbero['foto']); ?>" alt="" class="barber-avatar barber-foto">
                    <?php else: ?>
                      <div class="barber-avatar"><?php echo e(iniciales($barbero['nombre'])); ?></div>
                    <?php endif; ?>
                    <h3><?php echo e($barbero['nombre']); ?></h3>
                    <p><?php echo e($barbero['especialidad']); ?></p>
                  </label>
                <?php endforeach; ?>
              </div>

              <p class="paso-aviso" id="aviso-1"></p>

              <div class="step-actions flex-end">
                <button type="button" class="btn-blue" data-ir="2">CONTINUAR →</button>
              </div>
            </div>

            <!-- =====================================================
                 PASO 2: FECHA Y HORA
            ====================================================== -->
            <div class="step-content" id="step-2">
              <div class="calendar-hours-grid">

                <!-- CALENDARIO -->
                <div class="calendar-box">
                  <div class="calendar-nav">
                    <button type="button" class="calendar-flecha" id="mes-anterior" aria-label="Mes anterior">◄</button>
                    <span class="month-title" id="month-title"></span>
                    <button type="button" class="calendar-flecha" id="mes-siguiente" aria-label="Mes siguiente">►</button>
                  </div>
                  <div class="calendar-days" id="calendar-days"></div>
                </div>

                <!-- HORARIOS -->
                <div class="hours-box">
                  <span class="hours-title" id="horarios-estado">ELEGÍ UN DÍA</span>
                  <div class="hours-grid">
                    <?php foreach (HORARIOS_TURNOS as $hora): ?>
                      <label class="time-slot">
                        <input type="radio" name="hora" value="<?php echo $hora; ?>"
                          <?php echo $datos['hora'] === $hora ? 'checked' : ''; ?>>
                        <?php echo $hora; ?>h
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>

              </div>

              <p class="paso-aviso" id="aviso-2"></p>

              <div class="step-actions flex-between">
                <button type="button" class="btn-ghost-sm" data-ir="1">← VOLVER</button>
                <button type="button" class="btn-blue" data-ir="3">CONTINUAR →</button>
              </div>
            </div>

            <!-- =====================================================
                 PASO 3: TUS DATOS
            ====================================================== -->
            <div class="step-content" id="step-3">
              <div class="form-row">
                <div class="form-group">
                  <label for="nombre">NOMBRE</label>
                  <input type="text" id="nombre" class="wireframe-input" value="<?php echo e($_SESSION['usuario_nombre']); ?>" readonly>
                </div>
                <div class="form-group">
                  <label for="telefono">WHATSAPP *</label>
                  <input type="tel" id="telefono" name="telefono" class="wireframe-input" maxlength="20"
                    placeholder="+54 9 11 0000-0000" value="<?php echo e($datos['telefono']); ?>">
                </div>
              </div>

              <div class="form-group margin-top">
                <label>SERVICIOS *</label>
                <div class="servicios-lista">
                  <?php foreach ($servicios as $servicio): ?>
                    <label class="servicio-opcion">
                      <input type="checkbox" name="servicios[]" value="<?php echo e($servicio['id']); ?>"
                        data-precio="<?php echo e($servicio['precio']); ?>"
                        <?php echo in_array((int) $servicio['id'], $datos['servicios'], true) ? 'checked' : ''; ?>>
                      <span><?php echo e($servicio['nombre']); ?></span>
                      <strong><?php echo e(precio_formateado($servicio['precio'])); ?></strong>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- SEÑA -->
              <div class="sena-box">
                <span class="step-hint">SEÑA OBLIGATORIA (<?php echo $sena_porcentaje; ?>%)</span>
                <p>
                  Para guardar tu turno transferí <strong id="sena-monto">—</strong>
                  al alias <strong><?php echo e($mp_alias); ?></strong> y subí el comprobante.
                  Tu turno queda confirmado cuando la barbería verifica el pago.
                </p>
                <div class="form-group">
                  <label for="comprobante">COMPROBANTE DE LA SEÑA *</label>
                  <input type="file" id="comprobante" name="comprobante" class="wireframe-input"
                    accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                  <small class="sena-ayuda">Imagen (JPG, PNG o WEBP) o PDF, hasta 4 MB.</small>
                </div>
              </div>

              <div class="form-group margin-top">
                <label for="observaciones">OBSERVACIONES (OPCIONAL)</label>
                <textarea id="observaciones" name="observaciones" class="wireframe-input" maxlength="255" rows="2"
                  placeholder="Ej: es mi primera vez"><?php echo e($datos['observaciones']); ?></textarea>
              </div>

              <!-- RESUMEN -->
              <div class="summary-badge">
                <span id="summary-barber">✂ —</span>
                <span id="summary-date">📅 —</span>
                <span id="summary-time">⏰ —</span>
              </div>

              <div class="step-actions flex-between margin-top">
                <button type="button" class="btn-ghost-sm" data-ir="2">← VOLVER</button>
                <button type="submit" class="btn-red">CONFIRMAR RESERVA ✓</button>
              </div>
            </div>

          </form>

        <?php endif; ?>

      </div>
    </div>
  </section>

<?php include('includes/footer.php'); ?>
