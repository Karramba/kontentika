<?php

namespace AppBundle\Service;

use AppBundle\Entity\Notification;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationService
{
    private $em;
    private $translator;
    private $user;

    public function __construct(EntityManager $em, $translator, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->translator = $translator;

        $this->tokenStorage = $tokenStorage;
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function addReplyNotification($content, $message, array $params = array())
    {
        $notification = new Notification();
        $notification->setUser($content->getUser());
        $notification->setContentType((new \ReflectionClass($content))->getShortName());
        $notification->setContentUniqueId($content->getUniqueId());

        $message = $this->translator->trans("notification." . $message, array_merge($params, array('user' => $this->user)));
        $notification->setMessage($this->user->getUsername() . " " . $message);

        $this->em->persist($notification);
        $this->em->flush();
    }
}
