<?php

namespace Datatheke\Bundle\FrontendBundle\Decorator;

use Symfony\Component\HttpFoundation\RequestStack;

use Gloomy\TwigDecoratorBundle\Decorator\DecoratorInterface;

class CollectionDecorator implements DecoratorInterface
{
    protected $requestStack;
    protected $collectionManager;

    public function __construct(RequestStack $requestStack, $collectionManager)
    {
        $this->requestStack = $requestStack;
        $this->collectionManager = $collectionManager;
    }

    public function getTemplate(array $variables)
    {
        return $this->needDecoration() ? 'DatathekeFrontendBundle:Collection:collections.html.twig' : null;
    }

    public function getVariables(array $variables)
    {
        $layout = array();

        if ($this->needDecoration()) {
            $collection = $variables['collection'];
            $library    = $collection->getLibrary();

            $layout     = array(
                'library'          => $library,
                'collections'      => $this->collectionManager->getAdapter($library)->getItems(0, 100),
                'activeCollection' => $collection
            );
        }

        return array_merge($variables, $layout);
    }

    protected function needDecoration()
    {
        return !$this->requestStack->getCurrentRequest()->isXmlHttpRequest();
    }
}
