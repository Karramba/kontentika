<?php

namespace AppBundle\Factory\Notification;

use AppBundle\Entity\AbstractUniqueContent;
use AppBundle\Entity\Notification as NotificationEntity;
use UserBundle\Entity\User;

abstract class AbstractNotification
{
    private $notificationEntity;
    protected $contentType;
    protected $content;
    protected $message;
    protected $receiver;
    protected $sender;

    public function __construct()
    {
        $this->notificationEntity = new NotificationEntity();
    }

    public function setContent(AbstractUniqueContent $content)
    {
        $this->content = $content;
        $this->contentType = (new \ReflectionClass($content))->getShortName();
        $this->notificationEntity->setContentType($this->contentType);
        $this->notificationEntity->setContentUniqueId($content->getUniqueId());
    }

    public function setSender(User $user)
    {
        $this->sender = $user;
    }

    public function setReceiver(User $user)
    {
        $this->notificationEntity->setUser($user);
        $this->receiver = $user;
    }

    public function translate($translator)
    {
        $this->message = str_replace("%contentType%", strtolower($this->contentType), $this->message);

        $translatorParams = array(
            '%sender%' => $this->sender->getUsername(),
        );

        if (method_exists($this->content, 'getTitle')) {
            $translatorParams['%contentTitle%'] = substr($this->content->getTitle(), 0, 50) . "...";
        }
        if (method_exists($this->content, 'getContent')) {
            $translatorParams['%content%'] = substr($this->content->getContent(), 0, 50) . "...";
        }
        $message = $translator->trans($this->message, $translatorParams);
        $this->notificationEntity->setMessage($message);
    }

    public function getNotificationEntity()
    {
        return $this->notificationEntity;
    }

}
