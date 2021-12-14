<?php

namespace App\Controller\Api\Erp\Acl;

use App\Controller\Api\ControllerAbstract;
use App\Entity\Erp\User;
use App\Exception\NotFoundException;
use App\Repository\Erp\Acl\RoleRepository;
use App\Repository\Erp\UserRepository;
use App\Service\Erp\Acl\ActionService;
use App\Service\Erp\Acl\ResourceService;
use Nelmio\ApiDocBundle\Annotation as OA;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RoleController extends ControllerAbstract
{

    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;


    public function __construct(
        SerializerInterface $serializer,
        RoleRepository $roleRepository,
        UserRepository $userRepository
    ) {
        parent::__construct($serializer);

        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
    }
    
    /**
     * @Route(
     *     "/api/acl/roles",
     *     name="api_acl_roles_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Return all roles"
     * )
     * @SWG\Parameter(
     *     name="groups", in="path", type="string",
     *     description="Group names delimited by comma"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned roles",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Acl_Role")
     *     )
     * )
     * @SWG\Tag(name="ACL")
     * 
     * @return Response
     */
    public function getRolesAction()
    {
        $data = $this->roleRepository->findAll();
        
        return $this->handleResponse($data);
    }
    
    /**
     * @Route(
     *     "/api/acl/roles/users/{userId}",
     *     name="api_acl_users_roles_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Return all user roles"
     * )
     * @SWG\Parameter(
     *     name="groups", in="path", type="string",
     *     description="Group names delimited by comma"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned roles",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Acl_Role")
     *     )
     * )
     * @SWG\Tag(name="ACL")
     * 
     * @param string $userId
     * 
     * @return Response
     */
    public function getUsersRolesAction(string $userId)
    {
        $user = $this->getUserById($userId);
        
        return $this->handleResponse($user->getUserRoles());
    }
    
    protected function getUserById(string $userId): User {
        $user = $this->userRepository->findOneById($userId);
        
        if (!$user) {
            throw new NotFoundException('User not found.');
        }
        
        return $user;
    }
    
    /**
     * @Route(
     *     "/api/acl/roles/users/{userId}",
     *     name="api_acl_users_roles_patch",
     *     methods={"PATCH"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Update user roles"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Acl_UserRoles~patch")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="User roles updated",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Acl_Role")
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="User not found."
     * )
     * @SWG\Tag(name="ACL")
     * 
     * @param Request $request
     * @param string $userId
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function patchUsersRolesAction(Request $request, string $userId) 
    {
        $user = $this->getUserById($userId);
        
        $this->checkResourcePermission(ResourceService::CODE_USER, ActionService::CODE_USER_UPDATE_ROLE, $user);

        $data = $this->getRequestHandler()->getBody($request, ['userRoles']);
        
        $this->handlePatch($data, $user);
        
        if (!$user->hasDefaultUserRole()) {
            $user->addUserRole($this->roleRepository->findUserRole());
        }
        
        $this->userRepository->save($user);
        
        return $this->handleResponse($user->getUserRoles()->getValues());
    }
    
}
