<?php

class CurlTest extends PHPUnit_Framework_TestCase {

    private $curl;
    private $testUrl = 'http://example.com';

    public function setUp()
    {
        $this->curl = new \Alioygur\Curl\Curl();
    }

    public function testHeadMethod()
    {
        $response = $this->curl->head($this->testUrl);

        $this->assertEquals('HEAD', $response->requestHeaders('Method'));
        $this->assertEmpty($response->body());
    }

    public function testGetMethod()
    {
        $response = $this->curl->get($this->testUrl, ['foo_query_parameter' => 'foo query parameter value']);

        $this->assertEquals('GET', $response->requestHeaders('Method'));
        $this->assertEquals(200, $response->statusCode());
    }

    public function testPostMethod()
    {
        $response = $this->curl->post($this->testUrl, ['name' => 'jhon']);

        $this->assertEquals('POST', $response->requestHeaders('Method'));
        $this->assertEquals('application/x-www-form-urlencoded', $response->requestHeaders('Content-Type'));
    }

    public function testPutMethod()
    {
        $response = $this->curl->put($this->testUrl);

        $this->assertEquals('PUT', $response->requestHeaders('Method'));
        $this->assertArrayHasKey('Status', $response->headers());
    }

    public function testDeleteMethod()
    {
        $response = $this->curl->delete($this->testUrl);

        $this->assertEquals('DELETE', $response->requestHeaders('Method'));
        $this->assertArrayHasKey('Status', $response->headers());
    }

    public function testSetHeader()
    {
        $response = $this->curl->setHeader('Content-Type', 'application/json')->get($this->testUrl);
        $this->assertEquals('application/json', $response->requestHeaders('Content-Type'));
    }

    public function testSetOptions()
    {
        $response = $this->curl->setOption('CURLOPT_HTTP_VERSION', CURL_HTTP_VERSION_1_0)->get($this->testUrl);
        $this->assertEquals('HTTP/1.0', $response->requestHeaders('Version'));
    }

    public function testSetCookieFile()
    {
        $cookie_file = tempnam(sys_get_temp_dir(), 'cookie');

        $response = $this->curl->setCookieFile($cookie_file)->get($this->testUrl, ['foo_query_parameter' => 'foo query parameter value']);

        $this->assertEquals($cookie_file, $this->curl->getCookieFile());
        $this->assertFileExists($this->curl->getCookieFile());
        $this->assertEquals(200, $response->statusCode());
    }

    public function testSetBasicAuth()
    {
        $base64EncodedCredentials = base64_encode("alioygur:password");
        $response = $this->curl->setBasicAuth("alioygur", "password")->get($this->testUrl);

        $this->assertArrayHasKey('Authorization', $response->requestHeaders());
        $this->assertEquals('Basic ' . $base64EncodedCredentials, $response->requestHeaders('Authorization'));
    }

    /**
     * @expectedException \Alioygur\Curl\CurlException
     */
    public function testGivingExceptionOnError()
    {
        $this->curl->get('http://alioygur'); # get request to bad url;
    }
} 