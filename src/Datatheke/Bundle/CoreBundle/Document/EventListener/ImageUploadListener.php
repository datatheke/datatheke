<?php

namespace Datatheke\Bundle\CoreBundle\Document\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;

use Datatheke\Bundle\CoreBundle\Document\Library;
use Datatheke\Bundle\CoreBundle\Document\Collection;

class ImageUploadListener implements EventSubscriber
{
    protected $storagePath;

    public function __construct($storagePath)
    {
        $this->storagePath = $storagePath;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->preUpload($args);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->preUpload($args);
    }

    protected function preUpload(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();

        if (!$document instanceof Library && !$document instanceof Collection) {
            return;
        }

        if (null !== $document->getImageUpload()) {
            try {
                list($width, $height, $type, $attr) = getimagesize($document->getImageUpload()->getPathname());
                $orientation = ($width >= $height) ? 0 : 1;
                $document->setImageType($document->getImageUpload()->guessExtension());
                $document->setImageOrientation($orientation);

                if ($document->getId()) {
                    $dm = $args->getDocumentManager();
                    $class = $dm->getClassMetadata(get_class($document));
                    $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $document);
                }
            } catch (\Exception $e) {
                $document->setImageUpload(null);
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->postUpload($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->postUpload($args);
    }

    protected function postUpload(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();

        if ($document instanceof Library) {
            $dir = 'libraries';
        } elseif ($document instanceof Collection) {
            $dir = 'collections';
        } else {
            return;
        }

        if (null !== $document->getImageUpload()) {
            $document->getImageUpload()->move($this->storagePath.'/'.$dir, $document->getid().'.'.$document->getImageUpload()->guessExtension());
            $document->setImageUpload(null);
        }
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate,
        );
    }
}
