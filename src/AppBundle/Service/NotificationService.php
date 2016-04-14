<?php

namespace AppBundle\Service;

use AppBundle\Entity\Notification;
use Dev\PusherBundle\Service\PusherService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UserBundle\Service\UserService;

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
    private $pusherService;

    private $userService;
    private $repliedTo;

    /**
     * @param EntityManager $em
     * @param $translator
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManager $em,
        $translator,
        TokenStorageInterface $tokenStorage,
        UserService $userService,
        PusherService $pusherService
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->userService = $userService;
        $this->pusherService = $pusherService;

        $this->tokenStorage = $tokenStorage;
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    private function buildNotification($user, $content, $message, array $params = array())
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setContentType((new \ReflectionClass($content))->getShortName());
        $notification->setContentUniqueId($content->getUniqueId());

        $message = $this->translator->trans("notification." . $message, array_merge($params, array('user' => $this->user)));
        $notification->setMessage($this->user->getUsername() . " " . $message);

        return $notification;
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
        $notification = $this->buildNotification($content->getUser(), $content, $message, $params);
        $this->repliedTo = $content->getUser();

        $this->em->persist($notification);
        $this->em->flush();

        $unreadNotifications = $this->em
            ->getRepository("AppBundle:Notification")
            ->findUserUnreadNotifications($notification->getUser());

        $this->pusherService->notify('notification', $notification->getUser()->getId(), count($unreadNotifications));
    }

    public function addMentionNotification($content, $message, array $params = array())
    {
        $mentions = $this->userService->findMentions($content->getContent());
        $users = $this->em->getRepository("UserBundle:User")->findByUsername($mentions);

        foreach ($users as $user) {
            /**
             * We have to check that current user didn't mention himself.
             * Content owner is mentioned by addReplyNotification.
             */
            if ($user != $this->user && $user != $content->getUser() && $user != $this->repliedTo) {
                $notification = $this->buildNotification($user, $content, $message, $params);
                $this->em->persist($notification);

                $unreadNotifications = $this->em
                    ->getRepository("AppBundle:Notification")
                    ->findUserUnreadNotifications($notification->getUser());
                $this->pusherService->notify('notification', $notification->getUser()->getId(), count($unreadNotifications));
            }
        }
        $this->em->flush();
    }
}
