<?php

namespace Datatheke\Bundle\ApiBundle\Tests\Controller;

use Datatheke\Bundle\ApiBundle\Tests\DatathekeWebTestCase;

class SecurityControllerTest extends DatathekeWebTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

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
