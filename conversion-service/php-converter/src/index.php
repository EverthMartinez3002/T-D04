<?php
require __DIR__ . '/../vendor/autoload.php';

use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\Factory\AppFactory;

AppFactory::setResponseFactory(new Psr17Factory());

$app = AppFactory::create();

$app->post('/convert', function ($req, $res) {
    $payload = ['status' => 'pendiente', 'id' => 'tarea-456'];
    $res->getBody()->write(json_encode($payload));
    return $res->withHeader('Content-Type', 'application/json');
});

$app->run();
