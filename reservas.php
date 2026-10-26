<?php
  // Front controller del módulo de reservas (TP10 - MVC nativo).
  // No tiene consultas ni HTML: solo conecta la solicitud con el controlador.
  require_once __DIR__ . '/includes/sesion.php';
  require_once __DIR__ . '/config/conexion.php';
  require_once __DIR__ . '/includes/funciones.php';
  require_once __DIR__ . '/includes/turnos.php';

  require_once __DIR__ . '/app/Models/BarberoModel.php';
  require_once __DIR__ . '/app/Models/ServicioModel.php';
  require_once __DIR__ . '/app/Models/TurnoModel.php';
  require_once __DIR__ . '/app/Controllers/ReservaController.php';

  $controlador = new ReservaController($pdo);

  if (isset($_SESSION['usuario_id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->crear();
  } else {
    $controlador->mostrar();
  }
