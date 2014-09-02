<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Datatheke\Bundle\PagerBundle\DataGrid\Handler\Http\BootstrapTypeaheadHandler;

class UserController extends BaseController
{
    /**
     * @Route("/users/autocomplete", name="autocomplete_user")
     */
    public function aucompleteUserAction()
    {
        $handler = new BootstrapTypeaheadHandler(array('search_fields' => array('username', 'email')));

        return $this->get('datatheke.datagrid')
            ->createHttpDataGrid('DatathekeCoreBundle:User', array(), $handler)
            ->showOnly(array('username'))
            ->handleRequest($this->getRequest())
        ;
    }
}
