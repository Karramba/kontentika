<?php

namespace AppBundle\Factory\Notification;

class DeleteNotification extends AbstractNotification implements Notification
{
    protected $message = "notification.%sender%_deleted_your_%contentType%_%contentTitle%";
}
