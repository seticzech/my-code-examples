<?php

namespace App\Service\EventListener\Entity;

use App\Contract\Entity\ContainerAwareInterface;
use App\Service\EventListener\ListenerAbstract;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class PostLoadEntityListener extends ListenerAbstract
{

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof ContainerAwareInterface) {
            $entity->setContainer($this->container);
        }
    }

}
