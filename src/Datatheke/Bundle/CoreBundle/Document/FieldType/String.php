<?php

namespace Datatheke\Bundle\CoreBundle\Document\FieldType;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class String
{
    /**
     * @MongoDB\String
     */
    public $value;
}
