<?php

namespace Datatheke\Bundle\CoreBundle\Document;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

use JMS\Serializer\Annotation\Exclude;

use Datatheke\Bundle\CoreBundle\Document\Behavior\ImageUploadTrait;

/**
 * @MongoDB\Document
 */
class Collection
{
    use ImageUploadTrait;

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     *
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @MongoDB\String
     */
    protected $description;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Library")
     *
     * @Exclude
     */
    protected $library;

    /**
     * @MongoDB\EmbedMany(targetDocument="Field")
     *
     * @Assert\Count(min=1)
     * @Assert\Valid()
     */
    protected $fields = array();

    /**
     * @MongoDB\Boolean
     */
    protected $private;

    /**
     * @MongoDB\Boolean
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

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->private = false;
        $this->deleted = false;
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

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function addAnotherField($field)
    {
        $this->fields[] = $field;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields($noMultiple = false)
    {
        $list = array();
        foreach ($this->fields as $key => $field) {

            if ($noMultiple && $field->isMultiple()) {
                continue;
            }

            if ($field->isDeleted()) {
                continue;
            }

            $list[] = $field;
        }

        return $list;
    }

    public function getLibrary()
    {
        return $this->library;
    }

    public function setLibrary($library)
    {
        $this->library = $library;
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

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    public function isDeleted()
    {
        return $this->deleted;
    }
}
