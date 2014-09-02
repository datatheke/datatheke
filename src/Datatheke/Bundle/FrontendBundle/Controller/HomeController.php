<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HomeController extends BaseController
{
    /**
     * @Route("/", name="index")
     *
     * @Template()
     */
    public function indexAction()
    {
        $nbCollections = $this->get('doctrine.odm.mongodb.document_manager')
            ->createQueryBuilder('DatathekeCoreBundle:Collection')
            ->getQuery()
            ->count()
        ;

        $nbUsers = $this->get('doctrine.odm.mongodb.document_manager')
            ->createQueryBuilder('DatathekeCoreBundle:User')
            ->getQuery()
            ->count()
        ;

        $lastLibraries = $this->get('doctrine.odm.mongodb.document_manager')
            ->createQueryBuilder('DatathekeCoreBundle:Library')
            ->field('private')->equals(false)
            ->field('deleted')->equals(false)
            ->sort('createdAt', 'desc')
            ->limit(30)
            ->getQuery()
            ->execute()
        ;

        return array('nbCollections' => $nbCollections, 'nbUsers' => $nbUsers, 'lastLibraries' => $lastLibraries);
    }
}
