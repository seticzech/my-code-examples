<?php

namespace App\Controller\Api\Erp;

use App\Bundle\OAuth2Bundle\Server\AuthorizationServer;
use App\Controller\Api\ControllerAbstract;
use App\Entity\Erp\Module;
use App\Entity\Erp\Settings;
use App\Entity\Erp\Settings\SettingsOptions;
use App\Entity\Erp\Snm\SocialNetwork;
use App\Exception\AuthenticationException;
use App\Exception\BadRequestException;
use App\Exception\ConflictedException;
use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Model\Erp\Core\OAuth2\Social\AuthPost as SocialAuthPost;
use App\Model\Erp\Core\OAuth2\Social\RefreshPost as SocialRefreshPost;
use App\Model\Erp\Core\OAuth2\Social\TokenPost as SocialTokenPost;
use App\Model\Erp\Core\OAuth2\Sso\AuthPost as SsoAuthPost;
use App\Model\Erp\Core\OAuth2\Sso\TokenPost as SsoTokenPost;
use App\Plugin\Social\Facebook\Api\GraphApi\OAuth2;
use App\Plugin\Social\Facebook\Service\FacebookClient;
use App\Repository\Erp\OAuth2\AccessTokenRepository;
use App\Repository\Erp\SettingsRepository;
use App\Repository\Erp\Snm\SocialNetworkRepository;
use App\Repository\Erp\Snm\UserToSocialNetworkRepository;
use App\Repository\Erp\UserRepository;
use App\Service\Erp\Sso\SsoServiceFactory;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nelmio\ApiDocBundle\Annotation as OA;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OAuth2Controller extends ControllerAbstract
{

    /**
     * @var AuthorizationServer
     */
    protected $authorizationServer;

    /**
     * @var PasswordGrant
     */
    protected $passwordGrant;

    protected $validator;


    public function __construct(
        SerializerInterface $serializer,
        AuthorizationServer $authorizationServer,
        PasswordGrant $passwordGrant,
        ValidatorInterface $validator
    ) {
        parent::__construct($serializer);

        $this->authorizationServer = $authorizationServer;
        $this->passwordGrant = $passwordGrant;
        $this->validator = $validator;

        $this->passwordGrant->setRefreshTokenTTL(new \DateInterval('P1M'));
    }

    /**
     * @Route(
     *     "/api/oauth2/token",
     *     name="api_oauth2_token_post",
     *     methods={"POST"},
     *     format="json"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Token~~post")
     * )
     * @OA\Operation(
     *     summary="Authenticate user"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="API access token",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Token~~response")
     * )
     * @SWG\Tag(name="OAUTH2")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function tokenAction(Request $request)
    {
        // Convert to form request data because of stupid OAuth2 library which cannot handle JSON
        if (($request->getContentType() === 'json') && $request->getContent()) {
            $body = json_decode($request->getContent(), true);
            $request->request->replace($body);
        }

        return $this->oauth2Authenticate($request);
    }

    private function oauth2Authenticate(Request $request, $statusCode = 200)
    {
        $psr17factory = new Psr17Factory();
        $httpFactory = new PsrHttpFactory($psr17factory, $psr17factory, $psr17factory, $psr17factory);

        $psrRequest = $httpFactory->createRequest($request);
        $psrResponse = $httpFactory->createResponse(new Response());

        $this->authorizationServer->enableGrantType(
            $this->passwordGrant,
            new \DateInterval('P1M' /*'PT1H'*/)
        );

        $response = (new HttpFoundationFactory())->createResponse(
            $this->authorizationServer->respondToAccessTokenRequest($psrRequest, $psrResponse)
        );
        $response->setStatusCode($statusCode);

        return $response;
    }

    private function checkSocialNetwork(string $network, SocialNetworkRepository $socialNetworkRepository): SocialNetwork
    {
        $socialNetwork = $socialNetworkRepository->findOneByCode($network);
        if (!$socialNetwork) {
            throw new NotFoundException("Social network '{$network}' not found.");
        }

        return $socialNetwork;
    }

    private function getSsoSettings(SettingsRepository $settingsRepository): Settings
    {
        $settings = $settingsRepository->findSettingsByModuleCode(Module::MODULE_CORE_CODE);
        if (!$settings->getOption(SettingsOptions::CORE_SSO_ENABLED, false)) {
            throw new ForbiddenException("SSO is not enabled.");
        }

        return $settings;
    }

    /**
     * @Route(
     *     "/api/oauth2/sso/auth",
     *     name="api_oauth2_sso_auth_post",
     *     methods={"POST"},
     *     format="json"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Sso_Auth~~post")
     * )
     * @OA\Operation(
     *     summary="Authenticate user against some SSO service"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="URI to redirect for user authentication",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Sso_Auth~~response")
     * )
     * @SWG\Tag(name="OAUTH2")
     *
     * @param Request $request
     * @param ClientRepositoryInterface $clientRepository
     * @param SettingsRepository $settingsRepository
     *
     * @return Response
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function ssoAuthAction(
        Request $request,
        ClientRepositoryInterface $clientRepository,
        SettingsRepository $settingsRepository
    ) {
        $this->authenticateClient($request, $clientRepository);

        /** @var SsoAuthPost $post */
        $post = $this->handlePost($request, SsoAuthPost::class);
        $this->handleValidationViolations($this->validator->validate($post));

        $settings = $this->getSsoSettings($settingsRepository);

        $loginUrl = $settings->getOption(SettingsOptions::CORE_SSO_AUTH_SERVER_URL);
        if (!$loginUrl) {
            throw new BadRequestException(
                sprintf("SSO is not configured properly, missing parameter '%s'", SettingsOptions::CORE_SSO_AUTH_SERVER_URL)
            );
        }

        return $this->handleResponse([
            "loginUrl" => $loginUrl,
        ]);
    }

    /**
     * @Route(
     *     "/api/oauth2/sso/token",
     *     name="api_oauth2_sso_token_post",
     *     methods={"POST"},
     *     format="json"
     * )
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Sso_Token~~post")
     * )
     * @OA\Operation(
     *     summary="Authenticate user according to payload returned from SSO authentication service"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="User logged in",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Token~~response")
     * )
     * @SWG\Response(
     *     response=400,
     *     description="SSO payload verification failed"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="User identity returned from SSO authentication service is forbidden to login"
     * )
     * @SWG\Tag(name="OAUTH2")
     *
     * @param Request $request
     * @param ClientRepositoryInterface $clientRepository
     * @param SettingsRepository $settingsRepository
     * @param SsoServiceFactory $ssoServiceFactory
     * @param UserRepository $userRepository
     *
     * @return Response|\Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function ssoTokenAction(
        Request $request,
        ClientRepositoryInterface $clientRepository,
        SettingsRepository $settingsRepository,
        SsoServiceFactory $ssoServiceFactory,
        UserRepository $userRepository
    )
    {
        $client = $this->authenticateClient($request, $clientRepository);

        /** @var SsoTokenPost $post */
        $post = $this->handlePost($request, SsoTokenPost::class);
        $this->handleValidationViolations($this->validator->validate($post));

        $settings = $this->getSsoSettings($settingsRepository);
        $service = $ssoServiceFactory->createService($client, $this->getTenantId(), $settings);

        $user = $service->processPayload($post->payload);

        // generate auth code valid for one hour
        $userRepository
            ->generateAuthCode($user)
            ->save($user);

        // call action for create OAuth2 token
        $content = [
            'client_id' => $post->client_id,
            'client_secret' => $post->client_secret,
            'grant_type' => 'password',
            'username' => $user->getEmail(),
            'password' => 'auth|' . $user->getAuthCode()
        ];
        $newRequest = $request->duplicate(null, $content);

        return $this->oauth2Authenticate($newRequest);
    }

    /**
     * @Route(
     *     "/api/oauth2/social/auth",
     *     name="api_oauth2_social_auth_post",
     *     methods={"POST"},
     *     format="json"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Social_Auth~~post")
     * )
     * @OA\Operation(
     *     summary="Authenticate user against some social network"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="URI to redirect for user authenticatation on social network",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Sso_Auth~~response")
     * )
     * @SWG\Tag(name="OAUTH2")
     *
     * @param Request $request
     * @param ClientRepositoryInterface $clientRepository
     * @param FacebookClient $facebookClient,
     * @param SocialNetworkRepository $socialNetworkRepository
     *
     * @return Response
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function socialAuthAction(
        Request $request,
        ClientRepositoryInterface $clientRepository,
        FacebookClient $facebookClient,
        SocialNetworkRepository $socialNetworkRepository
    ) {
        $this->authenticateClient($request, $clientRepository);

        /** @var SocialAuthPost $post */
        $post = $this->handlePost($request, SocialAuthPost::class);
        $this->handleValidationViolations($this->validator->validate($post));

        $this->checkSocialNetwork($post->network, $socialNetworkRepository);

        $url = $facebookClient->getGraphApi()->oAuth2()->getLoginUrl($post->redirectUrl);

        return $this->handleResponse([
            'loginUrl' => $url
        ]);
    }

    /**
     * @Route(
     *     "/api/oauth2/social/refresh",
     *     name="api_oauth2_social_refresh_post",
     *     methods={"POST"},
     *     format="json"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Social_Refresh~~post")
     * )
     * @OA\Operation(
     *     summary="Refresh API access token from social network token; API access token must exists"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="API access token",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Token~~response")
     * )
     * @SWG\Tag(name="OAUTH2")
     *
     * @param Request $request
     * @param AccessTokenRepository $accessTokenRepository
     * @param ClientRepositoryInterface $clientRepository
     * @param SocialNetworkRepository $socialNetworkRepository
     * @param UserToSocialNetworkRepository $userToSocialNetworkRepository
     *
     * @return Response
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function socialRefreshAction(
        Request $request,
        AccessTokenRepository $accessTokenRepository,
        ClientRepositoryInterface $clientRepository,
        SocialNetworkRepository $socialNetworkRepository,
        UserToSocialNetworkRepository $userToSocialNetworkRepository
    ) {
        $client = $this->authenticateClient($request, $clientRepository);

        /** @var SocialRefreshPost $post */
        $post = $this->handlePost($request, SocialRefreshPost::class);
        $this->handleValidationViolations($this->validator->validate($post));

        $socialNetwork = $this->checkSocialNetwork($post->network, $socialNetworkRepository);

        $userSocialToken = $userToSocialNetworkRepository->findOneBySocialNetworkAndStoredToken(
            $socialNetwork, $post->token
        );
        if (!$userSocialToken) {
            throw new AuthenticationException('Social token not found.');
        }

        // response with generated bearer token if valid access token exists
        $accessToken = $accessTokenRepository->findAccessToken($client, $userSocialToken->getUser());
        if (!$accessToken) {
            throw new AuthenticationException('API access token not found.');
        }

        $psr17factory = new Psr17Factory();
        $httpFactory = new PsrHttpFactory($psr17factory, $psr17factory, $psr17factory, $psr17factory);

        $serverResponse = $httpFactory->createResponse(new Response());

        return (new HttpFoundationFactory())->createResponse(
            $this->authorizationServer->respondForAccessToken($accessToken, $serverResponse)
        );
    }

    /**
     * @Route(
     *     "/api/oauth2/social/token",
     *     name="api_oauth2_social_token_post",
     *     methods={"POST"},
     *     format="json"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Social_Token~~post")
     * )
     * @OA\Operation(
     *     summary="Exchange social network authentication code for API token"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="User logged in",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Token~~response")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="User registered and logged in",
     *     @SWG\Schema(ref="#/definitions/OAuth2_Token~~response")
     * )
     * @SWG\Response(
     *     response=401,
     *     description="User identified by social network email does not exists in system (when param 'registered' is TRUE)"
     * )
     * @SWG\Response(
     *     response=409,
     *     description="Email returned from social network already exists in system (when param 'registered' is FALSE)"
     * )
     * @SWG\Tag(name="OAUTH2")
     *
     * @param Request $request
     * @param AccessTokenRepository $accessTokenRepository
     * @param ClientRepositoryInterface $clientRepository
     * @param FacebookClient $facebookClient
     * @param SocialNetworkRepository $socialNetworkRepository
     * @param UserRepository $userRepository
     * @param UserToSocialNetworkRepository $userToSocialNetworkRepository
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function socialTokenAction(
        Request $request,
        AccessTokenRepository $accessTokenRepository,
        ClientRepositoryInterface $clientRepository,
        FacebookClient $facebookClient,
        SocialNetworkRepository $socialNetworkRepository,
        UserRepository $userRepository,
        UserToSocialNetworkRepository $userToSocialNetworkRepository
    ) {
        $client = $this->authenticateClient($request, $clientRepository);
        $statusCode = 200;

        /** @var SocialTokenPost $post */
        $post = $this->handlePost($request, SocialTokenPost::class);
        $this->handleValidationViolations($this->validator->validate($post));

        $socialNetwork = $this->checkSocialNetwork($post->network, $socialNetworkRepository);

        $socialToken = $facebookClient->getGraphApi()->oAuth2()->requestAccessTokenFromCode($post->code, $post->redirectUrl);

        $facebookClient->debugAccessToken();
        $me = $facebookClient->getUserBasicPersonalInfo();

        if (!array_key_exists('email',
        )) {
            throw new LogicException('Cannot determine social account email.');
        }


        // check for social network connection
        $userToSocialNetwork = $userToSocialNetworkRepository->findOneBySocialNetworkAndStoredUserId(
            $socialNetwork,
            $me['id']
        );

        $user = $userToSocialNetwork ? $userToSocialNetwork->getUser() : null;
        if (!$user) {
            // user must exists and must have connection to social network
            if ($post->registered === true) {
                throw new AuthenticationException('User must be registered through social network or connected to social network before login.');
            }

            // check if email exists
            $user = $userRepository->findOneByEmailAndTenantId($me['email'], $this->getTenantId());
            if (!$user) {
                // registration
                $user = $userRepository->createUser();

                $user
                    ->setEmail($me['email'])
                    ->setFirstName($me['first_name'])
                    ->setLastName($me['last_name'])
                    ->setTenantId($this->getTenantId());

                $userRepository
                    ->setUserPassword($user, (Uuid::uuid4())->toString());

                $statusCode = 201;
            } elseif ($post->registered === false) {
                throw new ConflictedException('Email address already exists.');
            }

        }

        // generate auth code valid for one hour
        $userRepository
            ->generateAuthCode($user)
            ->save($user);

        if (!$userToSocialNetwork) {
            $userToSocialNetwork = $userToSocialNetworkRepository->createUserToSocialNetwork();

            $userToSocialNetwork
                ->setUser($user)
                ->setTenantId($this->getTenantId())
                ->setSocialNetwork($socialNetwork);
        }

        $userToSocialNetwork
            ->setStore([
                'token' => $socialToken->getValue(),
                'expires_at' => $socialToken->getExpiresAt(),
                'user_id' => $me['id']
            ]);

        $userToSocialNetworkRepository->save($userToSocialNetwork);

        // call action for create OAuth2 token
        $content = [
            'client_id' => $post->client_id,
            'client_secret' => $post->client_secret,
            'grant_type' => 'password',
            'username' => $user->getEmail(),
            'password' => 'auth|' . $user->getAuthCode()
        ];
        $newRequest = $request->duplicate(null, $content);

        return $this->oauth2Authenticate($newRequest, $statusCode);
    }

}
