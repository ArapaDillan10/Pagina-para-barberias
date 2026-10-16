<?php
  // Crea (o actualiza) un usuario del panel. Solo se ejecuta desde la terminal:
  //   C:\xampp\php\php.exe clase7\database\crear_admin.php admin
  // La contraseña se pide después, así no queda guardada en el historial de la terminal.

  if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado.');
  }

  if ($argc !== 2) {
    exit("Uso: php crear_admin.php <usuario>\n");
  }

  $usuario = trim($argv[1]);

  if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $usuario)) {
    exit("El usuario debe tener entre 3 y 50 caracteres (letras, números o _).\n");
  }

  echo 'Contraseña para ' . $usuario . ' (mínimo 8 caracteres): ';
  $contrasena = trim((string) fgets(STDIN));

  echo 'Repetí la contraseña: ';
  $repetida = trim((string) fgets(STDIN));

  if (strlen($contrasena) < 8) {
    exit("\nLa contraseña debe tener al menos 8 caracteres.\n");
  }

  if ($contrasena !== $repetida) {
    exit("\nLas contraseñas no coinciden.\n");
  }

  // Ojo: config/env.php usa una variable $clave; por eso acá se llama $contrasena
  require_once __DIR__ . '/../config/conexion.php';

  $sql = 'INSERT INTO usuarios (usuario, password_hash) VALUES (:usuario, :hash)
          ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), estado = 1';
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':usuario' => $usuario,
    ':hash'    => password_hash($contrasena, PASSWORD_DEFAULT),
  ]);

  echo "\nUsuario '$usuario' listo. Ya podés entrar al panel.\n";
