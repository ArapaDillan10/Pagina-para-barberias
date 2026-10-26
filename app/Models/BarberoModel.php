<?php
  // Modelo: consultas de la tabla barberos (parte de la refactorización MVC del TP10).
  class BarberoModel {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
      $this->pdo = $pdo;
    }

    // Barberos visibles para elegir en el sitio y en las reservas.
    public function activos(): array {
      return $this->pdo
        ->query('SELECT id, nombre, especialidad, foto FROM barberos WHERE estado = 1 ORDER BY id')
        ->fetchAll();
    }

    public function existeActivo(int $id): bool {
      $stmt = $this->pdo->prepare('SELECT 1 FROM barberos WHERE id = :id AND estado = 1');
      $stmt->execute([':id' => $id]);
      return (bool) $stmt->fetchColumn();
    }
  }
