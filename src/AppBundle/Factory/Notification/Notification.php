<?php

namespace AppBundle\Factory\Notification;

use AppBundle\Entity\AbstractUniqueContent;
use UserBundle\Entity\User;

interface Notification
{
    public function __construct();
    public function setContent(AbstractUniqueContent $content);
    public function setSender(User $user);
    public function setReceiver(User $user);
    public function translate($translator);
    public function getNotificationEntity();
}
