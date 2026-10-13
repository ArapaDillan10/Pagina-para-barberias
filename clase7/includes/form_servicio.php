<?php
  // Formulario compartido por crear_servicio.php y editar_servicio.php.
  // Usa: $servicio, $errores, $accion_form, $texto_boton
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

        <div class="form-grupo">
          <label for="nombre">Nombre del servicio *</label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            class="wireframe-input"
            maxlength="100"
            placeholder="Ej: Tintura"
            value="<?php echo e($servicio['nombre']); ?>"
          >
        </div>

        <div class="form-grupo">
          <label for="descripcion">Descripción</label>
          <textarea
            id="descripcion"
            name="descripcion"
            class="wireframe-input"
            rows="4"
            placeholder="Ej: Coloración completa con productos profesionales."
          ><?php echo e($servicio['descripcion']); ?></textarea>
        </div>

        <div class="form-grupo">
          <label for="precio">Precio (en pesos) *</label>
          <input
            type="text"
            id="precio"
            name="precio"
            class="wireframe-input"
            inputmode="decimal"
            placeholder="Ej: 12000"
            value="<?php echo e($servicio['precio']); ?>"
          >
        </div>

        <div class="form-grupo">
          <label for="imagen">Imagen (JPG, PNG o WEBP, máximo 2 MB)</label>

          <?php if (!empty($servicio['imagen'])): ?>
            <div class="imagen-actual">
              <img src="img/<?php echo e($servicio['imagen']); ?>" alt="Imagen actual de <?php echo e($servicio['nombre']); ?>">
              <small class="text-subtle">Imagen actual. Si no elegís otra, se mantiene.</small>
            </div>
          <?php endif; ?>

          <input
            type="file"
            id="imagen"
            name="imagen"
            class="wireframe-input"
            accept="image/jpeg,image/png,image/webp"
          >
        </div>

        <label class="form-check">
          <input type="checkbox" name="estado" value="1" <?php echo $servicio['estado'] ? 'checked' : ''; ?>>
          Visible en la página de servicios
        </label>

        <div class="admin-acciones">
          <button type="submit" class="btn-blue">
            <?php echo e($texto_boton); ?>
          </button>

          <a href="admin_servicios.php" class="btn-ghost-sm">
            Cancelar
          </a>
        </div>

      </form>
