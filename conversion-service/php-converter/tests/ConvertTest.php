<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ConvertTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:4001',
            'http_errors' => false,   // para no lanzar excepciones en 4xx/5xx
        ]);
    }

    public function testHealthEndpoint()
    {
        $res  = $this->client->get('/health');
        $this->assertEquals(200, $res->getStatusCode());
        $body = json_decode($res->getBody(), true);
        $this->assertArrayHasKey('status', $body);
        $this->assertEquals('ok', $body['status']);
    }

    public function testConvertWithoutBody()
    {
        $res  = $this->client->post('/convert', ['json' => []]);
        $this->assertEquals(400, $res->getStatusCode());
        $body = json_decode($res->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function testConvertWithFakeInstruction()
    {
        $id      = 'fake-id-123';
        $payload = [
            'id'   => $id,
            'input'=> [
                'filePath'     => '/var/www/html/uploads/fake.txt',
                'originalName' => 'fake.txt',
                'formato'      => 'txt'
            ]
        ];

        $res = $this->client->post('/convert', ['json' => $payload]);
        $this->assertEquals(200, $res->getStatusCode(), 'El endpoint debe devolver 200 OK');
        
        $body = json_decode($res->getBody(), true);
        $this->assertEquals('completado', $body['status']);
        $this->assertEquals($id, $body['id']);
        $this->assertStringContainsString("/converted/{$id}.txt", $body['resultUrl']);
    }
}