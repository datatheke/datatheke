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

use Datatheke\Bundle\CoreBundle\Document\Library;
use Datatheke\Bundle\CoreBundle\Document\User;

class LibraryManager
{
    protected $securityContext;
    protected $documentManager;

    protected $userId;

    public function __construct(SecurityContext $securityContext, DocumentManager $documentManager)
    {
        $this->securityContext = $securityContext;
        $this->documentManager = $documentManager;

        if (($token = $securityContext->getToken()) && is_object($user = $token->getUser())) {
            $this->userId = $user->getId();
        }
    }

    public function find($id, $right = null)
    {
        $library = $this->documentManager->getRepository('DatathekeCoreBundle:Library')->find($id);

        if (null !== $right && !$this->securityContext->isGranted($right, $library)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        if (null !== $library && $library->isDeleted()) {
            throw new NotFoundHttpException('Library not found');
        }

        return $library;
    }

    public function delete(Library $library)
    {
        $library->setDeleted(true);
        $this->save($library);

        return $this;
    }

    public function save(Library $library)
    {
        $this->documentManager->persist($library);
        $this->documentManager->flush();

        return $this;
    }

    public function getAdapter(User $account = null)
    {
        $adapter = new MongoDBQueryBuilderAdapter($this->documentManager->createQueryBuilder('DatathekeCoreBundle:Library'));
        $adapter->addField(new Field('shares.user', Field::TYPE_OBJECT, 'shares.user.$id'), 'shares.user');

        $filter = array(
            'operator' => Filter::LOGICAL_AND,
            'criteria' => array(
                array(
                    'field'    => 'deleted',
                    'operator' => Filter::OPERATOR_EQUALS,
                    'value'    => false
                ),
                array(
                    'operator' => Filter::LOGICAL_OR,
                    'criteria' => array(
                        array(
                            'field'    => 'private',
                            'operator' => Filter::OPERATOR_EQUALS,
                            'value'    => false
                        ),
                        array(
                            'field'    => 'owner',
                            'operator' => Filter::OPERATOR_EQUALS,
                            'value'    => new \MongoId($this->userId)
                        ),
                        array(
                            'field'    => 'shares.user',
                            'operator' => Filter::OPERATOR_EQUALS,
                            'value'    => new \MongoId($this->userId)
                        ),
                    )
                )
            )
        );

        $adapter->setFilter(Filter::createFromArray($filter), 'access');
        if ($account) {
            $adapter->setFilter(new Filter(array('owner'), array(new \MongoId($account->getId())), array(Filter::OPERATOR_EQUALS)), 'account');
        }

        $adapter->setOrderBy(new OrderBy(array('updatedAt' => OrderBy::DESC)));

        return $adapter;
    }

    public function getSharedAdapter(User $user)
    {
        $adapter = $this->getAdapter();
        $adapter->setFilter(new Filter(array('shares.user'), array(new \MongoId($user->getId())), array(Filter::OPERATOR_EQUALS)), 'shares');

        return $adapter;
    }

    public function search($query, MongoDBQueryBuilderAdapter $adapter = null)
    {
        if (!$adapter) {
            $adapter = $this->getAdapter();
        }

        if (null !== $query) {
            $filters = array(
                'operator' => Filter::LOGICAL_OR,
                'criteria' => array(
                    array(
                        'field'    => 'name',
                        'operator' => Filter::OPERATOR_CONTAINS,
                        'value'    => $query
                    ),
                    array(
                        'field'    => 'description',
                        'operator' => Filter::OPERATOR_CONTAINS,
                        'value'    => $query
                    ),
                )
            );

            $adapter->setFilter(Filter::createFromArray($filters));
        }

        return $adapter;
    }
}
