<!DOCTYPE html>
<!-- Vista del módulo de servicios en CodeIgniter (TP10 - Parte B). -->
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Servicios - DKBZA (CodeIgniter)</title>
  <style>
    body { font-family: Arial, sans-serif; background:#111; color:#eee; margin:2rem auto; max-width:800px; }
    h1 { color:#e8b84b; }
    table { width:100%; border-collapse:collapse; }
    td, th { border:1px solid #444; padding:.6rem; text-align:left; }
    th { background:#1c1c1c; }
    a.btn { display:inline-block; background:#e8b84b; color:#111; padding:.5rem 1rem;
            border-radius:6px; text-decoration:none; font-weight:bold; margin-bottom:1rem; }
    a.del { color:#ff7b7b; }
  </style>
</head>
<body>
  <h1>Servicios de la barbería (módulo CodeIgniter)</h1>
  <a class="btn" href="<?= base_url('servicios/nuevo') ?>">+ Nuevo servicio</a>
  <table>
    <tr><th>Nombre</th><th>Descripción</th><th>Precio</th><th></th></tr>
    <?php foreach ($servicios as $s): ?>
      <tr>
        <td><?= esc($s['nombre']) ?></td>
        <td><?= esc($s['descripcion']) ?></td>
        <td>$<?= number_format((float) $s['precio'], 0, ',', '.') ?></td>
        <td><a class="del" href="<?= base_url('servicios/eliminar/' . $s['id']) ?>">Eliminar</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
