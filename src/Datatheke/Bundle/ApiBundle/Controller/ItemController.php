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

/**
 * @Route("/{version}", defaults={"_format"="json", "version"="v2"}, requirements={"_format"="json|xml", "version"="v1|v2"})
 */
class ItemController extends FOSRestController
{
    /**
     * @Method({"GET"})
     *
     * @Route("/collections/{id}/items.{_format}", requirements={"_format"="json"})
     * @Route("/collection/{id}/items.{_format}", requirements={"_format"="json"})
     *
     * @ParamConverter("collection", options={"param"="id"})
     */
    public function allAction(Request $request, $version, Collection $collection)
    {
        $adapter = $this->get('datatheke.manager.item')->getAdapter($collection);
        $pager = $this->get('datatheke.pager')->createHttpPager($adapter, array('item_count_per_page' => 20));
        $pager->getHandler()->setOption('current_page_number_param', 'page');

        $view = $pager->handleRequest($request);
        $context = SerializationContext::create()->setAttribute('gloomy_compatibility', true);

        return $this->handleView($this->view($view, 200)->setSerializationContext($context));
    }

    /**
     * @Method({"GET"})
     *
     * @Route("/collections/{id}/items/{item}.{_format}", name="api_item_get")
     * @Route("/collection/{id}/item/{item}.{_format}")
     *
     * @ParamConverter("collection", options={"param"="id"})
     */
    public function getAction(Collection $collection, $version, $item)
    {
        $item = $this->get('datatheke.manager.item')->find($collection, $item);

        return $this->handleView($this->view($item, 200));
    }

    /**
     * @Method({"DELETE"})
     *
     * @Route("/collections/{id}/items/{item}.{_format}")
     * @Route("/collection/{id}/item/{item}.{_format}")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_WRITER", "param"="id"})
     */
    public function deleteAction($version, Collection $collection, $item)
    {
        $this->get('datatheke.manager.item')->delete($collection, $item);

        return new Response('', 204);
    }

    /**
     * @Method({"POST"})
     *
     * @Route("/collections/{id}.{_format}")
     * @Route("/collection/{id}.{_format}")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_WRITER", "param"="id"})
     */
    public function postAction(Request $request, $version, Collection $collection)
    {
        $item = $this->get('datatheke.manager.item')->create($collection);

        return $this->processForm($request, $version, $collection, $item);
    }

    /**
     * @Method({"PUT"})
     *
     * @Route("/collections/{id}/items/{item}.{_format}")
     * @Route("/collection/{id}/item/{item}.{_format}")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_WRITER", "param"="id"})
     */
    public function putAction(Request $request, $version, Collection $collection, $item)
    {
        $item = $this->get('datatheke.manager.item')->find($collection, $item);

        return $this->processForm($request, $version, $collection, $item);
    }

    /**
     *
     */
    protected function processForm(Request $request, $version, Collection $collection, $item)
    {
        $view = '';
        $statusCode = 200;
        $headers = array();

        $name = 'v1' === $version ? '_'.$collection->getId() : '';

        $form = $this->get('form.factory')->createNamed($name, 'datatheke_item', $item, array(
            'method' => $request->getMethod(),
            'csrf_protection' => false,
            'collection' => $collection
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $statusCode = $item->getId() ? 204 : 201;
            $this->get('datatheke.manager.item')->save($item);

            if (201 === $statusCode) {
                $view = array('id' => $item->getId());
                $headers['Location'] = $this->generateUrl('api_item_get', array('id' => $collection->getId(), 'item' => $item->getId(), 'version' => $version), true);
            }
        } else {
            $view = $form;
        }

        return $this->handleView($this->view($view, 200));
    }
}
