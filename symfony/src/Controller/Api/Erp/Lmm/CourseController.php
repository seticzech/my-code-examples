<?php

namespace App\Controller\Api\Erp\Lmm;

use App\Controller\Api\ControllerAbstract;
use App\Entity\Erp\Lmm\Course;
use App\Entity\Erp\Lmm\Section;
use App\Exception\NotFoundException;
use App\Model\Erp\Common\ArrayItemRearrange;
use App\Repository\Erp\Lmm\CourseRepository;
use App\Service\Erp\Acl\ActionService;
use App\Service\Erp\Acl\ResourceService;
use App\Service\Erp\Lmm\CourseService;
use Nelmio\ApiDocBundle\Annotation as OA;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CourseController extends ControllerAbstract
{

    const ACL_RESOURCE_NAME = ResourceService::CODE_LMM_COURSE;
    
    const ACL_ACTION_DUPLICATE = ActionService::CODE_DUPLICATE;
    
    /**
     * @var CourseRepository
     */
    protected $courseRepository;
    

    public function __construct(SerializerInterface $serializer, CourseRepository $courseRepository)
    {
        parent::__construct($serializer);

        $this->courseRepository = $courseRepository;
    }

    /**
     * @Route(
     *     "/api/lmm/courses",
     *     name="api_lmm_course_create",
     *     methods={"POST"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Create course"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Lmm_Course~~create")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Created course",
     *     @SWG\Schema(ref="#/definitions/Lmm_Course")
     * )
     * @SWG\Tag(name="LMM")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function createCourseAction(Request $request)
    {
        $course = $this->handlePost($request, Course::class);
        
        $this->checkPermission(self::ACL_ACTION_CREATE, $course);
        
        $this->courseRepository->save($course);

        return $this->handleResponse($course, 201);
    }

    /**
     * @Route(
     *     "/api/lmm/courses/{courseId}/sections",
     *     name="api_lmm_course_section_create",
     *     methods={"POST"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Create section in course"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Lmm_Section~~create")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Created section",
     *     @SWG\Schema(ref="#/definitions/Lmm_Section")
     * )
     * @SWG\Tag(name="LMM")
     *
     * @param Request $request
     * @param string $courseId
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function createSectionAction(Request $request, string $courseId)
    {
        $course = $this->findCourseById($courseId);
        
        $this->checkPermission(self::ACL_ACTION_UPDATE, $course);

        /** @var Section $section */
        $section = $this->handlePost($request, Section::class);

        $this->courseRepository
            ->addSection($course, $section)
            ->saveAll();

        return $this->handleResponse($section, 201);
    }
    
    protected function findCourseById(string $courseId, bool $onlyPublished = false): Course
    {
        $course = $this->courseRepository->findCourseById($courseId, $onlyPublished);

        if (!$course) {
            throw new NotFoundException('Course not found.');
        }
        
        return $course;
    }

    /**
     * @Route(
     *     "/api/lmm/courses",
     *     name="api_lmm_courses_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Return all courses"
     * )
     * @SWG\Parameter(
     *     name="groups", in="path", type="string",
     *     description="Group names delimited by comma"
     * )
     * @SWG\Parameter(
     *     name="published", in="path", type="string",
     *     description="Filter published/not published courses"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned courses",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Lmm_Course")
     *     )
     * )
     * @SWG\Tag(name="LMM")
     * 
     * @param Request $request
     *
     * @return Response
     */
    public function getCoursesAction(Request $request)
    {
        if (!$this->getUser()) {
            $this->setSerializerGroups(["anon"]);
            $this->disableDefaultGroup = true;
        }
        
        $data = $this->courseRepository->findAllFiltered($request->query->all());
        
        return $this->handleResponse($data);
    }

    /**
     * @Route(
     *     "/api/lmm/courses/{courseId}",
     *     name="api_lmm_course_get",
     *     requirements={"courseId"="[a-f0-9\-]{36}"},
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Get course by ID"
     * )
     * @SWG\Parameter(
     *     name="groups", in="path", type="string",
     *     description="Group names delimited by comma"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Requested course",
     *     @SWG\Schema(ref="#/definitions/Lmm_Course")
     * )
     * @SWG\Tag(name="LMM")
     *
     * @param Request $request
     * @param string $courseId
     *
     * @return Response
     */
    public function getCourseAction(Request $request, string $courseId)
    {
        if (!$this->getUser()) {
            $this->setSerializerGroups(["anon"]);
            $this->disableDefaultGroup = true;
        }
        
        $course = $this->findCourseById($courseId, (bool) $request->query->get('published'));

        return $this->handleResponse($course);
    }

    /**
     * @Route(
     *     "/api/lmm/courses/{courseId}/sections",
     *     name="api_lmm_course_sections_get",
     *     methods={"GET"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Return sections of course"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned sections",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Lmm_Section")
     *     )
     * )
     * @SWG\Tag(name="LMM")
     *
     * @param string $courseId
     *
     * @return Response
     */
    public function getSectionsAction(string $courseId)
    {
        $course = $this->findCourseById($courseId);

        return $this->handleResponse($course->getSections());
    }

    /**
     * @Route(
     *     "/api/lmm/courses/{courseId}",
     *     name="api_lmm_course_patch",
     *     requirements={"courseId"="[a-f0-9\-]{36}"},
     *     methods={"PATCH"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Update course"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Lmm_Course~~patch")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Updated course",
     *     @SWG\Schema(ref="#/definitions/Lmm_Course")
     * )
     * @SWG\Tag(name="LMM")
     *
     * @param Request $request
     * @param string $courseId
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchCourseAction(Request $request, string $courseId)
    {
        $course = $this->findCourseById($courseId);
        
        $this->checkPermission(self::ACL_ACTION_UPDATE, $course);

        $excludedKeys = ['id', 'user'];
        $data = $this->getRequestHandler()->getBodyExcluded($request, $excludedKeys);

        $this->handlePatch($data, $course);
        $this->courseRepository->save($course);

        return $this->handleResponse($course);
    }
    
    /**
     * @Route(
     *     "/api/lmm/courses/{courseId}/duplicate",
     *     name="api_lmm_course_duplicate_post",
     *     requirements={"courseId"="[a-f0-9\-]{36}"},
     *     methods={"POST"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Duplicate course"
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Duplicated course",
     *     @SWG\Schema(ref="#/definitions/Lmm_Course")
     * )
     * @SWG\Tag(name="LMM")
     *
     * @param Request $request
     * @param string $courseId
     * @param CourseService $courseService
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function duplicateCourseAction(Request $request, string $courseId, CourseService $courseService)
    {
        $course = $this->findCourseById($courseId);
        
        $this->checkPermission(self::ACL_ACTION_DUPLICATE, $course);
        
        $newCourse = $courseService->duplicateCourse($course);

        return $this->handleResponse($newCourse, 201);
    }

    /**
     * @Route(
     *     "/api/lmm/courses/{courseId}/sections/rearrange",
     *     name="api_lmm_course_sections_rearrange_patch",
     *     methods={"PATCH"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Rearrange position of section in course"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref="#/definitions/Common_ArrayItemRearrange")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Sections in new order",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Lmm_Section")
     *     )
     * )
     * @SWG\Tag(name="LMM")
     *
     * @param Request $request
     * @param string $courseId
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function rearrangeSectionAction(Request $request, string $courseId, ValidatorInterface $validator)
    {
        $course = $this->findCourseById($courseId);
        
        $this->checkPermission(self::ACL_ACTION_UPDATE, $course);
        
        /** @var ArrayItemRearrange $params */
        $params = $this->handlePatch($request, new ArrayItemRearrange);
        $this->handleValidationViolations($validator->validate($params));

        $sections = $this
            ->courseRepository
            ->moveSection($course, $params->oldIndex, $params->newIndex);

        $this->courseRepository->saveAll();

        return $this->handleResponse($sections);
    }
    
     /**
     * @Route(
     *     "/api/lmm/courses/{courseId}",
     *     name="api_course_delete",
     *     methods={"DELETE"},
     *     format="json"
     * )
     * @OA\Operation(
     *     summary="Delete course"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Deleted course",
     *     @SWG\Schema(ref="#/definitions/Lmm_Course")
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Course not found"
     * )
     * @SWG\Tag(name="LMM")
     * 
     * @param string $courseId
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteCourseAction(string $courseId)
    {
        $course = $this->findCourseById($courseId);
        
        $this->checkPermission(self::ACL_ACTION_DELETE, $course);
        
        $this
            ->courseRepository
            ->beginTransaction()
            ->removeCourse($course)
            ->saveAll();
        
        return $this->handleResponse($course);
    }
}
