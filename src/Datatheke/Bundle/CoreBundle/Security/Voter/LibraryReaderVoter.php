<?php

namespace Datatheke\Bundle\CoreBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LibraryReaderVoter implements VoterInterface
{
    const SUPPORTED_CLASS = 'Datatheke\\Bundle\\CoreBundle\\Document\\Library';

    public function supportsAttribute($attribute)
    {
        return 'LIBRARY_READER' === strtoupper($attribute);
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

    public function vote(TokenInterface $token, $library, array $attributes)
    {
        $vote = self::ACCESS_ABSTAIN;

        if (!$this->supportsClass(get_class($library))) {
            return $vote;
        }

        foreach ($attributes as $attribute) {

            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $vote = self::ACCESS_DENIED;

            if ($library->isPublic()) {
                return self::ACCESS_GRANTED;
            }

            // User must be logged
            $user = $token->getUser();
            if (!$user instanceof UserInterface) {
                continue;
            }

            if ($library->isCollaborative()) {
                return self::ACCESS_GRANTED;
            }

            if ($library->getOwner() === $user) {
                return self::ACCESS_GRANTED;
            }

            foreach ($library->getShares() as $share) {
                if ($share->getUser() === $user) {
                    return self::ACCESS_GRANTED;
                }
            }
        }

        return $vote;
    }
}
