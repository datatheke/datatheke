<?php

namespace Datatheke\Bundle\CoreBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Validator\Constraints\Choice;

/**
 * @MongoDB\EmbeddedDocument
 */
class Field
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     *
     * @Assert\NotBlank
     */
    protected $label;

    /**
     * @MongoDB\String
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices = {"collection", "string", "textarea", "date", "coordinates"},
     *                message = "Invalid field type (must be 'collection', 'string', 'textarea', 'date' or 'coordinates')")
     */
    protected $type;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Collection")
     */
    protected $collection;

    /**
     * @MongoDB\Boolean
     */
    protected $multiple;

    /**
     * @MongoDB\Boolean
     */
    protected $deleted;

    public function __construct()
    {
        $this->type = 'string';
        $this->multiple = false;
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

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
    }

    public function isMultiple()
    {
        return $this->multiple;
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
