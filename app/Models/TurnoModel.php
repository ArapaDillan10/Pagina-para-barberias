<?php
  // Modelo: todas las consultas de la tabla turnos del módulo de reservas
  // (parte de la refactorización MVC del TP10).
  class TurnoModel {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
      $this->pdo = $pdo;
    }

    // Horas ocupadas de un barbero en una fecha (los cancelados no ocupan).
    public function horasOcupadas(int $barberoId, string $fecha): array {
      $stmt = $this->pdo->prepare(
        "SELECT TIME_FORMAT(hora, '%H:%i')
         FROM turnos
         WHERE barbero_id = :barbero AND fecha = :fecha AND estado <> 'cancelado'"
      );
      $stmt->execute([':barbero' => $barberoId, ':fecha' => $fecha]);
      return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Turnos futuros pendientes o confirmados que ya tiene el usuario.
    public function activosDeUsuario(int $usuarioId): int {
      $stmt = $this->pdo->prepare(
        "SELECT COUNT(*) FROM turnos
         WHERE usuario_id = :usuario
           AND estado IN ('pendiente', 'confirmado')
           AND TIMESTAMP(fecha, hora) > NOW()"
      );
      $stmt->execute([':usuario' => $usuarioId]);
      return (int) $stmt->fetchColumn();
    }

    // Crea el turno, sus servicios y registra la seña en una sola transacción.
    // $subirComprobante recibe el id del turno y devuelve ['archivo' => nombre] o ['error' => msg].
    public function crear(int $usuarioId, array $datos, array $preciosElegidos,
                          float $senaMonto, callable $subirComprobante): int {
      $this->pdo->beginTransaction();

      try {
        $stmt = $this->pdo->prepare(
          'INSERT INTO turnos (usuario_id, barbero_id, fecha, hora, telefono, observaciones)
           VALUES (:usuario, :barbero, :fecha, :hora, :telefono, :observaciones)'
        );
        $stmt->execute([
          ':usuario'       => $usuarioId,
          ':barbero'       => (int) $datos['barbero'],
          ':fecha'         => $datos['fecha'],
          ':hora'          => $datos['hora'],
          ':telefono'      => $datos['telefono'],
          ':observaciones' => $datos['observaciones'] !== '' ? $datos['observaciones'] : null,
        ]);
        $turnoId = (int) $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare(
          'INSERT INTO turno_servicio (turno_id, servicio_id, precio)
           VALUES (:turno, :servicio, :precio)'
        );
        foreach ($preciosElegidos as $servicioId => $precio) {
          $stmt->execute([':turno' => $turnoId, ':servicio' => $servicioId, ':precio' => $precio]);
        }

        $subida = $subirComprobante($turnoId);
        if (isset($subida['error'])) {
          throw new RuntimeException($subida['error']);
        }

        $stmt = $this->pdo->prepare(
          'UPDATE turnos SET sena_monto = :monto, sena_comprobante = :archivo WHERE id = :id'
        );
        $stmt->execute([':monto' => $senaMonto, ':archivo' => $subida['archivo'], ':id' => $turnoId]);

        $this->pdo->commit();
        return $turnoId;

      } catch (Throwable $e) {
        $this->pdo->rollBack();
        throw $e;
      }
    }
  }
