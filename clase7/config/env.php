<?php
  $ruta_env = __DIR__ . '/../.env';

  if (file_exists($ruta_env)) {
    $lineas = file($ruta_env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
      $linea = trim($linea);

      if ($linea === '' || $linea[0] === '#' || strpos($linea, '=') === false) {
        continue;
      }

      list($clave, $valor) = explode('=', $linea, 2);
      $_ENV[trim($clave)] = trim(trim($valor), '"\'');
    }
  }

  function env($clave, $por_defecto = '') {
    return $_ENV[$clave] ?? $por_defecto;
  }
?>