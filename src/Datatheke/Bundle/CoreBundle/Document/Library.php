<?php

namespace Datatheke\Bundle\CoreBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation\Exclude;

use Datatheke\Bundle\CoreBundle\Document\Behavior\ImageUploadTrait;

/**
 * @MongoDB\Document
 */
class Library
{
    use ImageUploadTrait;

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     *
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @MongoDB\String
     */
    protected $description;

    /**
     * @MongoDB\Boolean
     */
    protected $private;

    /**
     * @MongoDB\Boolean
     */
    protected $collaborative;

    /**
     * @MongoDB\Boolean
     *
     * @Exclude
     */
    protected $deleted;

    /**
     * @MongoDB\Date
     */
    protected $createdAt;

    /**
     * @MongoDB\Date
     */
    protected $updatedAt;

    /**
     * @MongoDB\ReferenceOne(targetDocument="User", cascade="all")
     *
     * @Exclude
     */
    protected $owner;

    /**
     * @MongoDB\EmbedMany(targetDocument="Share")
     *
     * @Exclude
     */
    protected $shares;

    public function __construct()
    {
        $this->createdAt     = new \DateTime();
        $this->updatedAt     = new \DateTime();
        $this->private       = true;
        $this->collaborative = false;
        $this->deleted       = false;
        $this->shares        = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set private
     *
     * @param boolean $private
     */
    public function setPrivate($private)
    {
        $this->private = $private;
    }

    /**
     * Is private
     *
     * @return string $private
     */
    public function isPrivate()
    {
        return $this->private;
    }

    public function setPublic($public)
    {
        $this->private = !$public;
    }

    public function isPublic()
    {
        return !$this->private;
    }

    /**
     * Set collaborative
     *
     * @param boolean $collaborative
     */
    public function setCollaborative($collaborative)
    {
        $this->collaborative = $collaborative;
    }

    /**
     * Is collaborative
     *
     * @return string $collaborative
     */
    public function isCollaborative()
    {
        return $this->collaborative;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getShares()
    {
        return $this->shares;
    }

    public function setShares($shares)
    {
        $this->shares = $shares;
    }

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    public function isDeleted()
    {
        return $this->deleted;
    }
}
