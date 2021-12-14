<?php

namespace App\Facade;

use App\Entity\Proposition as PropositionEntity;
use Nettrine\ORM\EntityManager;


class Proposition
{

    /**
     * @var EntityManager
     */
    protected $em;



    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }



    public function create(array $data): PropositionEntity
    {
        $entity = new PropositionEntity($data['name']);

        $entity->setValues($data);

        $this->em->persist($entity);
        $this->em->flush($entity);

        return $entity;
    }



    public function getByProjectId($projectId)
    {
        return $this->em->getRepository(PropositionEntity::class)->findBy([
            'projectId' => $projectId,
            'deleted' => 0,
        ]);
    }



    /**
     * @param int $id
     * @return PropositionEntity|null
     */
    public function getProposition(int $id)
    {
        return $this->em->getRepository(PropositionEntity::class)->findOneBy([
            'id' => $id,
            'deleted' => 0,
        ]);
    }



    public function update(array $data): PropositionEntity
    {
        $entity = $this->getProposition($data['id']);
        unset($data['id']);

        $entity->setValues($data);
        $entity->setModifiedAt();

        $this->em->flush($entity);

        return $entity;
    }



    public function remove(PropositionEntity $proposition): PropositionEntity
    {
        $proposition->setDeleted(true);
        $this->em->flush($proposition);

        return $proposition;
    }

}