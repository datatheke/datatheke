<?php

namespace Datatheke\Bundle\CoreBundle\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

use Datatheke\Bundle\CoreBundle\Manager\LibraryManager;

class LibraryParamConverter implements ParamConverterInterface
{
    protected $libraryManager;

    public function __construct(LibraryManager $libraryManager)
    {
        $this->libraryManager = $libraryManager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        $right = isset($options['right']) ? $options['right'] : 'LIBRARY_READER';
        $param = isset($options['param']) ? $options['param'] : $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $id = $request->attributes->get($param);
        $library = $this->libraryManager->find($id, $right);

        $request->attributes->set($configuration->getName(), $library);

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

        return "Datatheke\Bundle\CoreBundle\Document\Library" === $configuration->getClass();
    }
}
