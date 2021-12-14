<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\Permission;
use Doctrine\ORM\EntityManager;


class PermissionRepository extends Repository
{

    /**
     * @var PermissionGroupRepository
     */
    protected $groupRepository;
    
    
    public function __construct(EntityManager $em, PermissionGroupRepository $groupRepository)
    {
        $this->em = $em;
        $this->er = $em->getRepository(Permission::class);
        $this->groupRepository = $groupRepository;
    }


    public function getTree()
    {
        $groups = $this->groupRepository->findAllParents();

        $result = [];

        foreach ($groups as $group) {
            $result[] = $this->formatTreeGroup($group);
        }

        return $result;
    }


    private function formatTreeGroup(Permission\Group $group)
    {
        $result = [
            'id' => $group->getId(),
            'children' => [],
            'permissions' => $group->getPermissions()->toArray(),
            'translations' => $group->getTranslations()->toArray(),
        ];

        foreach ($group->getChildren() as $child) {
            $result['children'][] = $this->formatTreeGroup($child);
        }

        return $result;
    }

}
