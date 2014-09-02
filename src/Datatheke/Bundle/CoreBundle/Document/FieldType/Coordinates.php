<?php

namespace Datatheke\Bundle\CoreBundle\Document\FieldType;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Coordinates
{
    /**
     * @MongoDB\Id
     */
    public $id;

    /**
     * @MongoDB\Float
     */
    protected $longitude;

    /**
     * @MongoDB\Float
     */
    protected $latitude;

    public function __construct($longitude = 0, $latitude = 0)
    {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function __toString()
    {
        return $this->latitude.'°, '.$this->longitude.'°';
    }
}
