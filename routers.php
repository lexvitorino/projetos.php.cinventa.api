<?php
global $routes;
$routes = array();

$routes['/test'] = '/home/index';

$routes['/session'] = '/session';
$routes['/session/auth'] = '/session/auth';
$routes['/session/lock'] = '/session/lock';
$routes['/session/unlock'] = '/session/unlock';

$routes['/user'] = '/user';
$routes['/user/create'] = '/user/create';
$routes['/user/{id}'] = '/user/byId/:id';

$routes['/subscriber'] = '/subscriber';
$routes['/subscriber/create'] = '/subscriber/create';
$routes['/subscriber/{id}'] = '/subscriber/byId/:id';

$routes['/inscription'] = '/inscription';
$routes['/inscription/sendEmail'] = '/inscription/sendEmail';
$routes['/inscription/byEventoAndDataAndEmail/{evento}/{data}/{email}'] = '/inscription/byEventoAndDataAndEmail/:evento/:data/:email';
$routes['/inscription/byEventoAndData/{evento}/{data}'] = '/inscription/byEventoAndData/:evento/:data';
$routes['/inscription/byEmail/{email}/{ativo}'] = '/inscription/byEmail/:email/:ativo';
$routes['/inscription/create'] = '/inscription/create';
$routes['/inscription/{id}'] = '/inscription/byId/:id';
$routes['/inscription/confirmar'] = '/inscription/confirmar';
$routes['/inscription/vagasValidas/{chave}/{data}/{duplas}'] = '/inscription/vagasValidas/:chave/:data/:duplas';

$routes['/event'] = '/event';
$routes['/event/create'] = '/event/create';
$routes['/event/{id}'] = '/event/byId/:id';
