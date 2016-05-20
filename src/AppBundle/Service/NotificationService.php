<?php

namespace AppBundle\Service;

use AppBundle\Entity\Notification;
use AppBundle\Factory\Notification\DeleteNotification;
use AppBundle\Factory\Notification\MentionNotification;
use AppBundle\Factory\Notification\ReplyNotification;
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

    public function createNotification($notificationType, $content, $receiver)
    {
        try {
            switch ($notificationType) {
                case 'reply':
                    $notification = new ReplyNotification();
                    break;
                case 'delete':
                    $notification = new DeleteNotification();
                    break;
                case 'mention':
                    $notification = new MentionNotification();
                    break;

                default:
                    throw new \Exception("Error Processing Notification", 1);
                    break;
            }

            $notification->setContent($content);
            $notification->setSender($this->user);
            $notification->setReceiver($receiver);
            $notification->translate($this->translator);
            $notificationEntity = $notification->getNotificationEntity();

            return $notificationEntity;
        } catch (\Exception $e) {
            // var_dump($e);exit;
        }

    }

    /**
     * Creates notification for comment/entry reply
     *
     * @param $content
     * @param $message
     * @param array $params
     */
    public function addReplyNotification($content)
    {
        $notification = $this->createNotification("reply", $content, $content->getUser());
        $this->repliedTo = $content->getUser();

        $this->em->persist($notification);
        $this->em->flush();

        $user = $notification->getUser();
        $this->pusherService->notifyUser('notification', $user->getId(), $user->getUnreadNotificationsNumber());
    }

    public function addDeleteNotification($content)
    {
        $notification = $this->createNotification("delete", $content, $content->getUser());

        $this->em->persist($notification);
        $this->em->flush();

        $user = $notification->getUser();
        $this->pusherService->notifyUser('notification', $user->getId(), $user->getUnreadNotificationsNumber());
    }

    public function addMentionNotification($content)
    {
        $mentions = $this->userService->findMentions($content->getContent());
        $users = $this->em->getRepository("UserBundle:User")->findByUsername($mentions);

        foreach ($users as $receiver) {
            /**
             * We have to check that current user didn't mention himself.
             * Content owner is mentioned by addReplyNotification.
             */
            if ($receiver != $this->user && $receiver != $content->getUser() && $receiver != $this->repliedTo) {
                // $notification = $this->buildNotification($user, $content, $message, $params);
                $notification = $this->createNotification("mention", $content, $receiver);
                $this->em->persist($notification);
            }
        }
        $this->em->flush();

        /* We need to send an unread notifications number after doctrine flush */
        foreach ($users as $receiver) {
            if ($receiver != $this->user && $receiver != $content->getUser() && $receiver != $this->repliedTo) {
                $this->pusherService->notifyUser('notification', $receiver->getId(), $receiver->getUnreadNotificationsNumber());
            }
        }
    }
}
