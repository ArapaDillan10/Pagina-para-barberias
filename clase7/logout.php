<?php
  require_once __DIR__ . '/includes/sesion.php';

  // Solo se cierra sesión por POST y con token CSRF
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
  }

  csrf_verificar();
  cerrar_sesion();

  header('Location: login.php?msg=salida');
  exit;
