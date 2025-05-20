<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ConvertTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:4001'
        ]);
    }

    public function testHealthEndpoint()
    {
        $res = $this->client->get('/health');
        $this->assertEquals(200, $res->getStatusCode());
        $body = json_decode($res->getBody(), true);
        $this->assertArrayHasKey('status', $body);
        $this->assertEquals('ok', $body['status']);
    }

    public function testConvertWithoutBody()
    {
        $res = $this->client->post('/convert', ['json' => []]);
        $this->assertEquals(400, $res->getStatusCode());
        $body = json_decode($res->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }
}
