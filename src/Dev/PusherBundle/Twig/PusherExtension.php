<?php

namespace Dev\PusherBundle\Twig;

use Dev\PusherBundle\Service\PusherService;

class PusherExtension extends \Twig_Extension
{
    private $pusherService;

    public function __construct(PusherService $pusherService)
    {
        $this->pusherService = $pusherService;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('pusherJsSettings', array($this, 'getPusherJsSettings'), array()),
        );
    }

    public function getPusherJsSettings()
    {
        return $this->pusherService->getJsSettings();
    }

    public function getName()
    {
        return 'dev_pusher_extension';
    }
}
