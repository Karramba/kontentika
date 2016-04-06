<?php

namespace AppBundle\Service;

use AppBundle\Entity\LinkGroup;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UserBundle\Entity\User;

/**
 * undocumented class
 *
 * @package default
 * @author
 **/
class AuthService
{
    private $user = null;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function isGroupModerator(LinkGroup $group)
    {
        if ($this->user instanceof User) {
            if ($group->getModerators()->contains($this->user)) {
                return true;
            }
        }

        return false;
    }

    public function isGroupAdmin(LinkGroup $group)
    {
        if ($this->user instanceof User) {
            if ($group->getOwner() == $this->user) {
                return true;
            }
        }
        return false;
    }

    public function haveModerationToolsAccess(LinkGroup $group)
    {
        if ($this->isGroupModerator($group) || $this->isGroupAdmin($group)) {
            return true;
        }
        return false;
    }

} // END class
