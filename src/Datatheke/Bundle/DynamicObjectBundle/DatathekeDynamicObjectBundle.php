<?php

namespace Datatheke\Bundle\DynamicObjectBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DatathekeDynamicObjectBundle extends Bundle
{
    protected $classLoader;

    public function boot()
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $cacheDir = $this->container->getParameter('kernel.cache_dir');

        /**
         * Register our own MappingDriver to the document_manager DriverChain
         */
        $virtual = new Doctrine\MappingDriver\DynamicDocument($dm);
        $driverChain = $dm->getConfiguration()->getMetadataDriverImpl();
        $driverChain->addDriver($virtual, 'Datatheke\Bundle\DynamicObjectBundle\Document');

        /**
         * Register our own document autoloader
         */
        $this->classLoader = new ClassLoader\DynamicDocument($dm, $cacheDir);
        spl_autoload_register(array($this->classLoader, 'classLoader'));
    }
}
