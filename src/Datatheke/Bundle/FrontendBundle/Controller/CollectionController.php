<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Datatheke\Bundle\CoreBundle\Document\Collection;
use Datatheke\Bundle\CoreBundle\Document\Library;

class CollectionController extends BaseController
{
    /**
     * @Route("/libraries/{id}", name="library")
     * @Route("/library/{id}")
     *
     * @Route("/libraries/{id}/collections", name="library_collections")
     * @Route("/library/{id}/collections")
     *
     * @ParamConverter("library", options={"param"="id"})
     *
     * @Template()
     */
    public function collectionsAction(Library $library)
    {
        // Redirect to the first collection if we have one
        $collections = $this->get('datatheke.manager.collection')->getAdapter($library)->getItems(0, 1);
        if ($collections->hasNext()) {
            return $this->forward('DatathekeFrontendBundle:Collection:collection', array('id' => $collections->getNext()->getId()));
        }

        return array(
            'library' => $library
        );
    }

    /**
     * @Route("/collections/{id}", name="collection")
     * @Route("/collection/{id}")
     *
     * @ParamConverter("collection", options={"param"="id"})
     *
     * @Template()
     */
    public function collectionAction(Request $request, Collection $collection)
    {
        $datagrid = $this->get('datatheke.manager.item')->getDataGrid($collection);

        $view = $datagrid->handleRequest($request)
            ->setRoute('collection')
            ->setParameters(array(
                'id' => $collection->getId()
            ));

        return array(
            'collection' => $collection,
            'datagrid'   => $view
        );
    }

    /**
     * @Route("/libraries/{id}/add", name="collection_add")
     * @Route("/library/{id}/add")
     *
     * @ParamConverter("library", options={"right"="LIBRARY_ADMIN", "param"="id"})
     *
     * @Template("DatathekeFrontendBundle:Collection:form.html.twig")
     */
    public function addAction(Library $library)
    {
        $collection = new Collection();
        $collection->setLibrary($library);
        $collection->setOwner($library->getOwner());

        return $this->processForm($collection);
    }

    /**
     * @Route("/collections/{id}/edit", name="collection_edit")
     * @Route("/collection/{id}/edit")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_ADMIN", "param"="id"})
     *
     * @Template("DatathekeFrontendBundle:Collection:form.html.twig")
     */
    public function editAction(Collection $collection)
    {
        return $this->processForm($collection);
    }

    /**
     *
     */
    protected function processForm(Collection $collection)
    {
        $action = $collection->getId() ? 'edit' : 'create';

        $form = $this->get('form.factory')->create('datatheke_collection', $collection, array('library' => $collection->getLibrary()));
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $this->get('datatheke.manager.collection')->save($collection);

            $this->get('session')->getFlashBag()->add('collection_operation', $action);

            return $this->redirect($this->generateUrl('collection', array('id' => $collection->getId())));
        }

        return array(
            'form'               => $form->createView(),
            'collection'         => $collection,
            'action'             => $action,
            'libraryCollections' => $this->get('datatheke.manager.collection')->getLibraryCollections($collection->getLibrary())
        );
    }

    /**
     * @Route("/collections/{id}/delete", name="collection_delete")
     * @Route("/collection/{id}/delete")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_ADMIN", "param"="id"})
     */
    public function deleteAction(Collection $collection)
    {
        $this->get('datatheke.manager.collection')->delete($collection);
        $this->get('session')->getFlashBag()->add('collection_operation', 'delete');

        return $this->redirect($this->generateUrl('library', array('id' => $collection->getLibrary()->getId())));
    }
}
