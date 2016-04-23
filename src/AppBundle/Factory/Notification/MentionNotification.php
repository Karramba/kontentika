<?php

namespace AppBundle\Factory\Notification;

class MentionNotification extends AbstractNotification implements Notification
{
    protected $message = "notification.%sender%_mentioned_you_in_%contentType%_%content%";
}
