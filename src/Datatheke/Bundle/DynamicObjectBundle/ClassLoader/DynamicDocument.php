<?php

namespace Datatheke\Bundle\DynamicObjectBundle\ClassLoader;

use Doctrine\ODM\MongoDB\DocumentManager;

class DynamicDocument
{
    const prefix = 'Datatheke\\Bundle\\DynamicObjectBundle\\Document\\_';

    protected $dm;
    protected $cacheDir;

    public function __construct(DocumentManager $dm, $cacheDir)
    {
        $cacheDir .= '/datatheke/dynamic_document';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $this->dm = $dm;
        $this->cacheDir = $cacheDir;
    }

    public function classLoader($class)
    {
        if (0 === strpos($class, self::prefix)) {
            $this->loadDocument($class);
        }
    }

    protected function loadDocument($class)
    {
        $id = str_replace(self::prefix, '', $class);
        $collection = $this->dm->getRepository('DatathekeCoreBundle:Collection')->find($id);

        $fields = array('$id');
        foreach ($collection->getFields() as $field) {
            $fields[] = '$_'.$field->getId();
        }
        $fields = join(',', $fields);

        $class = <<<EOT
<?php
namespace Datatheke\\Bundle\\DynamicObjectBundle\\Document;

use JMS\Serializer\Annotation\Exclude;

class _$id {

    /**
     * @Exclude
     */
    public \$deleted;

    public $fields;

    public function getId()
    {
        return \$this->id;
    }
EOT;

        $toString = 'public function __toString() { return (string) $this->id; }';
        foreach ($collection->getFields() as $field) {
            if ($field->getType() !== 'collection' && !$field->isMultiple()) {
                if ('date' === $field->getType()) {
                    $toString = 'public function __toString() { return (string) $this->_'.$field->getId().'->format(\'d/m/Y\'); }';
                } else {
                    $toString = 'public function __toString() { return (string) $this->_'.$field->getId().'; }';
                }
                break;
            }
        }
        $class .= $toString;

        $class .= <<<EOT
}
EOT;

        $file = $this->cacheDir.'/'.md5($class).'.php';
        if (!file_exists($file)) {
            file_put_contents($file, $class);
        }

        require_once $file;
    }
}
