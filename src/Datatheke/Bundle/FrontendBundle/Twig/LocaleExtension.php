<?php

namespace Datatheke\Bundle\FrontendBundle\Twig;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleExtension extends \Twig_Extension
{
    protected $router;
    protected $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return array(
            'locale_switcher' => new \Twig_Function_Method($this, 'localeSwitcher'),
        );
    }

    public function localeSwitcher($locale)
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request->attributes->has('_route')) {
            $route = $request->attributes->get('_route');
            $params = $request->attributes->get('_route_params');
        } else {
            $match = $this->router->match($request->getPathInfo());
            $route = $match['_route'];
            $params = array();
            foreach ($match as $key => $val) {
                if (0 !== strpos($key, '_')) {
                    $params[$key] = $val;
                }
            }
        }

        if ('datatheke_frontend_root' === $route) { // 'datatheke_frontend_root' is not localized, 'index' is
            $route = 'index';
        }
        $params['_locale'] = $locale;

        return $this->router->generate($route, $params);
    }

    public function getName()
    {
        return 'datatheke_locale';
    }
}
