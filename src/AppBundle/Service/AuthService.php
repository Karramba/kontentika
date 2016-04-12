<?php

namespace AppBundle\Service;

use AppBundle\Entity\LinkGroup;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UserBundle\Entity\User;

/**
 * Main moderator/admin authorization class
 */
class AuthService
{
    /**
     * @var User
     */
    private $user = null;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    /**
     * @param LinkGroup $group
     */
    public function isGroupModerator(LinkGroup $group)
    {
        if ($this->user instanceof User) {
            if ($group->getModerators()->contains($this->user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param LinkGroup $group
     */
    public function isGroupAdmin(LinkGroup $group)
    {
        if ($this->user instanceof User) {
            if ($group->getOwner() == $this->user) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param LinkGroup $group
     */
    public function haveModerationToolsAccess(LinkGroup $group)
    {
        if ($this->isGroupModerator($group) || $this->isGroupAdmin($group)) {
            return true;
        }
        return false;
    }

} // END class
