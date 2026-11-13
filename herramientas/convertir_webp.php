<?php
  // Convierte a WebP las imágenes PNG/JPG de img/ (no toca las originales).
  // Uso desde la terminal, en la carpeta del proyecto:
  //   C:\xampp\php\php.exe herramientas\convertir_webp.php corteI.png producto1.png
  // Sin nombres, convierte todas las de img/ que todavía no tengan su .webp.

  if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
  }

  if (!function_exists('imagewebp')) {
    exit("Falta la extensión GD: en C:\\xampp\\php\\php.ini sacale el ; a la línea ;extension=gd\n");
  }

  $carpeta = __DIR__ . '/../img/';
  $calidad = 80;
  $ancho_maximo = 1600;

  // Estas tienen que seguir en PNG/JPG (favicon, logo y la imagen de WhatsApp)
  $no_convertir = ['favicon-32.png', 'apple-touch-icon.png', 'logo.png', 'og-imagen.jpg'];

  $archivos = array_slice($argv, 1);
  if (empty($archivos)) {
    $archivos = array_map('basename', glob($carpeta . '*.{png,jpg,jpeg}', GLOB_BRACE));
  }

  foreach ($archivos as $archivo) {
    $origen = $carpeta . $archivo;
    $destino = $carpeta . pathinfo($archivo, PATHINFO_FILENAME) . '.webp';

    if (in_array($archivo, $no_convertir) || !is_file($origen)) {
      echo "Salteo: $archivo\n";
      continue;
    }

    if (empty(array_slice($argv, 1)) && is_file($destino)) {
      continue;
    }

    $imagen = @imagecreatefromstring(file_get_contents($origen));
    if ($imagen === false) {
      echo "No es una imagen válida: $archivo\n";
      continue;
    }

    // Las fotos muy anchas se achican: en la página nunca se ven más grandes
    if (imagesx($imagen) > $ancho_maximo) {
      $imagen = imagescale($imagen, $ancho_maximo);
    }

    imagepalettetotruecolor($imagen);
    imagealphablending($imagen, false);
    imagesavealpha($imagen, true);
    imagewebp($imagen, $destino, $calidad);
    imagedestroy($imagen);

    printf("%s: %d KB -> %d KB\n", $archivo, filesize($origen) / 1024, filesize($destino) / 1024);
  }