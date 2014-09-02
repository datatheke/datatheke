<?php

namespace Datatheke\Bundle\ApiBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

use FOS\OAuthServerBundle\Document\AuthCode as BaseAuthCode;

/**
 * @MongoDB\Document
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Client")
     */
    protected $client;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Datatheke\Bundle\CoreBundle\Document\User")
     */
    protected $user;
}
