<?php

namespace Datatheke\Bundle\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use FOS\RestBundle\Controller\FOSRestController;

use JMS\Serializer\SerializationContext;

use Datatheke\Bundle\CoreBundle\Document\Collection;
use Datatheke\Bundle\CoreBundle\Document\Library;

/**
 * @Route("/{version}", defaults={"_format"="json", "version"="v2"}, requirements={"_format"="json|xml", "version"="v1|v2"})
 */
class CollectionController extends FOSRestController
{
    /**
     * @Method({"GET"})
     *
     * @Route("/libraries/{id}/collections.{_format}", requirements={"_format"="json"})
     * @Route("/library/{id}/collections.{_format}", requirements={"_format"="json"})
     *
     * @ParamConverter("library", options={"param"="id"})
     */
    public function allAction(Request $request, $version, Library $library)
    {
        $adapter = $this->get('datatheke.manager.collection')->getAdapter($library);
        $pager = $this->get('datatheke.pager')->createHttpPager($adapter, array('item_count_per_page' => 20));
        $pager->getHandler()->setOption('current_page_number_param', 'page');

        $view = $pager->handleRequest($request);
        $context = SerializationContext::create()->setAttribute('gloomy_compatibility', true);

        return $this->handleView($this->view($view, 200)->setSerializationContext($context));
    }

    /**
     * @Method({"GET"})
     *
     * @Route("/collections/{id}.{_format}", name="api_collection_get")
     * @Route("/collection/{id}.{_format}")
     *
     * @ParamConverter("collection", options={"param"="id"})
     */
    public function getAction($version, Collection $collection)
    {
        return $this->handleView($this->view($collection, 200));
    }

    /**
     * @Method({"DELETE"})
     *
     * @Route("/collections/{id}.{_format}")
     * @Route("/collection/{id}.{_format}")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_ADMIN", "param"="id"})
     */
    public function deleteAction($version, Collection $collection)
    {
        $this->get('datatheke.manager.collection')->delete($collection);

        return new Response('', 204);
    }

    /**
     * @Method({"POST"})
     *
     * @Route("/libraries/{id}.{_format}")
     * @Route("/library/{id}.{_format}")
     *
     * @ParamConverter("library", options={"right"="LIBRARY_ADMIN", "param"="id"})
     */
    public function postAction(Request $request, $version, Library $library)
    {
        $collection = new Collection();
        $collection->setOwner($this->getUser());
        $collection->setLibrary($library);

        return $this->processForm($request, $version, $collection);
    }

    /**
     * @Method({"PUT"})
     *
     * @Route("/collections/{id}.{_format}")
     * @Route("/collection/{id}.{_format}")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_ADMIN", "param"="id"})
     */
    public function putAction(Request $request, $version, Collection $collection)
    {
        return $this->processForm($request, $version, $collection);
    }

    /**
     *
     */
    protected function processForm(Request $request, $version, Collection $collection)
    {
        $view = '';
        $statusCode = 200;
        $headers = array();

        $name = 'v1' === $version ? 'collection' : '';

        $form = $this->get('form.factory')->createNamed($name, 'datatheke_collection', $collection, array(
            'library' => $collection->getLibrary(),
            'method' => $request->getMethod(),
            'csrf_protection' => false,
            'api' => true
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $statusCode = $collection->getId() ? 204 : 201;
            $this->get('datatheke.manager.collection')->save($collection);

            if (201 === $statusCode) {
                $view = array('id' => $collection->getId());
                $headers['Location'] = $this->generateUrl('api_collection_get', array('id' => $collection->getId(), 'version' => $version), true);
            }
        } else {
            $view = $form;
        }

        return $this->handleView($this->view($view, 200));
    }
}
