<?php
// Modelo del módulo de servicios en CodeIgniter 4 (TP10 - Parte B).
// Usa el ORM/Query Builder del framework: no hay SQL escrito a mano.
namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table         = 'servicios';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nombre', 'descripcion', 'precio', 'imagen', 'estado'];
}
