<?php
// API REST del módulo: GET /api/servicios devuelve JSON (TP10 - Parte B).
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ServicioModel;
use CodeIgniter\HTTP\ResponseInterface;

class Servicios extends BaseController
{
    public function index(): ResponseInterface
    {
        $servicios = (new ServicioModel())
            ->where('estado', 1)
            ->orderBy('precio', 'DESC')
            ->findAll();

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'status' => 'success',
                'data'   => $servicios,
            ]);
    }
}
