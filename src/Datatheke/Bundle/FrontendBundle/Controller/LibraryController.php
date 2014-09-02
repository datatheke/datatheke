<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Datatheke\Bundle\CoreBundle\Document\User;
use Datatheke\Bundle\CoreBundle\Document\Library;

class LibraryController extends BaseController
{
    /**
     * @Route("/{username}/add", name="library_add")
     *
     * @ParamConverter("account", options={"right"="ACCOUNT_ADMIN", "param"="username"})
     *
     * @Template("DatathekeFrontendBundle:Library:form.html.twig")
     */
    public function addAction(User $account)
    {
        $library = new Library();
        $library->setOwner($account);

        return $this->processForm($library);
    }

    /**
     * @Route("/libraries/{id}/edit", name="library_edit")
     * @Route("/library/{id}/edit")
     *
     * @ParamConverter("library", options={"right"="LIBRARY_ADMIN", "param"="id"})
     *
     * @Template("DatathekeFrontendBundle:Library:form.html.twig")
     */
    public function editAction(Library $library)
    {
        return $this->processForm($library);
    }

    /**
     *
     */
    protected function processForm(Library $library)
    {
        $action = $library->getId() ? 'edit' : 'create';

        $form = $this->get('form.factory')->create('datatheke_library', $library);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $this->get('datatheke.manager.library')->save($library);
            $this->get('session')->getFlashBag()->add('library_operation', $action);

            return $this->redirect($this->generateUrl('library', array('id' => $library->getId())));
        }

        return array(
            'form'    => $form->createView(),
            'library' => $library,
            'action'  => $action
        );
    }

    /**
     * @Route("/libraries/{id}/delete", name="library_delete")
     * @Route("/library/{id}/delete")
     *
     * @ParamConverter("library", options={"right"="LIBRARY_ADMIN", "param"="id"})
     */
    public function deleteAction(Library $library)
    {
        $this->get('datatheke.manager.library')->delete($library);
        $this->get('session')->getFlashBag()->add('library_operation', 'delete');

        return $this->redirect($this->generateUrl('account', array('username' => $library->getOwner()->getUsername())));
    }
}
