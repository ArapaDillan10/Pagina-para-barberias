<?php
// Controlador del módulo de servicios (TP10 - Parte B).
// Maneja las rutas /servicios: listado (vista), alta y baja.
namespace App\Controllers;

use App\Models\ServicioModel;

class Servicios extends BaseController
{
    private ServicioModel $modelo;

    public function __construct()
    {
        $this->modelo = new ServicioModel();
        helper('form'); // para csrf_field() en las vistas
    }

    // GET /servicios → lista en una vista HTML.
    public function index()
    {
        $data['servicios'] = $this->modelo
            ->where('estado', 1)
            ->orderBy('precio', 'DESC')
            ->findAll();

        return view('servicios/index', $data);
    }

    // GET /servicios/nuevo → formulario de alta.
    public function nuevo()
    {
        return view('servicios/form');
    }

    // POST /servicios → guarda en la base con el modelo.
    public function guardar()
    {
        $this->modelo->save([
            'nombre'      => trim((string) $this->request->getPost('nombre')),
            'descripcion' => trim((string) $this->request->getPost('descripcion')),
            'precio'      => (float) $this->request->getPost('precio'),
            'estado'      => 1,
        ]);

        return redirect()->to('/servicios');
    }

    // GET /servicios/eliminar/N → baja.
    public function eliminar($id)
    {
        $this->modelo->delete((int) $id);

        return redirect()->to('/servicios');
    }
}
