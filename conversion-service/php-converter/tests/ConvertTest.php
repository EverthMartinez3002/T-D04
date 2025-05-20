<?php
use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\UploadedFile;

class ConvertTest extends TestCase
{
    private $app;
    private $psr17;

    protected function setUp(): void
    {
        // Cargar la app
        $this->app   = require __DIR__ . '/../src/app.php';
        $this->psr17 = new Psr17Factory();
    }

    public function testHealthEndpoint()
    {
        $request  = new ServerRequest('GET', '/health');
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertSame('ok', $body['status']);
    }

    public function testConvertWithoutFile()
    {
        // Petición multipart vacía
        $request = (new ServerRequest('POST', '/convert'))
            ->withParsedBody(['formato'=>'pdf']);

        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function testConvertWithFakeFile()
    {
        // Preparo un fichero en /tmp para simular upload
        $tmp = tempnam(sys_get_temp_dir(),'phpconv');
        file_put_contents($tmp, 'contenido');

        $uploaded = new UploadedFile(
            $tmp,
            filesize($tmp),
            UPLOAD_ERR_OK,
            'doc.txt',
            'text/plain'
        );

        // Construyo la petición multipart
        $request = (new ServerRequest('POST', '/convert'))
            ->withUploadedFiles(['archivo'=>$uploaded])
            ->withParsedBody(['formato'=>'txt']);

        $response = $this->app->handle($request);
        $this->assertEquals(202, $response->getStatusCode());

        $body = json_decode((string)$response->getBody(), true);
        $this->assertSame('pendiente', $body['status']);
        $this->assertArrayHasKey('id', $body);
    }
}