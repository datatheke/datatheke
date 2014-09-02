<?php

namespace Datatheke\Bundle\CoreBundle\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

use Datatheke\Bundle\CoreBundle\Manager\UserManager;

class UserParamConverter implements ParamConverterInterface
{
    protected $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        $right = isset($options['right']) ? $options['right'] : null;
        $param = isset($options['param']) ? $options['param'] : $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $id = $request->attributes->get($param);
        $user = $this->userManager->find($id, $right);

        $request->attributes->set($configuration->getName(), $user);

        return true;
    }

    /**
     * @{inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return "Datatheke\Bundle\CoreBundle\Document\User" === $configuration->getClass();
    }
}
