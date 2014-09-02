<?php

namespace Datatheke\Bundle\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testGetToken()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/v1/token', array(), array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'test_password',
        ));

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $response);
    }
}
