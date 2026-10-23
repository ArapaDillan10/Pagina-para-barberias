<?php
  require_once __DIR__ . '/env.php';

  $host    = env('DB_HOST', '127.0.0.1');
  $db      = env('DB_NAME', 'dkbza_db');
  $user    = env('DB_USER', 'root');
  $pass    = env('DB_PASS', '');
  $charset = env('DB_CHARSET', 'utf8mb4');

  $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  try {
    $pdo = new PDO($dsn, $user, $pass, $options);
  } catch (PDOException $e) {
    error_log($e->getMessage());
    die('Error crítico: No se pudo establecer la conexión con la base de datos.');
  }
?>
