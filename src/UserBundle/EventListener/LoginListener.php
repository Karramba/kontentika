<?php

namespace UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use UserBundle\Entity\LoginHistory;

/**
 * Listener responsible to change the redirection at the end of the password resetting
 */
class LoginListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'onLogin',
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        );
    }

    public function onLogin(InteractiveLoginEvent $args)
    {
        $request = $args->getRequest();
        $user = $args->getAuthenticationToken()->getUser();

        $loginHistory = new LoginHistory();
        $loginHistory->setIp($request->getClientIp());
        $loginHistory->setUserAgent($request->headers->get('user-agent'));

        $user->addLoginHistory($loginHistory);

        // var_dump($entity);exit;

    }
}
