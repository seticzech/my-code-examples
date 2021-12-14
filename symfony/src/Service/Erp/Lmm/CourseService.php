<?php

namespace App\Service\Erp\Lmm;

use App\Entity\Erp\Lmm\Course;
use App\Entity\Erp\Lmm\Section;
use App\Repository\Erp\Lmm\CourseRepository;
use App\Repository\Erp\Lmm\LessonRepository;
use App\Repository\Erp\Lmm\SectionRepository;
use App\Repository\Erp\Lmm\SegmentRepository;
use Throwable;

class CourseService
{
    
    /**
     * @var CourseRepository
     */
    protected $courseRepository;
    
    /**
     * @var SectionRepository
     */
    protected $sectionRepository;
    
    /**
     * @var LessonRepository
     */
    protected $lessonRepository;
    
    /**
     * @var SegmentRepository
     */
    protected $segmentRepository;
    
    
    public function __construct(
        CourseRepository $courseRepository, 
        SectionRepository $sectionRepository, 
        LessonRepository $lessonRepository, 
        SegmentRepository $segmentRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->sectionRepository = $sectionRepository;
        $this->lessonRepository = $lessonRepository;
        $this->segmentRepository = $segmentRepository;
    }
    
    
    public function duplicateCourse(Course $course): Course
    {
        $newCourse = clone $course;
        $newCourse->setId(null);
        $newCourse->setCreatedAt(new \DateTime);
        $newCourse->setUpdatedAt(new \DateTime);
        
        $this->courseRepository->beginTransaction();
        
        try {
            $this->courseRepository->save($newCourse);
            
            $this->duplicateSections($newCourse);
            
            $this->courseRepository->commit();
            
            return $newCourse;
        } catch (Throwable $exc) {
            $this->courseRepository->rollback();
            throw $exc;
        }
    }
    
    protected function duplicateSections(Course $newCourse)
    {
        $newSections = [];
        foreach ($newCourse->getSections() as $section) {
            /** @var Section $section */
            $newSection = clone $section;
            $newSection->setId(null);
            $newSection->setCourse($newCourse);
            $newSection->setCreatedAt(new \DateTime);
            $newSection->setUpdatedAt(new \DateTime);
            
            $this->sectionRepository->save($newSection);
            $this->duplicateLessons($newSection);
            $newSections[] = $newSection;
        }
        $newCourse->setSections($newSections);
    }
    
    protected function duplicateLessons(Section $newSection)
    {
        $newLessons = [];
        foreach ($newSection->getLessons() as $lesson) {
            /** @var \App\Entity\Erp\Lmm\Lesson $lesson */
            $newLesson = clone $lesson;
            $newLesson->setId(null);
            $newLesson->setSection($newSection);
            $newLesson->setCreatedAt(new \DateTime);
            $newLesson->setUpdatedAt(new \DateTime);
            
            $this->lessonRepository->save($newLesson);
            $this->duplicateSegments($newLesson);
            $newLessons[] = $newLesson;
        }
        $newSection->setLessons($newLessons);
    }
    
    protected function duplicateSegments(\App\Entity\Erp\Lmm\Lesson $newLesson)
    {
        $newSegments = [];
        foreach ($newLesson->getSegments() as $segment) {
            /** @var \App\Entity\Erp\Lmm\Segment $segment */
            $newSegment = clone $segment;
            $newSegment->setId(null);
            $newSegment->setLesson($newLesson);
            $newSegment->setCreatedAt(new \DateTime);
            $newSegment->setUpdatedAt(new \DateTime);
            
            $this->segmentRepository->save($newSegment);
            $newSegments[] = $newSegment;
        }
        $newLesson->setSegments($newSegments);
    }

}
