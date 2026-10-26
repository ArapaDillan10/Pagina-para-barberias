<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('servicios', 'Servicios::index');
$routes->get('servicios/nuevo', 'Servicios::nuevo');
$routes->post('servicios', 'Servicios::guardar', ['filter' => 'csrf']);
$routes->get('servicios/eliminar/(:num)', 'Servicios::eliminar/$1');
$routes->get('api/servicios', 'Api\Servicios::index');