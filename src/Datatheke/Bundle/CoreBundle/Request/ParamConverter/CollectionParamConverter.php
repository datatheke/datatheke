<?php

namespace Datatheke\Bundle\CoreBundle\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

use Datatheke\Bundle\CoreBundle\Manager\CollectionManager;

class CollectionParamConverter implements ParamConverterInterface
{
    protected $collectionManager;

    public function __construct(CollectionManager $collectionManager)
    {
        $this->collectionManager = $collectionManager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        $right = isset($options['right']) ? $options['right'] : 'COLLECTION_READER';
        $param = isset($options['param']) ? $options['param'] : $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $id = $request->attributes->get($param);
        $collection = $this->collectionManager->find($id, $right);

        $request->attributes->set($configuration->getName(), $collection);

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

        return "Datatheke\Bundle\CoreBundle\Document\Collection" === $configuration->getClass();
    }
}
