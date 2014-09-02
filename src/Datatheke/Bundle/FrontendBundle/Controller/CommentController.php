<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Datatheke\Bundle\CoreBundle\Document\Comment;
use Datatheke\Bundle\CoreBundle\Document\Library;

class CommentController extends BaseController
{
    /**
     * @Route("/libraries/{id}/comments", name="library_comments")
     * @Route("/library/{id}/comments")
     *
     * @ParamConverter("library", options={"param"="id"})
     *
     * @Template()
     */
    public function commentsAction(Request $request, Library $library)
    {
        $form = null;
        if ($this->getUser()) {

            $comment = new Comment();
            $comment->setOwner($this->getUser());
            $comment->setLibrary($library);

            $form    = $this->get('form.factory')->create('datatheke_comment', $comment);

            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->get('datatheke.manager.comment')->save($comment);
                $this->get('session')->getFlashBag()->add('library_operation', 'add');

                return $this->redirect($this->generateUrl('library_comments', array('id' => $library->getId())));
            }

            $form = $form->createView();
        }

        $adapter  = $this->get('datatheke.manager.comment')->getAdapter($library);
        $pager    = $this->get('datatheke.pager')->createHttpPager($adapter);
        $view     = $pager->handleRequest($request)
            ->setRoute('library_comments')
            ->setParameters(array(
                'id' => $library->getId()
            ))
        ;

        return array(
            // Required by the extended Layout
            'library' => $library,

            // Section template
            'pager'   => $view,
            'form'    => $form
        );
    }
}
