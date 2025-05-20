<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\Factory\AppFactory;

AppFactory::setResponseFactory(new Psr17Factory());
$app = AppFactory::create();

// Health check
$app->get('/health', function (Request $req, Response $res) {
    $payload = ['status' => 'ok', 'timestamp' => date('c')];
    $res->getBody()->write(json_encode($payload));
    return $res->withHeader('Content-Type', 'application/json');
});

// Serve converted files
$app->get('/converted/{file}', function (Request $req, Response $res, array $args) {
    $file = basename($args['file']);
    $path = __DIR__ . '/../converted/' . $file;
    if (!is_file($path)) {
        return $res->withStatus(404);
    }
    $stream = new Stream(fopen($path, 'rb'));
    return $res
        ->withBody($stream)
        ->withHeader('Content-Type', 'application/octet-stream');
});

// Convert endpoint (in-memory simulation)
$app->post('/convert', function (Request $req, Response $res) {
    $body = json_decode((string)$req->getBody(), true);
    if (
        !isset($body['id']) ||
        !isset($body['input']['filePath']) ||
        !isset($body['input']['formato'])
    ) {
        $error = ['error' => 'Se requieren id, input.filePath y formato.'];
        $res->getBody()->write(json_encode($error));
        return $res
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    $id       = $body['id'];
    $formato  = $body['input']['formato'];

    // Simular procesamiento
    // (en producción usaría copy(), LibreOffice, Imagick, etc.)
    $resultUrl = "/converted/{$id}.{$formato}";

    $payload = [
        'status'    => 'completado',
        'id'        => $id,
        'resultUrl' => $resultUrl
    ];
    $res->getBody()->write(json_encode($payload));
    return $res->withHeader('Content-Type', 'application/json');
});

return $app;