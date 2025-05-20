<?php
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\Factory\AppFactory;

// Configurar PSR-17
AppFactory::setResponseFactory(new Psr17Factory());
$app = AppFactory::create();

// Rutas definidas aquí:
$app->get('/health', function (Request $req, Response $res) {
    $res->getBody()->write(json_encode(['status'=>'ok','timestamp'=>date('c')]));
    return $res->withHeader('Content-Type','application/json');
});

$app->get('/converted/{file}', function (Request $req, Response $res, $args) {
    $file = basename($args['file']);
    $path = __DIR__ . '/../converted/' . $file;
    if (!file_exists($path)) {
        return $res->withStatus(404);
    }
    $stream = fopen($path, 'rb');
    return $res
        ->withBody(new \Nyholm\Psr7\Stream($stream))
        ->withHeader('Content-Type', 'application/octet-stream');
});

/**
 * POST /convert
 * Recibe JSON: { id, input: { filePath, originalName, formato } }
 * filePath apunta a /uploads/{tempname}
 */
$app->post('/convert', function (Request $req, Response $res) {
    $body = json_decode((string)$req->getBody(), true);

    if (
        !isset($body['id']) ||
        !isset($body['input']['filePath']) ||
        !isset($body['input']['formato'])
    ) {
        $error = ['error' => 'Se requieren id, input.filePath y formato.'];
        $res->getBody()->write(json_encode($error));
        return $res->withStatus(400)
                   ->withHeader('Content-Type', 'application/json');
    }

    $id        = $body['id'];
    $filePath  = $body['input']['filePath'];   
    $formato   = $body['input']['formato'];

    $uploadFullPath = __DIR__ . '/../' . ltrim($filePath, '/');

    if (!file_exists($uploadFullPath)) {
        $error = ['error' => "No se encontró el archivo en {$filePath}"];
        $res->getBody()->write(json_encode($error));
        return $res->withStatus(404)
                   ->withHeader('Content-Type', 'application/json');
    }

    $outDir = __DIR__ . '/../converted';
    if (!is_dir($outDir)) {
        mkdir($outDir, 0777, true);
    }

    $outputFile = "{$id}.{$formato}";
    $outPath    = "{$outDir}/{$outputFile}";

    copy($uploadFullPath, $outPath);

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:4001';
    $resultUrl = "http://{$host}/converted/{$outputFile}";

    try {
        $client = new Client([
            'base_uri' => 'http://conversion-node:4000',
            'timeout'  => 5.0
        ]);
        $response = $client->post('/callback', [
            'json' => [
                'id'        => $id,
                'resultUrl' => $resultUrl
            ]
        ]);
    } catch (\Exception $e) {
        error_log("Callback failed: " . $e->getMessage());
    }

        $payload = [
            'status'    => 'completado',
            'id'        => $id,
            'resultUrl' => $resultUrl
        ];
    $res->getBody()->write(json_encode($payload));
    return $res->withHeader('Content-Type', 'application/json');
});

return $app;