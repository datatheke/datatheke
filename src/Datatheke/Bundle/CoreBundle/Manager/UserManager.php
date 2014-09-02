<?php

namespace Datatheke\Bundle\CoreBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Doctrine\ODM\MongoDB\DocumentManager;

use FOS\UserBundle\Model\UserManager as FOSUserManager;

use Datatheke\Bundle\PagerBundle\Pager\Adapter\MongoDBDocumentAdapter;

class UserManager
{
    private $securityContext;
    private $documentManager;

    public function __construct(SecurityContext $securityContext, DocumentManager $documentManager, FOSUserManager $FOSUserManager)
    {
        $this->securityContext = $securityContext;
        $this->documentManager = $documentManager;
        $this->FOSUserManager  = $FOSUserManager;
    }

    public function find($username, $right = null)
    {
        $user = $this->FOSUserManager->findUserByUsername($username);

        if (null !== $right && !$this->securityContext->isGranted($right, $user)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return $user;
    }

    public function getUserWrapper()
    {
        // TODO : use fos_user.user_manager ??
        $adapter = new MongoDBDocumentAdapter('DatathekeCoreBundle:User');
        // $adapter->setFilters(array('enabled'), array(true), array('equals'));
        return $wrapper;
    }
}
