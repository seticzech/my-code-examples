<?php

namespace App\Service\Erp\Lmm;

use App\Collection\Lmm\Course\SortedLessonCollection;
use App\Entity\Erp\Lmm\Course;
use App\Entity\Erp\Lmm\CourseToUser;
use App\Entity\Erp\Lmm\CourseToUserLessonStats;
use App\Entity\Erp\Lmm\CourseToUserTestResult;
use App\Entity\Erp\Lmm\Lesson;
use App\Exception\Service\CourseToUser\BeginningOfCourseExcepion;
use App\Exception\Service\CourseToUser\CourseHasNoLessonsException;
use App\Exception\Service\CourseToUser\EndOfCourseException;
use App\Repository\Erp\Lmm\CourseToUserLessonStatsRepository;
use App\Repository\Erp\Lmm\CourseToUserRepository;
use DateTime;

class UndergoingCourseService 
{
        
    /**
     * @var CourseToUserLessonStatsRepository
     */
    protected $lessonStatsRepository;
    
    /**
     * @var CourseToUserRepository 
     */
    protected $courseToUserRepository;
    
    protected $sortedLessonCollectionByCourseId = [];
    
    
    public function __construct(
        CourseToUserLessonStatsRepository $lessonStatsRepository, 
        CourseToUserRepository $courseToUserRepository
    )
    {
        $this->lessonStatsRepository = $lessonStatsRepository;
        $this->courseToUserRepository = $courseToUserRepository;
    }
    
    public function nextLesson(CourseToUser $courseToUser, Course $course): CourseToUser
    {
        if (!$courseToUser->getCurrentLesson()) {
            $courseToUser = $this->initializeCourseToUser($courseToUser, $course);
        } else {
            $nextLesson = $this->getNextLesson($courseToUser->getCurrentLesson());
            
            $courseToUser = $this->lessonMove($courseToUser, $nextLesson);
            
            if (!$nextLesson) {
                throw new EndOfCourseException;
            }
        }
        
        return $courseToUser;
    }
    
    public function previousLesson(CourseToUser $courseToUser, Course $course): CourseToUser
    {
        if (!$courseToUser->getCurrentLesson()) {
            $courseToUser = $this->initializeCourseToUser($courseToUser, $course);
        } else {
            $previousLesson = $this->getPreviousLesson($courseToUser->getCurrentLesson());
            
            $courseToUser = $this->lessonMove($courseToUser, $previousLesson);
            
            if (!$previousLesson) {
                throw new BeginningOfCourseExcepion;
            }
        }
        
        return $courseToUser;
    }
    
    protected function initializeCourseToUser(CourseToUser $courseToUser, Course $course): CourseToUser 
    {
        $firstLesson = $course->getFirstSectionFirstLesson();
        
        if (!$firstLesson) {
            throw new CourseHasNoLessonsException;
        }
        
        $this->saveNewCurrentLesson($courseToUser, $firstLesson);
        
        return $courseToUser;
    }
    
    protected function saveNewCurrentLesson(CourseToUser $courseToUser, Lesson $newCurrentLesson) 
    {
        $this->courseToUserRepository->beginTransaction();
        try {
            if ($courseToUser->getCurrentLesson()) {
                $this->finishLessonStats($courseToUser, $courseToUser->getCurrentLesson());
            }
            
            $this->saveNewLessonStats($courseToUser, $newCurrentLesson);
            
            $courseToUser->setCurrentLesson($newCurrentLesson);
            
            $this->courseToUserRepository->save($courseToUser);
            $this->courseToUserRepository->commit();
        } catch (\Throwable $exc) {
            $this->courseToUserRepository->rollback();
            throw $exc;
        }
    }
    
    protected function saveNewLessonStats(CourseToUser $courseToUser, Lesson $currentLesson) 
    {
        $currentLessonLessonStats = $courseToUser->getLessonStatsByLesson($currentLesson);
        
        if (!$currentLessonLessonStats) {
            $lessonStat = new CourseToUserLessonStats;
            $lessonStat->setCourseToUser($courseToUser);
            $lessonStat->setLesson($currentLesson);
            $courseToUser->addLessonStats($lessonStat);
            
            $this->lessonStatsRepository->save($lessonStat);
        }
    }
    
