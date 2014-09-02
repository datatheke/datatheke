<?php

namespace Datatheke\Bundle\CoreBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CollectionWriterVoter implements VoterInterface
{
    const SUPPORTED_CLASS = 'Datatheke\\Bundle\\CoreBundle\\Document\\Collection';

    protected $voter;

    public function __construct(LibraryWriterVoter $voter)
    {
        $this->voter = $voter;
    }

    public function supportsAttribute($attribute)
    {
        return 'COLLECTION_WRITER' === strtoupper($attribute);
    }

    public function supportsClass($class)
    {
        if (self::SUPPORTED_CLASS === $class) {
            return true;
        }

        if (class_exists($class)) {
            $r = new \ReflectionClass($class);

            return $r->isSubclassOf(self::SUPPORTED_CLASS);
        }

        return false;
    }

    public function vote(TokenInterface $token, $collection, array $attributes)
    {
        $vote = self::ACCESS_ABSTAIN;

        if (!$this->supportsClass(get_class($collection))) {
            return $vote;
        }

        foreach ($attributes as $attribute) {

            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $vote = self::ACCESS_DENIED;

            if (self::ACCESS_GRANTED === $this->voter->vote($token, $collection->getLibrary(), array('LIBRARY_WRITER'))) {
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }
}
