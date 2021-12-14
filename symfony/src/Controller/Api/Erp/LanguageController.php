<?php

namespace App\Controller\Api\Erp;

use App\Controller\Api\ControllerAbstract;
use App\Entity\Erp\Language;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\Erp\LanguageRepository;
use Nelmio\ApiDocBundle\Annotation as OA;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LanguageController extends ControllerAbstract
{

    /**
     * @var LanguageRepository
     */
    protected $languageRepository;


    public function __construct(SerializerInterface $serializer, LanguageRepository $languageRepository)
    {
        parent::__construct($serializer);

        $this->languageRepository = $languageRepository;
    }

    /**
     * @Route(
     *     "/api/languages",
     *     name="api_language_create",
     *     methods={"POST"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Create new language"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     description="New language",
     *     in="body",
     *     @OA\Model(type=Language::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Created language",
     *     @OA\Model(type=Language::class, groups={"default"})
     * )
     * @SWG\Tag(name="Core")
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws ValidationException
     */
    public function createLanguageAction(Request $request, ValidatorInterface $validator): Response
    {
        $language = $this->handlePost($request, Language::class);

        $this->handleValidationViolations($validator->validate($language));
        $this->languageRepository->save($language);

        return $this->handleResponse($language, 201);
    }

    /**
     * @Route(
     *     "/api/languages",
     *     name="api_languages_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Return all languages"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned languages",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@OA\Model(type=Language::class, groups={"default"}))
     *     )
     * )
     * @SWG\Tag(name="Core")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getLanguagesAction(Request $request)
    {
        $data = $this->languageRepository->findAll();

        return $this->handleResponse($data);
    }

    /**
     * @Route(
     *     "/api/languages/{languageId}",
     *     name="api_language_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Return language by ID"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned language by ID",
     *     @OA\Model(type=Language::class, groups={"default"})
     * )
     * @SWG\Tag(name="Core")
     *
     * @param string $languageId
     *
     * @return Response
     *
     * @throws NotFoundException
     */
    public function getLanguageAction(string $languageId): Response
    {
        $data = $this->languageRepository->findOneById($languageId);

        if (!$data) {
            throw new NotFoundException('Language not found.');
        }

        return $this->handleResponse($data);
    }

}
