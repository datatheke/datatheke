<?php

namespace Datatheke\Bundle\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use FOS\RestBundle\Controller\FOSRestController;

use JMS\Serializer\SerializationContext;

use Datatheke\Bundle\CoreBundle\Document\Library;

/**
 * @Route("/{version}", defaults={"_format"="json", "version"="v2"}, requirements={"_format"="json|xml", "version"="v1|v2"})
 */
class LibraryController extends FOSRestController
{
    /**
     * @Method({"GET"})
     *
     * @Route("/libraries.{_format}", requirements={"_format"="json"})
     */
    public function allAction(Request $request, $version)
    {
        $adapter = $this->get('datatheke.manager.library')->getAdapter($this->getUser());
        $pager = $this->get('datatheke.pager')->createHttpPager($adapter, array('item_count_per_page' => 20));
        $pager->getHandler()->setOption('current_page_number_param', 'page');

        $view = $pager->handleRequest($request);
        $context = SerializationContext::create()->setAttribute('gloomy_compatibility', ('v1' === $version));

        return $this->handleView($this->view($view, 200)->setSerializationContext($context));
    }

    /**
     * @Method({"GET"})
     *
     * @Route("/libraries/{id}.{_format}", name="api_library_get")
     * @Route("/library/{id}.{_format}")
     *
     * @ParamConverter("library", options={"param"="id"})
     */
    public function getAction($version, Library $library)
    {
        return $this->handleView($this->view($library, 200));
    }

    /**
     * @Method({"DELETE"})
     *
     * @Route("/libraries/{id}.{_format}")
     * @Route("/library/{id}.{_format}")
     *
     * @ParamConverter("library", options={"right"="LIBRARY_ADMIN", "param"="id"})
     */
    public function deleteAction($version, Library $library)
    {
        $this->get('datatheke.manager.library')->delete($library);

        return new Response('', 204);
    }

    /**
     * @Method({"POST"})
     *
     * @Route("/libraries.{_format}")
     * @Route("/library.{_format}")
     */
    public function postAction(Request $request, $version)
    {
        $library = new Library();
        $library->setOwner($this->getUser());

        return $this->processForm($request, $version, $library);
    }

    /**
     * @Method({"PUT"})
     *
     * @Route("/libraries/{id}.{_format}")
     * @Route("/library/{id}.{_format}")
     *
     * @ParamConverter("library", options={"right"="LIBRARY_ADMIN", "param"="id"})
     */
    public function putAction(Request $request, $version, Library $library)
    {
        return $this->processForm($request, $version, $library);
    }

    /**
     *
     */
    protected function processForm(Request $request, $version, Library $library)
    {
        $view = '';
        $statusCode = 200;
        $headers = array();

        $name = 'v1' === $version ? 'library' : '';

        $form = $this->get('form.factory')->createNamed($name, 'datatheke_library', $library, array(
            'method' => $request->getMethod(),
            'csrf_protection' => false,
            'api' => true
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $statusCode = $library->getId() ? 204 : 201;
            $this->get('datatheke.manager.library')->save($library);

            if (201 === $statusCode) {
                $view = array('id' => $library->getId());
                $headers['Location'] = $this->generateUrl('api_library_get', array('id' => $library->getId(), 'version' => $version), true);
            }
        } else {
            $view = $form;
        }

        return $this->handleView($this->view($view, 200));
    }
}
