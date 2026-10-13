<?php
  if (!function_exists('e')) {
    function e($valor) {
      return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
  }

  function precio_formateado($precio) {
    return '$' . number_format((float) $precio, 0, ',', '.');
  }

  // "12.000", "$12000" o "12000,50" -> "12000" / "12000.50"
  function normalizar_precio($precio) {
    $precio = str_replace(['$', '.', ' '], '', $precio);
    return str_replace(',', '.', $precio);
  }

  function validar_servicio($nombre, $precio) {
    $errores = [];

    if (empty($nombre)) {
      $errores[] = 'El nombre del servicio es obligatorio.';
    } elseif (strlen($nombre) > 100) {
      $errores[] = 'El nombre no puede superar los 100 caracteres.';
    }

    if ($precio === '') {
      $errores[] = 'El precio es obligatorio.';
    } elseif (filter_var($precio, FILTER_VALIDATE_FLOAT) === false || (float) $precio < 0) {
      $errores[] = 'El precio debe ser un número positivo (ej: 12000).';
    }

    return $errores;
  }

  // Devuelve null si no se eligió imagen, ['ruta' => ...] si se guardó o ['error' => ...] si falló.
  function subir_imagen($archivo) {
    if (!isset($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
      return null;
    }

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
      return ['error' => 'No se pudo subir la imagen (máximo 2 MB).'];
    }

    if ($archivo['size'] > 2 * 1024 * 1024) {
      return ['error' => 'La imagen no puede pesar más de 2 MB.'];
    }

    $tipos_permitidos = [
      IMAGETYPE_JPEG => 'jpg',
      IMAGETYPE_PNG  => 'png',
      IMAGETYPE_WEBP => 'webp',
    ];

    $info = @getimagesize($archivo['tmp_name']);

    if ($info === false || !isset($tipos_permitidos[$info[2]])) {
      return ['error' => 'El archivo debe ser una imagen JPG, PNG o WEBP.'];
    }

    $carpeta = __DIR__ . '/../img/servicios/';

    if (!is_dir($carpeta)) {
      mkdir($carpeta, 0755, true);
    }

    $nombre_archivo = uniqid('servicio_') . '.' . $tipos_permitidos[$info[2]];

    if (!move_uploaded_file($archivo['tmp_name'], $carpeta . $nombre_archivo)) {
      return ['error' => 'No se pudo guardar la imagen en el servidor.'];
    }

    return ['ruta' => 'servicios/' . $nombre_archivo];
  }

  // Solo borra imágenes subidas desde el panel (img/servicios/), nunca las originales del sitio.
  function borrar_imagen($ruta) {
    if (!empty($ruta) && strpos($ruta, 'servicios/') === 0) {
      $archivo = __DIR__ . '/../img/servicios/' . basename($ruta);

      if (is_file($archivo)) {
        unlink($archivo);
      }
    }
  }
?>
