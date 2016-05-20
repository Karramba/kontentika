<?php

namespace AppBundle\Factory\Notification;

class ReplyNotification extends AbstractNotification implements Notification
{
    protected $message = "notification.%sender%_replied_to_your_%contentType%_%content%";
}
