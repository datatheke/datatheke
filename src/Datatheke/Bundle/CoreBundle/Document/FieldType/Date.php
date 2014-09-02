<?php

namespace Datatheke\Bundle\CoreBundle\Document\FieldType;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Date
{
    /**
     * @MongoDB\Date
     */
    public $value;
}
