<?php
  // Modelo: consultas de la tabla servicios (parte de la refactorización MVC del TP10).
  class ServicioModel {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
      $this->pdo = $pdo;
    }

    // Servicios activos con su precio actual para el paso 3 de la reserva.
    public function activos(): array {
      return $this->pdo
        ->query('SELECT id, nombre, precio FROM servicios WHERE estado = 1 ORDER BY precio DESC')
        ->fetchAll();
    }
  }
