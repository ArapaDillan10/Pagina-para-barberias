<?php
  // Crea (o actualiza) un administrador. Solo se ejecuta desde la terminal:
  //   C:\xampp\php\php.exe database\crear_admin.php admin@dkbza.com
  // El nombre y la contraseña se piden después, así no quedan en el historial de la terminal.

  if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado.');
  }

  if ($argc !== 2) {
    exit("Uso: php crear_admin.php <email>\n");
  }

  $email = strtolower(trim($argv[1]));

  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
    exit("El email no es válido.\n");
  }

  echo 'Nombre para mostrar: ';
  $nombre = trim((string) fgets(STDIN));

  if (!preg_match('/^[\p{L} ]{2,100}$/u', $nombre)) {
    exit("El nombre debe tener entre 2 y 100 letras.\n");
  }

  echo 'Contraseña (mínimo 8 caracteres): ';
  $contrasena = trim((string) fgets(STDIN));

  echo 'Repetí la contraseña: ';
  $repetida = trim((string) fgets(STDIN));

  if (strlen($contrasena) < 8 || strlen($contrasena) > 72) {
    exit("\nLa contraseña debe tener entre 8 y 72 caracteres.\n");
  }

  if ($contrasena !== $repetida) {
    exit("\nLas contraseñas no coinciden.\n");
  }

  // Ojo: config/env.php usa una variable $clave; por eso acá se llama $contrasena
  require_once __DIR__ . '/../config/conexion.php';

  $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, 'admin')
          ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), password = VALUES(password), rol = 'admin', estado = 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':nombre'   => $nombre,
    ':email'    => $email,
    ':password' => password_hash($contrasena, PASSWORD_BCRYPT),
  ]);

  echo "\nAdministrador '$email' listo. Ya podés entrar al panel.\n";

  