<?php

namespace App\Service\Erp\Lmm;

use App\Entity\Erp\Lmm\CourseToUser;
use App\Entity\Erp\Lmm\CourseToUserTestResult;
use App\Entity\Erp\Lmm\Lesson;
use App\Entity\Erp\Lmm\Segment;
use App\Exception\Service\CourseToUser\IncorrectTestAnswerFormatException;
use App\Exception\Service\CourseToUser\TestCannotBeRepeatedException;
use App\Exception\Service\CourseToUser\TestQuestionNotFoundException;
use App\Repository\Erp\Lmm\CourseToUserTestResultRepository;
use Doctrine\Common\Collections\Collection;
use stdClass;

class EvaluateTestService {
    
    /**
     * @var CourseToUserTestResultRepository
     */
    protected $testResultRepository;
    
    
    public function __construct(CourseToUserTestResultRepository $testResultRepository)
    {
        $this->testResultRepository = $testResultRepository;
    }
    
    public function evaluateTest(array $testAnswers, Lesson $lesson, CourseToUser $courseToUser): CourseToUserTestResult
    {
        if (
            $lesson->getOption(Lesson\LessonOptions::OPTION_DISABLE_REPETITION)
            && $courseToUser->getLastTestResult($lesson)
        ) {
          throw new TestCannotBeRepeatedException("This test cannot be repeated.");  
        }
        
        $testResult = $this->createTestResult($testAnswers, $lesson);
        
        $testResult->setCourseToUser($courseToUser);
        
        $this->testResultRepository->save($testResult);
        
        return $testResult;
    }
    
    protected function createTestResult(array $testAnswers, Lesson $lesson): CourseToUserTestResult
    {
        $scoringCounter = new stdClass();
        $scoringCounter->total = 0;
        $scoringCounter->earned = 0;
        $scoringCounter->incorrect = [];
        
        $questions = $this->getAllQuestions($lesson);
        $answersById = $this->getAnswersById($testAnswers);
        
        foreach ($questions as $question) {
            $scoringCounter = $this->evaluateAnswer($question, $answersById[(string) $question->getId()] ?? [], $scoringCounter);
        }

        $score = $scoringCounter->total 
            ? round($scoringCounter->earned / $scoringCounter->total * 100) 
            : 0;
        
        $minimumTestScore = $lesson->getOption(Lesson\LessonOptions::OPTION_MINIMUM_TEST_SCORE) ?: 0;
        
        $testResult = new CourseToUserTestResult;
        $testResult->setLesson($lesson);
        $testResult->setScore($score);
        $testResult->setPassed($score >= $minimumTestScore);
        $testResult->setIncorrectQuestionIds($scoringCounter->incorrect);
        
        return $testResult;
    }
    
    protected function getAllQuestions(Lesson $lesson): array
    {
        return $lesson->getSegments(false)->filter(
            function (Segment $segment) {
                return $segment->getSegmentType()->isQuestion();
            }
        )->toArray();
    }
    
    protected function getAnswersById(array $testAnswers): array
    {
        $answersById = [];
        
        foreach ($testAnswers as $answer) {
            $this->validateAnswer($answer);
            $answersById[$answer['id']] = (array) $answer['answers'];
        }
        
        return $answersById;
    }
    
    protected function validateAnswer(array $answer)
    {
        if (empty($answer['id'])) {
            throw new IncorrectTestAnswerFormatException("Missing question id.");
        }

        if (!isset($answer['answers'])) {
            throw new IncorrectTestAnswerFormatException("Missing question answers for question {$answer['id']}.");
        }
    }
     
    protected function evaluateAnswer(Segment $question, array $answer, stdClass $scoringCounter): stdClass
    {
        $questionPoints = $question->getOptions()['points'] ?? 1;
        
        if ($answer) {
            $questionAllowMultipleAnswers = $question->getOptions()['multipleAnswers'] ?? false;

            if (!$questionAllowMultipleAnswers && count($answer) > 1) {
                throw new IncorrectTestAnswerFormatException("Question {$question->getId()} does not allow multiple answers.");
            }

            if ($questionAllowMultipleAnswers) {
                $scoringCounter = $this->scoreMultipleAnswerQuestion($question, array_unique($answer), $questionPoints, $scoringCounter);
            } else {
                $scoringCounter = $this->scoreSingleAnswerQuestion($question, reset($answer), $questionPoints, $scoringCounter);
            }
        }
        
        $scoringCounter->total += $questionPoints;

        return $scoringCounter;
    }
    
    protected function getSegmentById(Collection $segments, $id): ?Segment
    {
        return $segments->filter(function (Segment $segment) use ($id) {
            return (string) $segment->getId() === $id;
        })->first() ?: null;
    }
    
    protected function scoreSingleAnswerQuestion(
        Segment $question, 
        string $answerId, 
        int $questionPoints,
        stdClass $scoringCounter
    ): stdClass
    {
        /** @var Segment $answer */
        $answer = $this->getSegmentById($question->getChildren(false), $answerId);
        
        if (!$answer) {
            throw new TestQuestionNotFoundException("Answer id {$answerId} for question id {$question->getId()} not found.");
        }
        
        if ($answer->getOptions()['correct'] ?? false) {
            $scoringCounter->earned += $questionPoints;
        } else {
            $scoringCounter->incorrect[] = $question->getId();
        }
        
        return $scoringCounter;
    }
    
    protected function scoreMultipleAnswerQuestion(
        Segment $question, 
        array $answerIds, 
        int $questionPoints,
        stdClass $scoringCounter
    ): stdClass
    {
        foreach ($answerIds as $answerId) {
            /** @var Segment $answer */
            $answer = $this->getSegmentById($question->getChildren(false), $answerId);
            
            if (!$answer) {
                throw new TestQuestionNotFoundException("Answer id {$answerId} for question id {$question->getId()} not found.");
            }
        }
        
        $correctAnswerIds = $question->getCorrectAnswerIds();
        
        if (!array_diff($answerIds, $correctAnswerIds) && !array_diff($correctAnswerIds, $answerIds)) {
            $scoringCounter->earned += $questionPoints;
        } else {
            $scoringCounter->incorrect[] = $question->getId();
        }
        
        return $scoringCounter;
    }
    
}
