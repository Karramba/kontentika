<?php

namespace AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;

class PersistListener implements EventSubscriber
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (method_exists($entity, "setIp")) {
            $entity->setIp($this->requestStack->getCurrentRequest()->getClientIp());
        }
    }
}
