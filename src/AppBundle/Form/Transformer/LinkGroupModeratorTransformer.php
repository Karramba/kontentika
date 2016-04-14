<?php

namespace AppBundle\Form\Transformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms usernames to users (for linkgroup settings)
 */
class LinkGroupModeratorTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($users)
    {
        $usernames = array();
        foreach ($users as $user) {
            $usernames[] = $user->getUsername();
        }
        return $usernames;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($username)
    {
        if (!$username) {
            return array();
        }
        $users = $this->manager->getRepository('UserBundle:User')->findByUsername($username);

        if (null === $users) {
            throw new TransformationFailedException(sprintf('An user with username "%s" does not exist!', $username));
        }
        return $users;
    }
}
