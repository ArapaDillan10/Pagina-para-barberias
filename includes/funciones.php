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

  function validar_barbero($nombre, $especialidad) {
    $errores = [];

    if ($nombre === '') {
      $errores[] = 'El nombre del barbero es obligatorio.';
    } elseif (!preg_match('/^.{2,100}$/us', $nombre)) {
      $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
    }

    if (!preg_match('/^.{0,100}$/us', $especialidad)) {
      $errores[] = 'La especialidad no puede superar los 100 caracteres.';
    }

    return $errores;
  }

  // Iniciales para mostrar cuando el barbero no tiene foto: "Dillan Arapa" -> "DA"
  function iniciales($nombre) {
    preg_match_all('/(?<=^|\s)\p{L}/u', (string) $nombre, $letras);
    return strtoupper(implode('', array_slice($letras[0], 0, 2)));
  }

  // Devuelve null si no se eligió imagen, ['ruta' => ...] si se guardó o ['error' => ...] si falló.
  // $carpeta: subcarpeta de img/ donde se guarda ('servicios' o 'barberos').
  function subir_imagen($archivo, $carpeta = 'servicios') {
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

    $destino = __DIR__ . '/../img/' . $carpeta . '/';

    if (!is_dir($destino)) {
      mkdir($destino, 0755, true);
    }

    $nombre_archivo = bin2hex(random_bytes(8)) . '.' . $tipos_permitidos[$info[2]];

    if (!move_uploaded_file($archivo['tmp_name'], $destino . $nombre_archivo)) {
      return ['error' => 'No se pudo guardar la imagen en el servidor.'];
    }

    return ['ruta' => $carpeta . '/' . $nombre_archivo];
  }

  // Guarda el comprobante de seña en var/comprobantes/ (fuera del alcance público).
  // Solo acepta PDF o imagen real (JPG, PNG, WEBP) leyendo la firma del archivo:
  // un .php o .html renombrado queda rechazado.
  function guardar_comprobante($archivo, $turno_id) {
    if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
      return null;
    }

    if ($archivo['size'] > 4 * 1024 * 1024) {
      return ['error' => 'El comprobante no puede pesar más de 4 MB.'];
    }

    $tipos_permitidos = [
      'image/jpeg'      => 'jpg',
      'image/png'       => 'png',
      'image/webp'      => 'webp',
      'application/pdf' => 'pdf',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    if (!isset($tipos_permitidos[$mime])) {
      return ['error' => 'El comprobante tiene que ser una imagen o un PDF.'];
    }

    $destino = __DIR__ . '/../var/comprobantes/';

    if (!is_dir($destino)) {
      mkdir($destino, 0755, true);
    }

    $nombre_archivo = 'turno-' . $turno_id . '-' . bin2hex(random_bytes(6)) . '.' . $tipos_permitidos[$mime];

    if (!move_uploaded_file($archivo['tmp_name'], $destino . $nombre_archivo)) {
      return ['error' => 'No se pudo guardar el comprobante en el servidor.'];
    }

    return ['archivo' => $nombre_archivo];
  }

  // Solo borra imágenes subidas desde el panel (img/servicios/ o img/barberos/), nunca las originales del sitio.
  function borrar_imagen($ruta) {
    $carpeta = explode('/', (string) $ruta)[0];

    if (in_array($carpeta, ['servicios', 'barberos'], true)) {
      $archivo = __DIR__ . '/../img/' . $carpeta . '/' . basename($ruta);

      if (is_file($archivo)) {
        unlink($archivo);
      }
    }
  }
?>
