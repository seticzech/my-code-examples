<?php

namespace App\Doctrine\Generators;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Ramsey\Uuid\Nonstandard\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidGenerator extends AbstractIdGenerator
{

    public function generate(EntityManager $em, $entity): UuidInterface
    {
        if (!$entity->getId()) {
            return Uuid::uuid4();
        }
        if (!Uuid::isValid($entity->getId())) {
            return Uuid::uuid4();
        }

        return $entity->getId();
    }
}