    protected function getSortedLessonsCollectionIncludingDeleted(Course $course): SortedLessonCollection
    {
        if (empty($this->sortedLessonCollectionByCourseId[(string) $course->getId()])) {
            $this->courseToUserRepository->disableSoftDeleteFilter();
            $sortedLessonsCollection = $course->getSortedLessonsCollection();
            $this->courseToUserRepository->enableSoftDeleteFilter();
            
            $this->sortedLessonCollectionByCourseId[(string) $course->getId()] = $sortedLessonsCollection;
        }
        
        return $this->sortedLessonCollectionByCourseId[(string) $course->getId()];
    }
        
    public function getNextLesson(Lesson $lesson): ?Lesson
    {
        return $this->getSortedLessonsCollectionIncludingDeleted($lesson->getCourse())->getNextLesson($lesson);
    }
        
    public function getPreviousLesson(Lesson $lesson): ?Lesson
    {
        return $this->getSortedLessonsCollectionIncludingDeleted($lesson->getCourse())->getPreviousLesson($lesson);
    }
    
    protected function lessonMove(
        CourseToUser $courseToUser, 
        ?Lesson $lesson
    ): CourseToUser
    {
        if ($lesson) {
            $courseToUser = $this->applyNewLesson($courseToUser, $lesson);
        } else {
            if ($courseToUser->getCurrentLesson()) {
                $this->finishLessonStats($courseToUser, $courseToUser->getCurrentLesson());
            }
        }

        if ($courseToUser->getCourse()->isCompletelyFinished()) {
            $this->finishCourse($courseToUser);
        }
        
        return $courseToUser;
    }
    
    protected function applyNewLesson(
        CourseToUser $courseToUser, 
        Lesson $lesson
    ): CourseToUser
    {
        $sortedLessonsCollection = $this->getSortedLessonsCollectionIncludingDeleted($courseToUser->getCourse());
        
        if (
            !$courseToUser->getLastLesson() 
            || $sortedLessonsCollection->getLessonIndex($lesson) > $sortedLessonsCollection->getLessonIndex($courseToUser->getLastLesson())
        ) {
            $courseToUser->setLastLesson($courseToUser->getCurrentLesson());
        }
        
        $this->saveNewCurrentLesson($courseToUser, $lesson);
        
        return $courseToUser;
    }
    
    public function finishLessonStats(CourseToUser $courseToUser, Lesson $lesson, ?CourseToUserTestResult $testResult = null) {
        $currentLessonLessonStats = $courseToUser->getLessonStatsByLesson($lesson);

        if (!$currentLessonLessonStats) {
            $currentLessonLessonStats = new CourseToUserLessonStats;
            $currentLessonLessonStats->setCourseToUser($courseToUser);
            $currentLessonLessonStats->setLesson($lesson);
            $courseToUser->addLessonStats($currentLessonLessonStats);
        }
        
        if ($testResult) {
            $currentLessonLessonStats->setSubmittedAt(new DateTime);
        }

        if (
            !$currentLessonLessonStats->getFinishedAt()
            && (!$testResult || $testResult->getPassed())
        ) {
            $currentLessonLessonStats->setFinishedAt(new DateTime);
        }
        
        $this->lessonStatsRepository->save($currentLessonLessonStats);
    }

    public function finishCourse(CourseToUser $courseToUser)
    {
        try {
            $courseToUser->setFinishedAt(new DateTime);
            $this->courseToUserRepository->save($courseToUser);
            
            foreach ($courseToUser->getLessonsStats() as $lessonStat) {
                /** @var CourseToUserLessonStats $lessonStat */
                if ($lessonStat->getLesson()->getId() === $courseToUser->getCurrentLesson()->getId()) {
                    $lessonStat->setFinishedAt(new DateTime);
                    $this->lessonStatsRepository->save($lessonStat);
                    break;
                }
            }
            
            $this->courseToUserRepository->commit();
        } catch (\Throwable $exc) {
            $this->courseToUserRepository->rollback();
            throw $exc;
        }
    }
    
    public function getFirstUnfinishedLesson(Course $course, CourseToUser $courseToUser): ?Lesson
    {
        $sortedLessonsCollection = $course->getSortedLessonsCollection();
        
        foreach ($sortedLessonsCollection->getAllLessons() as $lesson) {
            /** @var Lesson $lesson */
            $lessonStats = $courseToUser->getLessonStatsByLesson($lesson);
            
            If (!$lessonStats || !$lessonStats->getFinishedAt()) {
                return $lesson;
            }
        }
        
        return null;
    }
    
}
