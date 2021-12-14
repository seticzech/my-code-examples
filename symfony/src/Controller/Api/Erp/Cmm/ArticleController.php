<?php

namespace App\Controller\Api\Erp\Cmm;

use App\Controller\Api\ControllerAbstract;
use App\Entity\Erp\Cmm\Article;
use App\Exception\NotFoundException;
use App\Repository\Erp\Cmm\ArticleRepository;
use Nelmio\ApiDocBundle\Annotation as OA;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ArticleController extends ControllerAbstract
{

    /**
     * @var ArticleRepository
     */
    protected $articleRepository;


    public function __construct(SerializerInterface $serializer, ArticleRepository $articleRepository)
    {
        parent::__construct($serializer);

        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route(
     *     "/api/cmm/articles",
     *     name="api_cmm_article_create",
     *     methods={"POST"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Create article"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Cmm_Article~~create")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Created article",
     *     @SWG\Schema(ref="#/definitions/Cmm_Article")
     * )
     * @SWG\Tag(name="CMM")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function createArticleAction(Request $request)
    {
        $arcticle = $this->handlePost($request, Article::class);
        
        $this->articleRepository->save($arcticle);

        return $this->handleResponse($arcticle, 201);
    }

    /**
     * @Route(
     *     "/api/cmm/articles",
     *     name="api_cmm_articles_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Return all articles"
     * )
     * @SWG\Parameter(
     *     name="groups", in="path", type="string",
     *     description="Group names delimited by comma"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned articles",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Cmm_Article")
     *     )
     * )
     * @SWG\Tag(name="CMM")
     * 
     * @param Request $request
     *
     * @return Response
     */
    public function getArticlesAction(Request $request)
    {
        $data = $this->articleRepository->findAllFiltered($request->query->all());
        
        return $this->handleResponse($data);
    }

    /**
     * @Route(
     *     "/api/cmm/articles/{articleId}",
     *     name="api_cmm_article_get",
     *     requirements={"articleId"="[a-f0-9\-]{36}"},
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Get article by ID"
     * )
     * @SWG\Parameter(
     *     name="groups", in="path", type="string",
     *     description="Group names delimited by comma"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Requested article",
     *     @SWG\Schema(ref="#/definitions/Cmm_Article")
     * )
     * @SWG\Tag(name="CMM")
     *
     * @param Request $request
     * @param string $articleId
     *
     * @return Response
     */
    public function getArticleAction(Request $request, string $articleId)
    {
        $article = $this->findArticleById($articleId, (bool) $request->query->get('published'));

        return $this->handleResponse($article);
    }
    
    protected function findArticleById(string $articleId, bool $onlyPublished = false): Article
    {
        $article = $this->articleRepository->findArticleById($articleId, $onlyPublished);
        
        if (!$article) {
            throw new NotFoundException('Article not found.');
        }
        
        return $article;
    }

    /**
     * @Route(
     *     "/api/cmm/articles/{articleId}",
     *     name="api_cmm_article_patch",
     *     requirements={"articleId"="[a-f0-9\-]{36}"},
     *     methods={"PATCH"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Update article"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Cmm_Article~~patch")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Updated article",
     *     @SWG\Schema(ref="#/definitions/Cmm_Article")
     * )
     * @SWG\Tag(name="CMM")
     *
     * @param Request $request
     * @param string $articleId
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchArticleAction(Request $request, string $articleId)
    {
        $article = $this->findArticleById($articleId);

        $excludedKeys = ['id'];
        $data = $this->getRequestHandler()->getBodyExcluded($request, $excludedKeys);

        $this->handlePatch($data, $article);
        $this->articleRepository->save($article);

        return $this->handleResponse($article);
    }
    
    /**
     * @Route(
     *     "/api/cmm/articles/{articleId}",
     *     name="api_cmm_article_delete",
     *     methods={"DELETE"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Delete article"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Deleted article",
     *     @SWG\Schema(ref="#/definitions/Cmm_Article")
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Article not found"
     * )
     * @SWG\Tag(name="CMM")
     * 
     * @param string $articleId 
    *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws ExceptionInterface
     */
    public function deleteArticleAction(string $articleId)
    {
        $article = $this->findArticleById($articleId);
        
        $this->articleRepository->remove($article);
        $this->articleRepository->saveAll();
        
        return $this->handleResponse($article);
    }

}
