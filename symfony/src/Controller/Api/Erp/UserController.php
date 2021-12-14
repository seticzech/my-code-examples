<?php

namespace App\Controller\Api\Erp;

use App\Controller\Api\ControllerAbstract;
use App\Entity\Erp\OAuth2\Client;
use App\Entity\Erp\User;
use App\Entity\Erp\UserGroup;
use App\Exception\BadRequestException;
use App\Exception\ConflictedException;
use App\Exception\NotFoundException;
use App\Model\Erp\Core\User\UserRegister;
use App\Repository\Erp\OAuth2\ClientRepository;
use App\Repository\Erp\Acl\RoleRepository;
use App\Repository\Erp\UserGroupRepository;
use App\Repository\Erp\UserRepository;
use App\Service\Erp\Acl\ActionService;
use App\Service\Erp\Acl\ResourceService;
use App\Service\Erp\Acl\RoleService;
use Nelmio\ApiDocBundle\Annotation as OA;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class UserController extends ControllerAbstract
{
    
    const ACL_RESOURCE_NAME = ResourceService::CODE_USER;
    const ACL_ACTION_UPDATE_ROLE = ActionService::CODE_USER_UPDATE_ROLE;

    /**
     * @var ClientRepository
     */
    protected $clientRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var UserGroupRepository
     */
    protected $userGroupRepository;


    public function __construct(
        SerializerInterface $serializer,
        ClientRepository $clientRepository,
        UserRepository $userRepository,
        UserGroupRepository $userGroupRepository
    )
    {
        parent::__construct($serializer);

        $this->clientRepository = $clientRepository;
        $this->userRepository = $userRepository;
        $this->userGroupRepository = $userGroupRepository;
    }
    
     /**
     * @Route(
     *     "/api/register",
     *     name="api_user_register_post",
     *     methods={"POST"},
     *     format="json"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Core_User~~register")
     * )
     * @OA\Operation(
     *     summary="User registration"
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Registered user",
     *     @SWG\Schema(ref="#/definitions/Core_User")
     * )
     * @SWG\Response(
     *     response=409,
     *     description="Email already exists"
     * )
     * @SWG\Tag(name="Core")
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param RoleService $roleService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function registerUserAction(
        Request $request,
        ValidatorInterface $validator,
        RoleService $roleService
    ) {
        /** @var Client $client */
        $this->authenticateClient($request, $this->clientRepository);
        $params = $this->getRequestHandler()->getBody($request);

        /** @var UserRegister $post */
        $post = $this->handlePost($request, UserRegister::class);

        $user = $this->userRepository->findOneByEmailAndTenantId($post->username, $this->getTenantId());
        if ($user) {
            throw new ConflictedException('Email address already exists.');
        }

        /** @var User $user */
        $user = $this->handlePost($request, User::class);
        $user->setEmail($post->username);

        $user->setTenantId($this->getTenantId());
        
        $defaultRoles = $roleService->findDefaultRoles();
        foreach ($defaultRoles as $defaultRole) {
            $user->addUserRole($defaultRole);
        }

        if ($user->getCompany()) {
            $user
                ->setIsCompany(true)
                ->getCompany()->setTenantId($this->getTenantId());
        }

        $this->handleValidationViolations($validator->validate($user));

        $this
            ->userRepository
            ->setUserPassword($user, $params['password'])
            ->save($user);

        return $this->handleResponse($user, 201);
    }

    /**
     * @Route(
     *     "/api/me",
     *     name="api_user_me_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Returns logged user"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Logged user",
     *     @SWG\Schema(ref="#/definitions/Core_User")
     * )
     * @SWG\Tag(name="Core")
     *
     * @return Response
     */
    public function getMeAction()
    {
        return $this->handleResponse($this->getUser());
    }

    /**
     * @Route(
     *     "/api/users/{userId}",
     *     name="api_user_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Returns user by ID"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="User by ID",
     *     @SWG\Schema(ref="#/definitions/Core_User")
     * )
     * @SWG\Tag(name="Core")
     *
     * @param Request $request
     * @param string $userId
     *
     * @return Response
     */
    public function getUserAction(Request $request, string $userId)
    {
        $entity = $this->findUserById($userId);

        return $this->handleResponse($entity);
    }
    
    protected function findUserById(string $userId): User {
        $user = $this->userRepository->findOneById($userId);

        if (!$user) {
            throw new NotFoundException('User not found.');
        }
        
        return $user;
    }

    /**
     * @Route(
     *     "/api/users",
     *     name="api_users_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Returns all users"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Users",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Core_User")
     *     )
     * )
     * @SWG\Tag(name="Core")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getUsersAction(Request $request)
    {
        $filters = $request->query->all();
        $data = $this->userRepository->findAllFiltered($filters);

        return $this->handleResponse($data);
    }

    /**
     * @Route(
     *     "/api/users/{userId}",
     *     name="api_user_patch",
     *     methods={"PATCH"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Update user"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Updated user",
     *     @SWG\Schema(ref="#/definitions/Core_User")
     * )
     * @SWG\Tag(name="Core")
     *
     * @param Request $request
     * @param string $userId
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchUserAction(Request $request, string $userId, RoleRepository $roleRepository)
    {
        $user = $this->findUserById($userId);

        $keys = ['firstName', 'lastName', 'phone', 'userGroups'];
        
        if ($this->isAllowed(self::ACL_ACTION_UPDATE_ROLE, $user)) {
            $keys[] = 'userRoles';
        }
        
        $data = $this->getRequestHandler()->getBody($request, $keys, false);
        
        $this->handlePatch($data, $user);
        
        if (!$user->hasDefaultUserRole()) {
            $user->addUserRole($roleRepository->findUserRole());
        }
        
        $this->userRepository->saveAll();

        return $this->handleResponse($user);
    }

    /**
     * @Route(
     *     "/api/users/{userId}/users-groups/{userGroupId}",
     *     name="api_user_group_add_user",
     *     methods={"POST"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Add user to user group"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Updated user",
     *     @SWG\Schema(ref="#/definitions/Core_User")
     * )
     * @SWG\Response(
     *     response=404,
     *     description="User group or user not found"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="User already in group"
     * )
     * @SWG\Tag(name="Core")
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function addUserToUserGroupAction(
        Request $request, 
        string $userId,
        string $userGroupId
    ) {
        /** @var UserGroup $userGroup */
        $userGroup = $this->findUserGroupById($userGroupId);
        $user = $this->findUserById($userId);
        
        if ($userGroup->hasUser($user)) {
            throw new BadRequestException("User already in group");
        }
        
        $userGroup->addUser($user);
        
        $this->userGroupRepository->save($userGroup);
        
        $this->setSerializerGroups(['userGroups']);
        
        return $this->handleResponse($user);
    }
    
    protected function findUserGroupById(string $userGroupId): UserGroup {
        $userGroup = $this->userGroupRepository->findOneById($userGroupId);
        
        if (!$userGroup) {
            throw new NotFoundException('User group not found.');
        }
        
        return $userGroup;
    }
    
    /**
     * @Route(
     *     "/api/users/{userId}/users-groups/{userGroupId}",
     *     name="api_user_group_remove_user",
     *     methods={"DELETE"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Remove user from user group"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Updated user with user groups",
     *     @SWG\Schema(ref="#/definitions/Core_User")
     * )
     * @SWG\Response(
     *     response=404,
     *     description="User group or user not found"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="User not in group"
     * )
     * @SWG\Tag(name="Core")
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function removeUserFromUserGroupAction(
        Request $request, 
        string $userGroupId,
        string $userId
    ) {
        /** @var UserGroup $userGroup */
        $userGroup = $this->findUserGroupById($userGroupId);
        $user = $this->findUserById($userId);
        
        if (!$userGroup->hasUser($user)) {
            throw new BadRequestException("User not in group");
        }
        
        $userGroup->removeUser($user);
        
        $this->userGroupRepository->save($userGroup);
        
        $this->setSerializerGroups(['userGroups']);
        
        return $this->handleResponse($user);
    }
}
