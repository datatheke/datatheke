<?php

namespace Datatheke\Bundle\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Datatheke\Bundle\CoreBundle\Manager\UserManager;

class UsernameToUserTransformer implements DataTransformerInterface
{
    protected $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function transform($user)
    {
        return $user;
    }

    public function reverseTransform($username)
    {
        if (null === $username) {
            return null;
        }

        $user = $this->userManager->find($username);
        if (null === $user) {
            throw new TransformationFailedException(sprintf(
                'The user "%s" does not exist',
                $id
            ));
        }

        return $user;
    }
}
