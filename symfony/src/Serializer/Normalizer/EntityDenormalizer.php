<?php

namespace App\Serializer\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class EntityDenormalizer implements DenormalizerInterface
{

    protected $entityManager;

    protected $objectNormalizer;


    public function __construct(
        EntityManagerInterface $entityManager,
        ObjectNormalizer $objectNormalizer
    ) {
        $this->entityManager = $entityManager;
        $this->objectNormalizer = $objectNormalizer;
    }

    public function supportsDenormalization($data, $type, string $format = null)
    {
        return (strpos($type, 'App\Entity\\') === 0) &&
            (is_string($data) || (is_array($data) && isset($data['id'])));
    }

    public function denormalize($data, $class, string $format = null, array $context = [])
    {
        if (is_array($data) && isset($data['id'])) {
            $entity = $this->entityManager->find($class, $data['id']);

            unset($data['id']);

            if (!empty($data)) {
                $context = array_merge($context, [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $entity]);

                return $this->objectNormalizer->denormalize($data, get_class($entity), $format, $context);
            }

            return $entity;
        }

        return $this->entityManager->find($class, $data);
    }

}
