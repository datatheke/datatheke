<?php

namespace Datatheke\Bundle\CoreBundle\Document\Behavior;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

trait ImageUploadTrait
{
    /**
     * @MongoDB\String
     *
     * @Exclude
     */
    protected $imageType;

    /**
     * @MongoDB\Int
     *
     * @Exclude
     */
    protected $imageOrientation;

    /**
     * @Assert\Image(
     *     maxSize   = 200000,
     *     maxWidth  = 1000,
     *     maxHeight = 1000
     * )
     *
     * @Exclude
     */
    protected $imageUpload;

    public function getImageType()
    {
        return $this->imageType;
    }

    public function setImageType($imageType)
    {
        $this->imageType = $imageType;
    }

    public function getImageOrientation()
    {
        return $this->imageOrientation;
    }

    public function setImageOrientation($imageOrientation)
    {
        $this->imageOrientation = $imageOrientation;
    }

    public function getImageUpload()
    {
        return $this->imageUpload;
    }

    public function setImageUpload($imageUpload)
    {
        $this->imageUpload = $imageUpload;
    }

    public function imageIsVertical()
    {
        return ($this->imageOrientation == 1);
    }
}
