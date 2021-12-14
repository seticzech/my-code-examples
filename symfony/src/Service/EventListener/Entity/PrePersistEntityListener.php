<?php

namespace App\Service\EventListener\Entity;

use App\Contract\Entity\ContainerAwareInterface;
use App\Contract\Entity\TenantAwareInterface;
use App\Contract\Entity\UserAwareInterface;
use App\Entity\Erp\User;
use App\Entity\IdentifiedAbstract;
use App\Service\EventListener\ListenerAbstract;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Ramsey\Uuid\Uuid;

class PrePersistEntityListener extends ListenerAbstract
{

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        /** @var User $user */
        $user = $this->getAuthenticatedUser();
        
        if (($entity instanceof IdentifiedAbstract) && !$entity->getId()) {
            $entity->setId(Uuid::uuid4());
        }

        if (($entity instanceof UserAwareInterface) && $user) {
            try {
                if (!$entity->getUser()) {
                    $entity->setUser($user);
                }
            } catch (\TypeError $e) {
                $entity->setUser($user);
            }
        }

        if (($entity instanceof TenantAwareInterface) && $user) {
            try {
                if (!$entity->getTenantId()) {
                    $entity->setTenantId($user->getTenantId());
                }
            } catch (\TypeError $e) {
                $entity->setTenantId($user->getTenantId());
            }
        }
        
        if ($entity instanceof ContainerAwareInterface) {
            $entity->setContainer($this->container);
        }
    }
}
