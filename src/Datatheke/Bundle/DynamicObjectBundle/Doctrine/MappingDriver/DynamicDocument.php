<?php

namespace Datatheke\Bundle\DynamicObjectBundle\Doctrine\MappingDriver;

use Doctrine\ODM\MongoDB\Mapping\Driver\YamlDriver;

class DynamicDocument extends YamlDriver
{
    protected $dm;

    public function __construct($dm)
    {
        parent::__construct(null);

        $this->dm = $dm;
    }

    public function getElement($className)
    {
        $result = $this->loadMappingFile($this->findMappingFile($className));

        return $result[$className];
    }

    protected function loadMappingFile($id)
    {
        $collection = $this->dm->getRepository('DatathekeCoreBundle:Collection')->find($id);
        if (!$collection) {
            return;
        }

        $namespace = 'Datatheke\\Bundle\\DynamicObjectBundle\\Document\\';
        $class = $namespace.'_'.$id;

        $mapping = array();
        $mapping[$class]['fields']['id']['id'] = true;
        $mapping[$class]['fields']['deleted']['type'] = 'boolean';

        foreach ($collection->getFields() as $field) {

            switch ($field->getType()) {

                case 'collection':
                    $type = $field->isMultiple() ? 'referenceMany' : 'referenceOne';
                    $mapping[$class][$type]['_'.$field->getId()] = array(
                        'targetDocument' => $namespace.'_'.$field->getCollection()->getId()
                    );
                    break;

                case 'string':
                case 'textarea':
                case 'date';
                    if ($field->isMultiple()) {
                        $mapping[$class]['embedMany']['_'.$field->getId()] = array(
                            'targetDocument' => 'Datatheke\\Bundle\\CoreBundle\\Document\\FieldType\\'.ucfirst($field->getType())
                        );
                    } else {
                        $type = $field->getType();
                        if ($type == 'textarea') {
                            $type = 'string';
                        }

                        $mapping[$class]['fields']['_'.$field->getId()] = array(
                            'type' => $type
                        );
                    }
                    break;

                default:
                    $type = $field->isMultiple() ? 'embedMany' : 'embedOne';
                    $mapping[$class][$type]['_'.$field->getId()] = array(
                        'targetDocument' => 'Datatheke\\Bundle\\CoreBundle\\Document\\FieldType\\'.ucfirst($field->getType())
                    );
                    break;
            }
        }

        return $mapping;
    }

    protected function findMappingFile($className)
    {
        $tmp = str_replace('\\', '/', $className);

        return substr(basename($tmp), 1);
    }
}
