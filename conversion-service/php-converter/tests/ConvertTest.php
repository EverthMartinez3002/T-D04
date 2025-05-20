<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\UploadedFile;
use Nyholm\Psr7\Factory\Psr17Factory;

final class ConvertTest extends TestCase
{
    private \Slim\App $app;
    private Psr17Factory $psr17;

    protected function setUp(): void
    {
        $this->app   = require __DIR__ . '/../src/app.php';
        $this->psr17 = new Psr17Factory();
    }

    public function testHealthEndpoint(): void
    {
        $req      = new ServerRequest('GET', '/health');
        $response = $this->app->handle($req);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertSame('ok', $body['status']);
        $this->assertArrayHasKey('timestamp', $body);
    }

    public function testConvertWithoutBody(): void
    {
        $req      = (new ServerRequest('POST', '/convert'))
            ->withParsedBody([]); 
        $response = $this->app->handle($req);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function testConvertWithFakeInstruction(): void
    {
        $id = 'fake-1234';
        $req = (new ServerRequest('POST', '/convert'))
            ->withParsedBody([
                'id'    => $id,
                'input' => [
                    'filePath'     => '/uploads/fake.txt',
                    'originalName' => 'fake.txt',
                    'formato'      => 'txt'
                ]
            ]);

        $response = $this->app->handle($req);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string)$response->getBody(), true);
        $this->assertSame('completado', $body['status']);
        $this->assertSame($id, $body['id']);
        $this->assertStringContainsString("/converted/{$id}.txt", $body['resultUrl']);
    }
}