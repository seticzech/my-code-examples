<?php

namespace App\Controller\Api\Erp\Snm;

use App\Controller\Api\ControllerAbstract;
use App\Entity\Erp\Snm\SocialNetwork;
use App\Exception\InsufficientDataException;
use App\Exception\NotFoundException;
use App\Model\Erp\Snm\ConnectCodePost;
use App\Model\Erp\Snm\ConnectTokenPost;
use App\Model\Erp\Snm\DebugTokenRequestQuery;
use App\Model\Erp\Snm\DisconnectPost;
use App\Plugin\Social\Facebook\FacebookClient;
use App\Repository\Erp\Snm\SocialNetworkRepository;
use App\Repository\Erp\Snm\UserToSocialNetworkRepository;
use Nelmio\ApiDocBundle\Annotation as OA;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SocialController extends ControllerAbstract
{

    /**
     * @var SocialNetworkRepository
     */
    protected $socialNetworkRepository;

    /**
     * @var UserToSocialNetworkRepository
     */
    protected $userToSocialNetworkRepository;


    public function __construct(
        SerializerInterface $serializer,
        SocialNetworkRepository $socialNetworkRepository,
        UserToSocialNetworkRepository $userToSocialNetworkRepository
    ) {
        parent::__construct($serializer);

        $this->socialNetworkRepository = $socialNetworkRepository;
        $this->userToSocialNetworkRepository = $userToSocialNetworkRepository;
    }

    private function checkSocialNetwork(string $network): SocialNetwork
    {
        $socialNetwork = $this->socialNetworkRepository->findOneByCode($network);
        if (!$socialNetwork) {
            throw new NotFoundException("Social network '{$network}' not found.");
        }

        return $socialNetwork;
    }

    /**
     * @Route(
     *     "/api/social/connect/code",
     *     name="api_social_connect_code_post",
     *     methods={"POST"},
     *     format="json"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Snm_ConnectCode~~post")
     * )
     * @OA\Operation(
     *     summary="Connect user to social network by social network authorization code"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Connected user",
     *     @SWG\Schema(ref="#/definitions/Core_User")
     * )
     * @SWG\Tag(name="SNM")
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function connectAction(Request $request, ValidatorInterface $validator)
    {
        /** @var ConnectCodePost $post */
        $post = $this->handlePost($request, ConnectCodePost::class);
        $this->handleValidationViolations($validator->validate($post));

        $socialNetwork = $this->checkSocialNetwork($post->network);
        $user = $this->getUser();

        $fbClient = new FacebookClient();
        $socialToken = $fbClient->requestAccessTokenFromCode($post->code, $post->redirectUrl);

        $fbClient->debugAccessToken();
        $me = $fbClient->getUserBasicPersonalInfo();

        // check if connection exists already
        $userToSocialNetwork = $this->userToSocialNetworkRepository->findOneBySocialNetworkAndUser(
            $socialNetwork, $user
        );
        if (!$userToSocialNetwork) {
            $userToSocialNetwork = $this->userToSocialNetworkRepository->createUserToSocialNetwork();
        }

        $userToSocialNetwork
            ->setSocialNetwork($socialNetwork)
            ->setStore([
                'token' => $socialToken->getValue(),
                'expires_at' => $socialToken->getExpiresAt(),
                'user_id' => $me['id']
            ]);

        $this->userToSocialNetworkRepository->save($userToSocialNetwork);

        return $this->handleResponse($user);
    }

    /**
     * @Route(
     *     "/api/social/connect/token",
     *     name="api_social_connect_token_post",
     *     methods={"POST"},
     *     format="json"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Snm_ConnectToken~~post")
     * )
     * @OA\Operation(
     *     summary="Connect user to social network by social network token"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Connected user",
     *     @SWG\Schema(ref="#/definitions/Core_User")
     * )
     * @SWG\Tag(name="SNM")
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function connectTokenAction(Request $request, ValidatorInterface $validator)
    {
        /** @var ConnectTokenPost $post */
        $post = $this->handlePost($request, ConnectTokenPost::class);
        $this->handleValidationViolations($validator->validate($post));

        $socialNetwork = $this->checkSocialNetwork($post->network);
        $user = $this->getUser();

        $fbClient = new FacebookClient();
        $socialToken = $fbClient->requestAccessTokenFromData($post->token, $post->expires_in);

        $fbClient->debugAccessToken();
        $me = $fbClient->getUserBasicPersonalInfo();

        // check if connection exists already
        $userToSocialNetwork = $this->userToSocialNetworkRepository->findOneBySocialNetworkAndUser(
            $socialNetwork, $user
        );
        if (!$userToSocialNetwork) {
            $userToSocialNetwork = $this->userToSocialNetworkRepository->createUserToSocialNetwork();
        }

        $userToSocialNetwork
            ->setSocialNetwork($socialNetwork)
            ->setStore([
                'token' => $socialToken->getValue(),
                'expires_at' => $socialToken->getExpiresAt(),
                'user_id' => $me['id']
            ]);

        $this->userToSocialNetworkRepository->save($userToSocialNetwork);

        return $this->handleResponse($user);
    }

    /**
     * @Route(
     *     "/api/social/debug-token",
     *     name="api_social_debug_token_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @SWG\Parameter(
     *     name="network",
     *     in="path",
     *     description="Social network code, e.g. facebook",
     *     type="string",
     *     required=true
     * )
     * @OA\Operation(
     *     summary="Debug social access token if exists"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Access token metadata",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(type="object")
     *     )
     * )
     * @SWG\Tag(name="SNM")
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     */
    public function debugTokenAction(Request $request, ValidatorInterface $validator)
    {
        /** @var DebugTokenRequestQuery $params */
        $params = $this->handleQuery($request, DebugTokenRequestQuery::class);
        $this->handleValidationViolations($validator->validate($params));

        $socialNetwork = $this->checkSocialNetwork($params->network);
        $user = $this->getUser();

        $userToSocialNetwork = $this->userToSocialNetworkRepository->findOneBySocialNetworkAndUser(
            $socialNetwork, $user
        );
        if (!$userToSocialNetwork) {
            throw new NotFoundException('User is not connected to specified social network.');
        }

        $token = $userToSocialNetwork->getStore()->get('token');
        if (!$token) {
            throw new NotFoundException('Social network token is empty.');
        }

        $fbClient = new FacebookClient();
        $fbClient->setAccessToken($userToSocialNetwork->getStore()->get('token'));
        $metadata = $fbClient->debugAccessToken();

        $result = [
            'app_id' => $metadata->getField('app_id'),
            'application' => $metadata->getField('application'),
            'type' => $metadata->getField('type'),
            'expires_at' => $metadata->getField('expires_at'),
            'is_valid' => $metadata->getField('is_valid'),
            'issued_at' => $metadata->getField('issued_at'),
            'scopes' => $metadata->getField('scopes'),
            'granular_scopes' => $metadata->getField('granular_scopes'),
        ];

        return $this->handleResponse($result);
    }

}
