<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Datatheke\Bundle\PagerBundle\Pager\Filter;
use Datatheke\Bundle\CoreBundle\Document\User;

/**
 * @Route("/{username}", requirements={"username"="^(?!doc$|developer$|my-account$|about-us$|search$)[a-zA-Z0-9-_\.]+"})
 */
class AccountController extends BaseController
{
    /**
     * @Route("", name="account")
     *
     * @ParamConverter("user", options={"param"="username"})
     *
     * @Template("DatathekeFrontendBundle:Account:libraries.html.twig")
     */
    public function indexAction(Request $request, User $user)
    {
        $adapter = $this->get('datatheke.manager.library')->getAdapter($user);

        return $this->processPager($request, $user, $adapter);
    }

    /**
     * @Route("/shares", name="account_shares")
     *
     * @ParamConverter("user", options={"right"="ACCOUNT_ADMIN", "param"="username"})
     *
     * @Template("DatathekeFrontendBundle:Account:libraries.html.twig")
     */
    public function sharesAction(Request $request, User $user)
    {
        $adapter = $this->get('datatheke.manager.library')->getSharedAdapter($user);

        return $this->processPager($request, $user, $adapter, 'share');
    }

    /**
     *
     */
    protected function processPager(Request $request, User $account, $adapter, $category = 'account')
    {
        // Filter
        $query   = $request->request->get('query', $request->query->get('query', null));
        $adapter = $this->get('datatheke.manager.library')->search($query, $adapter);

        // Pager
        $pager = $this->get('datatheke.pager')->createHttpPager($adapter, array('item_count_per_page' => 10));
        $view  = $pager->handleRequest($request)
            ->setParameters(array(
                'username' => $account->getUsername(),
                'query'    => $query
            ))
        ;

        return array(
            'account'  => $account,
            'pager'    => $view,
            'query'    => $query,
            'category' => $category
        );
    }
}
