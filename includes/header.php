<?php
  require_once __DIR__ . '/../config/env.php';
  $titulo_pagina = $titulo_pagina ?? env('APP_NAME') . ' | Estilo & Precisión';
  $descripcion_pagina = $descripcion_pagina ?? 'Barbería en Salta Capital. Cortes, barba y perfilado con técnicas modernas. Reservá tu turno online en DKBZA Barber Studio.';
  $url_sitio = rtrim(env('APP_URL'), '/');
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title><?php echo htmlspecialchars($titulo_pagina); ?></title>

  <!-- SEO: texto que muestra Google debajo del título -->
  <meta name="description" content="<?php echo htmlspecialchars($descripcion_pagina); ?>" />

  <!-- BASE: solo la usa 404.php para que los links funcionen desde cualquier carpeta -->
  <?php if (!empty($base_href)): ?>
    <base href="<?php echo htmlspecialchars($base_href); ?>" />
  <?php endif; ?>

  <!-- FAVICON (icono de la pestaña) -->
  <link rel="icon" href="./img/favicon-32.png" sizes="32x32" type="image/png" />
  <link rel="apple-touch-icon" href="./img/apple-touch-icon.png" />
  <meta name="theme-color" content="#060B18" />

  <!-- OPEN GRAPH: vista previa al compartir el link por WhatsApp, Instagram, etc. -->
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="<?php echo htmlspecialchars(env('APP_NAME')); ?>" />
  <meta property="og:title" content="<?php echo htmlspecialchars($titulo_pagina); ?>" />
  <meta property="og:description" content="<?php echo htmlspecialchars($descripcion_pagina); ?>" />
  <meta property="og:locale" content="es_AR" />
  <?php if ($url_sitio !== ''): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($url_sitio . '/img/og-imagen.jpg'); ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
  <?php endif; ?>

  <!-- CSS PRINCIPAL -->
  <link rel="stylesheet" href="./css/styles.css?v=4" />

  <!-- FUENTES -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap"
    rel="stylesheet">
</head>

<body>