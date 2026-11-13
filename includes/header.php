<?php
  require_once __DIR__ . '/../config/env.php';
  $titulo_pagina = $titulo_pagina ?? env('APP_NAME') . ' | Estilo & Precisión';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title><?php echo htmlspecialchars($titulo_pagina); ?></title>

  <!-- CSS PRINCIPAL -->
  <!-- BASE: solo la usa 404.php para que los links funcionen desde cualquier carpeta -->
  <?php if (!empty($base_href)): ?>
    <base href="<?php echo htmlspecialchars($base_href); ?>" />
  <?php endif; ?>

  <!-- CSS PRINCIPAL -->
  <link rel="stylesheet" href="./css/styles.css?v=3" />

  <!-- FUENTES -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap"
    rel="stylesheet">
</head>

<body>
