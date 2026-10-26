<?php
  // Controlador del módulo de reservas (refactorización MVC del TP10).
  // Recibe la solicitud, valida, habla con los modelos y decide qué vista mostrar.
  class ReservaController {

    private BarberoModel  $barberos;
    private ServicioModel $servicios;
    private TurnoModel    $turnos;
    private string $mpAlias;
    private int    $senaPorcentaje;

    public function __construct(PDO $pdo) {
      $this->barberos       = new BarberoModel($pdo);
      $this->servicios      = new ServicioModel($pdo);
      $this->turnos         = new TurnoModel($pdo);
      $this->mpAlias        = (string) env('MP_ALIAS');
      $this->senaPorcentaje = (int) env('SENA_PORCENTAJE', '50');
    }

    // GET: prepara los datos y renderiza la vista del wizard de 3 pasos.
    public function mostrar(array $errores = [], ?array $datos = null): void {
      $titulo_pagina   = 'DKBZA Barber Studio | Reservas';
      $logueado        = isset($_SESSION['usuario_id']);
      $barberos        = $this->barberos->activos();
      $servicios       = $this->servicios->activos();
      $datos           = $datos ?? ['barbero' => '', 'fecha' => '', 'hora' => '',
                                   'telefono' => '', 'servicios' => [], 'observaciones' => ''];
      $paso_inicial    = $errores ? 3 : 1;
      $hoy             = date('Y-m-d');
      $limite          = date('Y-m-d', strtotime('+' . DIAS_ANTICIPACION . ' days'));
      $mp_alias        = $this->mpAlias;
      $sena_porcentaje = $this->senaPorcentaje;

      require __DIR__ . '/../Views/reservas.php';
    }

    // POST /reservas.php: valida todo en el servidor y crea el turno.
    public function crear(): void {
      csrf_verificar();

      $datos = [
        'barbero'       => (string) ($_POST['barbero'] ?? ''),
        'fecha'         => (string) ($_POST['fecha'] ?? ''),
        'hora'          => (string) ($_POST['hora'] ?? ''),
        'telefono'      => trim((string) ($_POST['telefono'] ?? '')),
        'servicios'     => array_map('intval', (array) ($_POST['servicios'] ?? [])),
        'observaciones' => trim((string) ($_POST['observaciones'] ?? '')),
      ];

      $errores   = [];
      $servicios = $this->servicios->activos();
      $precios   = array_column($servicios, 'precio', 'id');
      $elegidos  = array_values(array_unique($datos['servicios']));

      if (!$this->barberos->existeActivo((int) $datos['barbero'])) {
        $errores[] = 'Elegí un barbero.';
      }

      if (!fecha_turno_valida($datos['fecha'])) {
        $errores[] = 'Elegí una fecha válida: desde hoy hasta ' . DIAS_ANTICIPACION . ' días, de lunes a sábado.';
      } elseif (!in_array($datos['hora'], HORARIOS_TURNOS, true) || hora_ya_paso($datos['fecha'], $datos['hora'])) {
        $errores[] = 'Elegí un horario disponible.';
      }

      if (!$elegidos || array_diff($elegidos, array_keys($precios))) {
        $errores[] = 'Elegí al menos un servicio.';
      }

      if (!preg_match('/^\+?[0-9 ()-]{8,20}$/', $datos['telefono'])) {
        $errores[] = 'Ingresá un WhatsApp válido, por ejemplo +54 9 11 1234-5678.';
      }

      if (!preg_match('/^.{0,255}$/us', $datos['observaciones'])) {
        $errores[] = 'Las observaciones no pueden superar los 255 caracteres.';
      }

      if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] === UPLOAD_ERR_NO_FILE) {
        $errores[] = 'Subí el comprobante de la seña.';
      } elseif ($_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
        $errores[] = 'No se pudo subir el comprobante (máximo 4 MB).';
      }

      if (!$errores
          && $this->turnos->activosDeUsuario((int) $_SESSION['usuario_id']) >= MAX_TURNOS_ACTIVOS) {
        $errores[] = 'Ya tenés ' . MAX_TURNOS_ACTIVOS . ' turnos activos. Cancelá alguno desde Mi cuenta para reservar otro.';
      }

      if ($errores) {
        $this->mostrar($errores, $datos);
        return;
      }

      // La seña se calcula en el servidor sobre los precios reales de la base,
      // nunca con lo que mande el navegador.
      $total      = array_sum(array_map(fn($id) => (float) $precios[$id], $elegidos));
      $sena_monto = $total * $this->senaPorcentaje / 100;

      $preciosElegidos = [];
      foreach ($elegidos as $servicioId) {
        $preciosElegidos[$servicioId] = $precios[$servicioId];
      }

      $subida = null;

      try {
        $this->turnos->crear(
          (int) $_SESSION['usuario_id'],
          $datos,
          $preciosElegidos,
          $sena_monto,
          function ($turnoId) use (&$subida) {
            $subida = guardar_comprobante($_FILES['comprobante'], $turnoId);
            return $subida;
          }
        );

        header('Location: mi_cuenta.php?msg=reservado');
        exit;

      } catch (Throwable $ex) {
        // Si el comprobante se guardó pero la transacción falló, se borra el archivo huérfano.
        if (!empty($subida['archivo'])) {
          $archivo = __DIR__ . '/../../var/comprobantes/' . basename($subida['archivo']);
          if (is_file($archivo)) {
            unlink($archivo);
          }
        }

        if ($ex instanceof PDOException && $ex->getCode() === '23000') {
          $errores[] = 'Ese horario se acaba de ocupar. Elegí otro.';
        } elseif ($ex instanceof RuntimeException) {
          $errores[] = $ex->getMessage();
        } else {
          throw $ex;
        }

        $this->mostrar($errores, $datos);
      }
    }
  }
