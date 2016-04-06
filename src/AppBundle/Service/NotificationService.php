<?php

namespace AppBundle\Service;

use AppBundle\Entity\Notification;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Notifications service
 *
 * @TODO - notifications builder
 */
class NotificationService
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var mixed
     */
    private $translator;
    /**
     * @var User
     */
    private $user;

    /**
     * @param EntityManager $em
     * @param $translator
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManager $em, $translator, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->translator = $translator;

        $this->tokenStorage = $tokenStorage;
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    /**
     * Creates notification for comment/entry reply
     *
     * @param $content
     * @param $message
     * @param array $params
     */
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
