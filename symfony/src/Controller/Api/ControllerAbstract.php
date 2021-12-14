<?php

namespace App\Controller\Api;

use App\Entity\EntityAbstract;
use App\Entity\Erp\OAuth2\Client;
use App\Entity\Erp\User;
use App\Exception\AuthenticationException;
use App\Exception\InsufficientDataException;
use App\Exception\ValidationException;
use App\Model\Erp\ModelAbstract;
use App\Service\Erp\Acl\ActionService;
use App\Service\Erp\Acl\PermissionService;
use App\Service\RequestHandler;
use App\Service\Sanitizer;
use App\Traits\AclCheckPermissionTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

abstract class ControllerAbstract extends AbstractController
{
    
    use AclCheckPermissionTrait;

    const CLIENT_AUTH_MANDATORY_PARAMS = ['client_id', 'client_secret'];

    const DEFAULT_SERIALIZER_CONTEXT = [
        "datetime_format" => "Y-m-d\TH:i:sP",
        "groups" => ["default"],
        AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => false,
    ];
    
    const ACL_RESOURCE_NAME = null;
    
    const ACL_ACTION_CREATE = ActionService::CODE_CREATE;
    const ACL_ACTION_UPDATE = ActionService::CODE_UPDATE;
    const ACL_ACTION_VIEW = ActionService::CODE_VIEW;
    const ACL_ACTION_DELETE = ActionService::CODE_DELETE;

    /**
     * @var string
     */
    private $dataFormat = 'json';

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $serializerContext;
    
    /**
     * @var array
     */
    private $serializerGroups = [];
    
    /**
     * @var array
     */
    protected $disableDefaultGroup = false;


    public function __construct(SerializerInterface $serializer)
    {
        $this->requestHandler = new RequestHandler(new Sanitizer(), $serializer);
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @param ClientRepositoryInterface $clientRepository
     *
     * @return ClientEntityInterface|Client|null
     */
    protected function authenticateClient(Request $request, ClientRepositoryInterface $clientRepository)
    {
        $params = $this->getRequestHandler()->getBody($request, self::CLIENT_AUTH_MANDATORY_PARAMS);

        foreach (self::CLIENT_AUTH_MANDATORY_PARAMS as $param) {
            if (!array_key_exists($param, $params)) {
                throw new InsufficientDataException('Authorization of OAuth2 client failed, missing mandatory parameter.');
            }
        }

        if (!$clientRepository->validateClient($params['client_id'], $params['client_secret'], '')) {
            throw new AuthenticationException('OAuth client authentication failed.');
        }

        return $clientRepository->getClientEntity($params['client_id']);
    }

    protected function getDataFormat(): string
    {
        return $this->getRequestHandler()->getDataFormat();
    }

    protected function getRequestHandler(): RequestHandler
    {
        return $this->requestHandler;
    }

    protected function getTenantId(): ?string
    {
        return $_ENV["tenantId"] ?? null;
    }

    /**
     * @return object|UserInterface|User|null
     */
    protected function getUser()
    {
        return parent::getUser();
    }

    /**
     * @param Request|array $requestData
     * @param EntityAbstract|ModelAbstract $entity
     * @param string|null $format
     * @param array|null $context
     *
     * @return mixed
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    protected function handlePatch(
        $requestData,
        $entity,
        ?string $format = null,
        array $context = []
    ) {
        return $this->getRequestHandler()->handlePatch($requestData, $entity, $format, $context);
    }

    /**
     * @param Request|array $requestData
     * @param string $entityClass
     * @param string|null $format
     * @param array|null $context
     *
     * @return EntityAbstract|ModelAbstract
     *
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    protected function handlePost(
        $requestData,
        string $entityClass,
        ?string $format = null,
        array $context = []
    ) {
        return $this->getRequestHandler()->handlePost($requestData, $entityClass, $format, $context);
    }

    protected function handleQuery(
        $requestData,
        string $entityClass,
        array $context = []
    ) {
        return $this->getRequestHandler()->handleQuery($requestData, $entityClass, $context);
    }

    protected function handleResponse($data = '', int $status = 200, array $headers = []): Response
    {
        return $this->handleSerializedResponse(
            $this->getSerializedResponse($data),
            $status,
            $headers
        );
    }
    
    protected function getSerializedResponse($data): string 
    {
        return $this->getSerializer()->serialize($data, $this->dataFormat, $this->getSerializerContext());
    }
    
    protected function normalize($data): array
    {
        return $this->getSerializer()->normalize($data, $this->dataFormat, $this->getSerializerContext());
    }
    
    protected function encodeNormalizedResponse(array $response): string
    {
        return $this->getSerializer()->encode($response, $this->dataFormat, $this->getSerializerContext());
    }
            
    protected function handleSerializedResponse(string $content, int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }

    /**
     * @param ConstraintViolationListInterface $violations
     *
     * @throws ValidationException
     */
    public function handleValidationViolations(ConstraintViolationListInterface $violations): void
    {
        if ($violations->count()) {
            $errors = [];

            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            throw new ValidationException(
                $this->getSerializer()->serialize($errors, $this->dataFormat)
            );
        }
    }
    
    protected function checkPermission(string $action, $subject = null)
    {
        return $this->checkUserPermission($this->getUser(), $action, $subject);
    }
    
    protected function isAllowed(string $action, $subject = null)
    {
        return $this->isUserActionAllowed($this->getUser(), $action, $subject);
    }
    
    protected function getSanitizer(): Sanitizer
    {
        return $this->getRequestHandler()->getSanitizer();
    }

    protected function getSerializer(): Serializer
    {
        return $this->serializer;
    }

    public function getSerializerContext(): array
    {
        if (!$this->serializerContext) {
            $this->serializerContext = self::DEFAULT_SERIALIZER_CONTEXT;
            
            if ($this->getSerializerGroups()) {
                if ($this->disableDefaultGroup) {
                    $this->serializerContext["groups"] = $this->getSerializerGroups();
                } else {
                    $this->serializerContext["groups"] = array_merge(
                        $this->serializerContext["groups"], 
                        $this->getSerializerGroups()
                    );
                }
            }

            $this->serializerContext[AbstractObjectNormalizer::CIRCULAR_REFERENCE_HANDLER] = function ($object, $format, $context) {
                return $object->getId();
            };
        }

        return $this->serializerContext;
    }

    public function setSerializerContext(array $context, $useDefaults = true): self
    {
        $this->serializerContext = $useDefaults
            ? array_merge(self::DEFAULT_SERIALIZER_CONTEXT, $context)
            : $context;

        if (!isset($this->serializerContext[AbstractObjectNormalizer::CIRCULAR_REFERENCE_HANDLER])) {
            $this->serializerContext[AbstractObjectNormalizer::CIRCULAR_REFERENCE_HANDLER] = function ($object, $format, $context) {
                return $object->getId();
            };
        }

        return $this;
    }

    public function validateRequestParams(Request $request, $constraint): void
    {
        $groups = new Assert\GroupSequence(['Default', 'custom']);
        $params = $this->getRequestHandler()->getBody($request);
        $validator = Validation::createValidator();

        $violations = $validator->validate($params, $constraint, $groups);
    }

    private function getSerializerGroups(): array
    {
        return $this->serializerGroups;
    }

    public function setSerializerGroups(array $serializerGroups)
    {
        $this->serializerGroups = $serializerGroups;
        return $this;
    }
    
    protected function addSerializerGroup(string $groupName)
    {
        $this->serializerGroups[] = $groupName;
    }

    /**
     * @required
     */
    public function setPermissionService(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
}
