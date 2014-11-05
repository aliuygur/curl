<?php

class CurlResponseTest extends PHPUnit_Framework_TestCase {

    private $curl;
    private $testUrl = 'http://example.com';

    public function setUp()
    {
        $this->curl = new \Alioygur\Curl\Curl();
    }

    public function testHeaders()
    {
        $response = $this->curl->get($this->testUrl);

        $this->assertTrue(is_array($response->headers()));
        $this->assertTrue(is_string($response->headers('Server')));
        $this->assertTrue(is_string($response->contentType()));
        $this->assertTrue(is_string($response->status()));
        $this->assertTrue(is_string($response->statusCode()));
    }

    public function testBody()
    {
        $response = $this->curl->get($this->testUrl);

        $this->assertGreaterThan(100, strlen($response->body()));
    }

    public function testShouldReturnResponseBodyWhenCallingToString()
    {
        $response = $this->curl->get($this->testUrl);

        $this->assertEquals($response->body(), (string) $response);
    }
} 