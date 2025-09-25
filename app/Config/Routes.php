<?php

use CodeIgniter\Router\RouteCollection;

// Strona główna
$routes->get('/', 'Home::index');

// Gry liczbowe
//$routes->group('games', function($routes) {
//    $routes->get('/', 'Games::index');
//    $routes->get('add', 'Games::add');
//    $routes->post('create', 'Games::create');
//    $routes->get('edit/(:num)', 'Games::edit/$1');
//    $routes->post('update/(:num)', 'Games::update/$1');
//    $routes->delete('delete/(:num)', 'Games::delete/$1');
//});



$routes->group('games', function($routes) {
// Gry - CRUD
$routes->get('/', 'GameController::index');
$routes->get('add', 'GameController::form');
$routes->get('(:num)/edit', 'GameController::form/$1');
$routes->post('save', 'GameController::save');

// Gry - wyświetlanie (slug jako ostatni parametr)
$routes->get('(:segment)', 'GameController::show/$1');

// Wygrane - zarządzanie
$routes->get('(:num)/prizes', 'GamePrizesController::index/$1');
$routes->get('(:num)/prizes/add', 'GamePrizesController::form/$1');
$routes->get('(:num)/prizes/(:num)/edit', 'GamePrizesController::form/$1/$2');
$routes->post('(:num)/prizes/save', 'GamePrizesController::save/$1');
$routes->get('(:num)/prizes/(:num)/delete', 'GamePrizesController::delete/$1/$2');
$routes->get('(:num)/prizes/import-multi-multi', 'GamePrizesController::importMultiMultiPrizes/$1');

}); 

// API
$routes->get('api/games/(:num)/variants/(:num)/prizes', 'GameController::getPrizesForVariant/$1/$2');
$routes->post('api/games/calculate-prize', 'GameController::calculatePrize');



// Losowania
$routes->group('draws', function($routes) {
    $routes->get('/', 'Draws::index');
    $routes->get('(:segment)', 'Draws::index/$1'); // dla konkretnej gry
    $routes->post('import', 'Draws::import');
    
    // Trasy dla uzupełniania braków
    $routes->post('checkMissingDraws', 'Draws::checkMissingDraws');
    $routes->post('fillSingleDraw', 'Draws::fillSingleDraw');

    $routes->get('test', 'Draws::test');
});

// Statystyki
$routes->group('statistics', function($routes) {
    $routes->get('/', 'Statistics::index');
    $routes->get('add', 'Statistics::add');
    $routes->post('create', 'Statistics::create');
    $routes->get('edit/(:num)', 'Statistics::edit/$1');
    $routes->post('update/(:num)', 'Statistics::update/$1');
    $routes->delete('delete/(:num)', 'Statistics::delete/$1');
    $routes->post('start/(:num)', 'Statistics::start/$1');
    $routes->post('start-combo', 'Statistics::startCombo');
    $routes->get('progress/(:num)', 'Statistics::progress/$1');
});

// Strategie
$routes->group('strategies', function($routes) {
    $routes->get('/', 'Strategies::index');
    $routes->get('add', 'Strategies::add');
    $routes->post('create', 'Strategies::create');
    $routes->get('edit/(:num)', 'Strategies::edit/$1');
    $routes->post('update/(:num)', 'Strategies::update/$1');
    $routes->delete('delete/(:num)', 'Strategies::delete/$1');
    $routes->post('start/(:num)', 'Strategies::start/$1');
});

// Zakłady
$routes->group('bets', function($routes) {
    $routes->get('/', 'Bets::index');
    $routes->get('add', 'Bets::add');
    $routes->post('create', 'Bets::create');
    $routes->get('edit/(:num)', 'Bets::edit/$1');
    $routes->post('update/(:num)', 'Bets::update/$1');
    $routes->delete('delete/(:num)', 'Bets::delete/$1');
    $routes->post('generate/(:num)', 'Bets::generate/$1');
});

// Wyniki
$routes->group('results', function($routes) {
    $routes->get('/', 'Results::index');
    $routes->get('(:segment)', 'Results::index/$1'); // dla konkretnej gry
    $routes->get('month/(:num)/(:num)', 'Results::month/$1/$2'); // rok/miesiąc
    $routes->get('day/(:segment)', 'Results::day/$1'); // data
    $routes->post('refresh', 'Results::refresh');
});

// Ustawienia
$routes->group('settings', function($routes) {
    $routes->get('/', 'Settings::index');
    $routes->post('update', 'Settings::update');
    $routes->post('test-api', 'Settings::testApi');
});