<?php

namespace Datatheke\Bundle\CoreBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ODM\MongoDB\DocumentManager;

use Datatheke\Bundle\PagerBundle\Pager\Adapter\MongoDBQueryBuilderAdapter;
use Datatheke\Bundle\PagerBundle\Pager\Field;
use Datatheke\Bundle\PagerBundle\Pager\Filter;
use Datatheke\Bundle\PagerBundle\Pager\OrderBy;

use Datatheke\Bundle\CoreBundle\Document\Collection;
use Datatheke\Bundle\CoreBundle\Document\Library;

class CollectionManager
{
    protected $securityContext;
    protected $documentManager;
    protected $libraryManager;

    public function __construct(SecurityContext $securityContext, DocumentManager $documentManager, LibraryManager $libraryManager)
    {
        $this->securityContext = $securityContext;
        $this->documentManager = $documentManager;
        $this->libraryManager  = $libraryManager;
    }

    public function find($id, $right = null)
    {
        $collection = $this->documentManager->getRepository('DatathekeCoreBundle:Collection')->find($id);

        if (null !== $right && !$this->securityContext->isGranted($right, $collection)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        if (null !== $collection && $collection->isDeleted()) {
            throw new NotFoundHttpException('Collection not found');
        }

        return $collection;
    }

    public function delete(Collection $collection)
    {
        $collection->setDeleted(true);
        $this->save($collection);

        return $this;
    }

    public function save(Collection $collection)
    {
        $this->documentManager->persist($collection);
        $this->documentManager->flush();

        return $this;
    }

    public function getAdapter(Library $library = null)
    {
        $adapter = new MongoDBQueryBuilderAdapter($this->documentManager->createQueryBuilder('DatathekeCoreBundle:Collection'));
        $adapter->addField(new Field('fields.collection', Field::TYPE_OBJECT, 'fields.collection.$id'), 'fields.collection');

        $adapter->setFilter(new Filter(array('deleted'), array(false), array(Filter::OPERATOR_EQUALS)), 'access');

        if ($library) {
            $adapter->setFilter(new Filter(array('library'), array(new \MongoId($library->getId())), array(Filter::OPERATOR_EQUALS)), 'library');
        }

        $adapter->setOrderBy(new OrderBy(array('updatedAt' => OrderBy::DESC)));

        return $adapter;
    }

    public function getLinkedCollections(Collection $collection)
    {
        $adapter = $this->getAdapter($collection->getLibrary());
        $adapter->setFilter(new Filter(array('fields.collection'), array(new \MongoId($collection->getId())), array(Filter::OPERATOR_EQUALS)));

        $linkedCollections = array();
        foreach ($adapter->getItems() as $link) { // all linked collections
            foreach ($link->getFields() as $field) { // all fields
                if ($field->getType() == 'collection' && $field->getCollection() && $field->getCollection()->getId() == $collection->getId()) {
                    $linkedCollections[] = array('collection' => $link, 'field' => $field);
                }
            }
        }

        return $linkedCollections;
    }

    public function getLibraryCollections(Library $library)
    {
        return $this->getAdapter($library)->getItems();
    }

    public function getMaps(Library $library)
    {
        $adapter = $this->getAdapter($library);
        $maps = array();
        foreach ($adapter->getItems() as $collection) {
            foreach ($collection->getFields() as $field) {
                if ('coordinates' === $field->getType()) {
                    $maps[] = $collection;
                    break;
                }
            }
        }

        return $maps;
    }
}
