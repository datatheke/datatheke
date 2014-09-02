<?php

namespace Datatheke\Bundle\CoreBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;

use Datatheke\Bundle\PagerBundle\Pager\Adapter\MongoDBQueryBuilderAdapter;
use Datatheke\Bundle\PagerBundle\Pager\Filter;
use Datatheke\Bundle\PagerBundle\Pager\OrderBy;

use Datatheke\Bundle\CoreBundle\Document\Library;
use Datatheke\Bundle\CoreBundle\Document\Comment;

class CommentManager
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function save(Comment $comment)
    {
        $this->documentManager->persist($comment);
        $this->documentManager->flush();

        return $this;
    }

    public function getAdapter(Library $library = null)
    {
        $adapter = new MongoDBQueryBuilderAdapter($this->documentManager->createQueryBuilder('DatathekeCoreBundle:Comment'));

        if ($library) {
            $adapter->setFilter(new Filter(array('library'), array(new \MongoId($library->getId())), array(Filter::OPERATOR_EQUALS)), 'library');
        }

        $adapter->setOrderBy(new OrderBy(array('updatedAt' => OrderBy::DESC)));

        return $adapter;
    }
}
