<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Register::index');
$routes->get('register', 'Register::index');
$routes->post('register/submit', 'Register::submit');
$routes->post('register/check-line', 'Register::checkLine');

// Setup (hidden URL for shared hosting without CLI access)
$routes->get('setup', 'Setup::index');

// Auth
$routes->get('login', 'Auth::login');
$routes->post('login/attempt', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');

// Admin (protected by auth filter)
$routes->group('admin', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Admin::index');
    $routes->get('map', 'Admin::map');
    $routes->get('map-data', 'Admin::mapData');
    $routes->get('create', 'Admin::create');
    $routes->get('edit/(:num)', 'Admin::edit/$1');
    $routes->post('save', 'Admin::save');
    $routes->post('delete/(:num)', 'Admin::delete/$1');
});
