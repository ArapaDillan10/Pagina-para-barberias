<?php
  // Formulario compartido por crear_barbero.php y editar_barbero.php.
  // Usa: $barbero, $errores, $accion_form, $texto_boton
?>

      <?php if (!empty($errores)): ?>
        <div class="alerta alerta-error">
          <strong>Revisá estos datos:</strong>
          <ul>
            <?php foreach ($errores as $error): ?>
              <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="<?php echo e($accion_form); ?>" enctype="multipart/form-data" class="form-servicio" novalidate>

        <?php echo csrf_campo(); ?>

        <div class="form-grupo">
          <label for="nombre">Nombre *</label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            class="wireframe-input"
            maxlength="100"
            placeholder="Ej: Mateo Nieva"
            value="<?php echo e($barbero['nombre']); ?>"
          >
        </div>

        <div class="form-grupo">
          <label for="especialidad">Especialidad</label>
          <input
            type="text"
            id="especialidad"
            name="especialidad"
            class="wireframe-input"
            maxlength="100"
            placeholder="Ej: Fade Specialist & Senior"
            value="<?php echo e($barbero['especialidad']); ?>"
          >
        </div>

        <div class="form-grupo">
          <label for="foto">Foto (JPG, PNG o WEBP, máximo 2 MB)</label>

          <?php if (!empty($barbero['foto'])): ?>
            <div class="imagen-actual">
              <img src="img/<?php echo e($barbero['foto']); ?>" alt="Foto actual de <?php echo e($barbero['nombre']); ?>">
              <small class="text-subtle">Foto actual. Si no elegís otra, se mantiene.</small>
            </div>
          <?php endif; ?>

          <input
            type="file"
            id="foto"
            name="foto"
            class="wireframe-input"
            accept="image/jpeg,image/png,image/webp"
          >
        </div>

        <label class="form-check">
          <input type="checkbox" name="estado" value="1" <?php echo $barbero['estado'] ? 'checked' : ''; ?>>
          Visible en Staff y disponible para reservas
        </label>

        <div class="admin-acciones">
          <button type="submit" class="btn-blue">
            <?php echo e($texto_boton); ?>
          </button>

          <a href="admin_barberos.php" class="btn-ghost-sm">
            Cancelar
          </a>
        </div>

      </form>
