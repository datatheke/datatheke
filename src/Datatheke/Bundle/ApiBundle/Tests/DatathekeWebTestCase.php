<?php

namespace Datatheke\Bundle\ApiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DatathekeWebTestCase extends WebTestCase
{
    public function setUp()
    {
        static::bootKernel();

        $conn = static::$kernel->getContainer()->get('doctrine_mongodb.odm.default_connection');
        $conn->dropDatabase($conn->getConfiguration()->getDefaultDB());

        $manipulator = static::$kernel->getContainer()->get('fos_user.util.user_manipulator');
        $manipulator->create('test', 'test_password', 'test@test.fr', true, false);
    }
}
