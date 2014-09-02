<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

class SearchController extends BaseController
{
    /**
     * @Route("/search", name="search")
     *
     * @Template()
     */
    public function librariesAction(Request $request)
    {
        // Filter
        $query   = $request->request->get('query', $request->query->get('query', null));
        $adapter = $this->get('datatheke.manager.library')->search($query);

        // Pager
        $pager = $this->get('datatheke.pager')->createHttpPager($adapter, array('item_count_per_page' => 40));
        $view  = $pager->handleRequest($request)
            ->setRoute('search')
            ->setParameters(array('query' => $query))
        ;

        return array(
            'pager' => $view,
            'query' => $query
        );
    }
}
