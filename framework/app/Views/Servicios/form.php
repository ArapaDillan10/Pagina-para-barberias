<!DOCTYPE html>
<!-- Formulario de alta de servicio (TP10 - Parte B). -->
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo servicio - DKBZA</title>
  <style>
    body { font-family: Arial, sans-serif; background:#111; color:#eee; margin:2rem auto; max-width:480px; }
    h1 { color:#e8b84b; }
    label { display:block; margin-top:1rem; }
    input, textarea { width:100%; padding:.5rem; background:#1c1c1c; border:1px solid #444;
                      color:#eee; border-radius:6px; }
    button { margin-top:1.5rem; background:#e8b84b; color:#111; border:0; padding:.6rem 1.5rem;
             border-radius:6px; font-weight:bold; cursor:pointer; }
    a { color:#e8b84b; }
  </style>
</head>
<body>
  <h1>Nuevo servicio</h1>
  <form method="post" action="<?= base_url('servicios') ?>">
    <?= csrf_field() ?>
    <label>Nombre *<input type="text" name="nombre" required maxlength="80"></label>
    <label>Descripción<textarea name="descripcion" rows="3" maxlength="255"></textarea></label>
    <label>Precio *<input type="number" name="precio" required min="0" step="0.01"></label>
    <button type="submit">Guardar</button>
  </form>
  <p><a href="<?= base_url('servicios') ?>">← Volver al listado</a></p>
</body>
</html>
