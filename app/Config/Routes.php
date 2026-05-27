<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Register::index');
$routes->get('register', 'Register::index');
$routes->post('register/submit', 'Register::submit');
$routes->get('register/check-line', 'Register::checkLine');
