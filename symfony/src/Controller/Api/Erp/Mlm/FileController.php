<?php

namespace App\Controller\Api\Erp\Mlm;

use App\Controller\Api\ControllerAbstract;
use App\Exception\InsufficientDataException;
use App\Exception\NotFoundException;
use App\Service\FileService;
use Nelmio\ApiDocBundle\Annotation as OA;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class FileController extends ControllerAbstract
{

    protected $fileService;
    
    
    public function __construct(
        SerializerInterface $serializer,
        FileService $fileService
    ) {
        parent::__construct($serializer);

        $this->fileService = $fileService;
    }

    /**
     * @Route(
     *     "/api/files/download/{fileId}",
     *     name="api_files_download_get",
     *     methods={"GET"}
     * )
     * @OA\Operation(
     *     summary="Download file",
     *     produces={"application/octet-stream"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Content of the file",
     *     @SWG\Schema(
     *         type="file"
     *     )
     * )
     * @SWG\Tag(name="Core")
     *
     * @param Request $request
     * @param string $fileId
     */
    public function downloadAction(Request $request, string $fileId)
    {
        $file = $this->fileService->findFileById($fileId);

        if (!$file) {
            throw new NotFoundException('File not found.');
        }

        $fileName = $this->fileService->getUploadedFilePath($file);
        $response = new BinaryFileResponse($fileName);
        
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getName());

        return $response;
    }

    /**
     * @Route(
     *     "/api/files/upload",
     *     name="api_files_upload_post",
     *     methods={"POST"}
     * )
     * @OA\Operation(
     *     summary="Upload file",
     *     consumes={"multipart/form-data"}
     * )
     * @SWG\Parameter(
     *     name="upload",
     *     in="formData",
     *     type="file"
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Created file",
     *     @SWG\Schema(ref="#/definitions/Mlm_File")
     * )
     * @SWG\Tag(name="Core")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function uploadAction(Request $request)
    {
        $tenantId = $this->getUser()->getTenantId();

        /** @var UploadedFile $upload */
        $upload = $request->files->get('upload');
        if (!$upload) {
            throw new InsufficientDataException("No uploaded file found. Check you form if it contains file element named 'upload'.");
        }

        $file = $this->fileService->uploadFile($tenantId, $upload);

        return $this->handleResponse($file, 201, ['Content-Type' => 'application/json']);
    }

}
