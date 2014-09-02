<?php

namespace Datatheke\Bundle\CoreBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Share
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="User")
     */
    protected $user;

    /**
     * @MongoDB\Boolean
     */
    protected $write;

    /**
     * @MongoDB\Boolean
     */
    protected $admin;

    public function __construct()
    {
        $this->write = false;
        $this->admin = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setWrite($write)
    {
        $this->write = $write;
    }

    public function getWrite()
    {
        return $this->write;
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    public function getAdmin()
    {
        return $this->admin;
    }
}
