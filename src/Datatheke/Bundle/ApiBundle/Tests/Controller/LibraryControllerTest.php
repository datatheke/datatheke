<?php

namespace Datatheke\Bundle\ApiBundle\Tests\Controller;

use Datatheke\Bundle\ApiBundle\Tests\DatathekeWebTestCase;

class LibraryControllerTest extends DatathekeWebTestCase
{
    protected $client;
    protected $token;

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $crawler = $this->client->request('POST', '/api/v1/token', array(), array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'test_password',
        ));

        $this->token = json_decode($this->client->getResponse()->getContent(), true);
    }

    public function testCreateLibrary()
    {
        $crawler = $this->client->request('POST', '/api/v1/library', array(), array(), array(
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token['token'],
            'CONTENT_TYPE'  => 'application/json',
        ), '{"library": {"name": "My brand new library !", "description": "Really cool"}}');

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $response);
        $this->assertArrayHasKey('id', $response);
    }
}
