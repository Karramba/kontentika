<?php

namespace Dev\PusherBundle\Service;

use Pusher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UserBundle\Entity\User;

class PusherService
{
    private $pusher;
    private $user;
    private $parameters;
    private $channel;
    private $socketId;

    public function __construct(array $parameters = array(), TokenStorageInterface $tokenStorage)
    {
        $this->parameters = $parameters;

        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
            $this->channel = $parameters['channel'];

            $this->pusher = new Pusher(
                $parameters['app_key'],
                $parameters['secret'],
                $parameters['app_id'],
                $parameters['options']
            );
        }
    }

    public function getJsSettings()
    {
        $settings = array(
            'app_key' => $this->parameters['app_key'],
            'cluster' => $this->parameters['options']['cluster'],
            'debug' => ($this->parameters['debug'] == true) ? "true" : "false",
        );
        return $settings;
    }

    public function presenceChannelsAuth($socketId)
    {
        $this->socketId = $socketId;

        if ($this->user instanceof User) {
            $this->pusher->presence_auth(
                $this->channel . "-" . $this->user->getId(),
                $socketId,
                $this->user->getId(),
                $this->user->getUsername()
            );
        }
    }

    public function notify($event, $receiverId, $message)
    {
        $this->pusher->trigger($this->channel . "-" . $receiverId, $event, $message, $this->socketId);
    }
}
