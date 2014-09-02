<?php

namespace Datatheke\Bundle\CoreBundle\Manager;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ODM\MongoDB\DocumentManager;

use Datatheke\Bundle\PagerBundle\Pager\Adapter\MongoDBQueryBuilderAdapter;
use Datatheke\Bundle\PagerBundle\Pager\Filter;
use Datatheke\Bundle\PagerBundle\DataGrid\Factory;

use Datatheke\Bundle\CoreBundle\Document\Collection;

class ItemManager
{
    protected $documentManager;
    protected $dataGridFactory;

    public function __construct(DocumentManager $documentManager, Factory $dataGridFactory)
    {
        $this->documentManager = $documentManager;
        $this->dataGridFactory = $dataGridFactory;
    }

    public function find(Collection $collection, $id)
    {
        $item = $this->documentManager->getRepository($this->getClass($collection))->find($id);

        if (null !== $item && $item->deleted) {
            throw new NotFoundHttpException('Item not found');
        }

        return $item;
    }

    public function create(Collection $collection)
    {
        $class = $this->getClass($collection);

        return new $class();
    }

    public function delete(Collection $collection, $id)
    {
        $item = $this->find($collection, $id);
        $item->setDeleted(true);
        $this->save($item);

        return $this;
    }

    public function save($item)
    {
        $this->documentManager->persist($item);
        $this->documentManager->flush();

        return $this;
    }

    public function getClass(Collection $collection)
    {
        return '\\Datatheke\\Bundle\\DynamicObjectBundle\\Document\\_'.$collection->getId();
    }

    public function getPreviousItem(Collection $collection, $item)
    {
        $builder = $this->documentManager->createQueryBuilder($this->getClass($collection));

        return $builder
            ->field('_id')->lt($item->getId())
            ->field('deleted')->equals(null)
            ->sort(array('_id' => 'desc'))
            ->limit(1)
            ->getQuery()
            ->getSingleResult();
    }

    public function getNextItem(Collection $collection, $item)
    {
        $builder = $this->documentManager->createQueryBuilder($this->getClass($collection));

        return $builder
            ->field('_id')->gt($item->getId())
            ->field('deleted')->equals(null)
            ->sort(array('_id' => 'asc'))
            ->limit(1)
            ->getQuery()
            ->getSingleResult();
    }

    public function getAdapter(Collection $collection)
    {
        $adapter = new MongoDBQueryBuilderAdapter($this->documentManager->createQueryBuilder($this->getClass($collection)));
        $adapter->setFilter(new Filter(array('deleted'), array(null), array(Filter::OPERATOR_NULL)), 'deleted');

        return $adapter;
    }

    public function getDataGrid(Collection $collection)
    {
        $adapter = $this->getAdapter($collection);
        $datagrid = $this->dataGridFactory->createHttpDatagrid($adapter);

        $datagrid->getColumn('id')->hide();
        $datagrid->getColumn('deleted')->hide();

        foreach ($collection->getFields() as $field) {
            $column = $datagrid->getColumn('_'.$field->getId());

            $column->setLabel($field->getLabel());

            if ($field->isMultiple()) {
                $column->hide();
            }

            if ($field->isMultiple() || in_array($field->getType(), array('collection', 'coordinates'))) {
                $column->setFilterable(false);
                $column->setSortable(false);
            }
        }

        return $datagrid;
    }
}
