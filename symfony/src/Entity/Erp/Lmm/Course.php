<?php

namespace App\Entity\Erp\Lmm;

use App\Collection\Lmm\Course\SortedLessonCollection;
use App\Contract\Entity\ContainerAwareInterface;
use App\Contract\Entity\TenantAwareInterface;
use App\Contract\Entity\UserAwareInterface;
use App\Entity\Erp\Language;
use App\Entity\Erp\Lmm\Lesson;
use App\Entity\Erp\Mlm\File;
use App\Entity\Erp\UserGroup;
use App\Entity\IdentifiedAbstract;
use App\Service\Erp\Acl\ActionService;
use App\Service\Erp\Acl\ResourceService;
use App\Traits\Entity\PublishedTrait;
use App\Traits\Entity\SoftDeletableTrait;
use App\Traits\Entity\TenantAwareTrait;
use App\Traits\Entity\TimestampableTrait;
use App\Traits\Entity\UserAwareTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.lmm_courses")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class Course extends IdentifiedAbstract implements TenantAwareInterface, UserAwareInterface, ContainerAwareInterface
{

    use PublishedTrait,
        SoftDeletableTrait,
        TenantAwareTrait,
        TimestampableTrait,
        UserAwareTrait;
    
    const ACL_RESOURCE_NAME = ResourceService::CODE_LMM_COURSE;
    const ACL_ACTION_ENROLL_USERS = ActionService::CODE_LMM_ENROLL_OTHER_USERS_TO_COURSES;

    /**
     * @ORM\ManyToOne(targetEntity = "CourseCategory")
     * @ORM\JoinColumn(nullable = false)
     *
     * @Groups({"default", "anon"})
     *
     * @var CourseCategory
     */
    protected $courseCategory;
    
    /**
     * @ORM\OneToMany(targetEntity="CourseToUser", mappedBy="course")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     * 
     * @var CourseToUser[]|ArrayCollection
     */
    protected $coursesToUsers;
    
    /**
     * @Groups({"default"})
     *
     * @var int
     */
    protected $usersCount;
    
    /**
     * @ORM\Column(type = "json_array", options = {"jsonb": true, "default": "{}"})
     *
     * @Groups({"default", "anon"})
     * @SWG\Property(type="object")
     *
     * @var array
     */
    protected $description;
    
    /**
     * @ORM\ManyToOne(targetEntity = "App\Entity\Erp\Language")
     * @ORM\JoinColumn(nullable = false)
     *
     * @Groups({"default", "anon"})
     *
     * @var Language
     */
    protected $language;
    
    /**
     * @ORM\ManyToMany(targetEntity = "App\Entity\Erp\UserGroup", inversedBy = "courses")
     * @ORM\JoinTable(name = "bb_erp.lmm_courses_to_users_groups")
     * 
     * @Groups({"default"})
     *
     * @var ArrayCollection|UserGroup[]
     */
    protected $userGroups;

    /**
     * @ORM\OneToMany(targetEntity = "Section", mappedBy = "course")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     *
     * @Groups({"default"})
     *
     * @var ArrayCollection|Section[]
     */
    protected $sections;

    /**
     * @Groups({"default", "anon"})
     *
     * @var int
     */
    protected $sectionsCount;

    /**
     * @Groups({"default", "anon"})
     *
     * @var int
     */
    protected $lessonsCount;

    /**
     * @ORM\Column(type = "string", length = 256, nullable = true)
     *
     * @Groups({"default", "anon"})
     *
     * @var string|null
     */
    protected $subtitle;

    /**
     * @ORM\ManyToMany(targetEntity = "Tag", inversedBy = "courses")
     * @ORM\JoinTable(name = "bb_erp.lmm_courses_to_tags")
     *
     * @var ArrayCollection|Tag[]
     */
    protected $tags;

    /**
     * @ORM\Column(type = "string", length = 256, nullable = false)
     *
     * @Groups({"default", "anon"})
     *
     * @var string
     */
    protected $title;
    
    /**
     * @ORM\ManyToOne(targetEntity = "App\Entity\Erp\Mlm\File")
     * @ORM\JoinColumn(nullable = true)
     * 
     * @Groups({"default", "anon"})
     * 
     * @var File
     */
    protected $image;
        
    /**
     * @ORM\Column(type = "json_array", options = {"jsonb": true, "default": "{}"})
     *
     * @Groups({"default"})
     * @SWG\Property(
     *     type="object",
     *     @SWG\Property(property="forceLinearProgress", type="boolean"),
     * )
     *
     * @var array
     */
    protected $options = [];
    
    /**
     * @Groups({"currentUserStats"})
     * 
     * @var bool
     */
    protected $isEnrolled;
    
    /**
     * @Groups({"currentUserStats"})
     * 
     * @var DateTime
     */
    protected $enrolledAt;
    
    /**
     * @Groups({"currentUserStats"})
     * 
     * @var Lesson
     */
    protected $currentLesson;
    
    /**
     * @Groups({"currentUserStats"})
     * 
     * @var Lesson
     */
    protected $lastLesson;
    
    /**
     * @Groups({"currentUserStats"})
     * 
     * @var bool
     */
    protected $isFinished;
    
    /**
     * @Groups({"currentUserStats"})
     * 
     * @var int
     */
    protected $doneByPercents;
    
    /**
     * @Groups({"currentUserStats"})
     * 
     * @var int
     */
    protected $doneByLessonsCount;
    
    /**
     * @var CourseToUser
     */
    protected $studentCourseToUser;
    

    public function __construct()
    {
        $this->isPublished = false;
        $this->sections = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->coursesToUsers = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
    }
    
    public function getDescription(): ?array
    {
        return $this->description;
    }

    public function setDescription(array $description)
    {
        $this->description = $description;
        
        return $this;
    }
        
    /**
     * @return UserGroup[]|ArrayCollection
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): self
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
        }

        return $this;
    }
    
    
    public function removeUserGroup(UserGroup $userGroup): self
    {
        if ($this->userGroups->contains($userGroup)) {
            $this->userGroups->removeElement($userGroup);
        }

        return $this;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function clearTags(): self
    {
        $this->tags->clear();

        return $this;
    }

    public function getCourseCategory(): ?CourseCategory
    {
        return $this->courseCategory;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    /**
     * @return ArrayCollection
     */
    public function getSections()
    {
        return $this->sections;
    }
    
    public function setSections(array $sections): self
    {
        $this->sections = new ArrayCollection($sections);
        return $this;
    }
        
    public function getFirstSectionFirstLesson(): ?Lesson
    {
        foreach ($this->getSections() as $section) {
            /** @var Section $section */
            if ($section->hasLessons()) {
                return $section->getFirstLesson();
            }
        }
    }
    
    public function getSortedLessonsCollection(): SortedLessonCollection {
        return new SortedLessonCollection($this);
    }

    public function getSectionsCount(): int
    {
        return $this->sections->count();
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    /**
     * @return Tag[]|ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function hasSections(): bool
    {
        return $this->sections->count() > 0;
    }

    public function setCourseCategory(CourseCategory $category): self
    {
        $this->courseCategory = $category;

        return $this;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function setSubtitle(?string $value): self
    {
        $this->subtitle = $value;

        return $this;
    }

    public function setTitle(string $value): self
    {
        $this->title = $value;

        return $this;
    }
    
    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image = null): self
    {
        $this->image = $image;
        
        return $this;
    }
    
    public function getOptions():? array
    {
        return array_merge(
            Course\CourseOptions::getDefaultOptions(),
            $this->options
        );
    }
    
    public function getOption(string $optionName)
    {
        return $this->getOptions()[$optionName] ?? null;
    }

    public function setOptions(array $options)
    {
        $this->options = array_merge(
            $this->options,
            $options
        );
        return $this;
    }

    /**
     * @return CourseToUser[]|ArrayCollection
     */
    public function getCoursesToUsers() 
    {
        return $this->coursesToUsers;
    }
    
    public function getUsersCount(): int
    {
        return $this->coursesToUsers->count();
    }
    
    private function getAuthenticatedUsersCourseToUser(): ?CourseToUser 
    {
        $currentUsersCourseToUser = null;
        
        if ($this->getAuthenticatedUser()) {
            foreach ($this->coursesToUsers as $courseToUser) {
                /** @var CourseToUser $courseToUser */
                if ($courseToUser->getUser()->getId() === $this->getAuthenticatedUser()->getId()) {
                    $currentUsersCourseToUser = $courseToUser;
                    break;
                }
            }
        }
        
        return $currentUsersCourseToUser;
    }
    
    public function getStudentCourseToUser(): ?CourseToUser
    {
        return $this->studentCourseToUser ?: $this->getAuthenticatedUsersCourseToUser();
    }

    public function setStudentCourseToUser(?CourseToUser $studentCourseToUser = null)
    {
        $this->studentCourseToUser = $studentCourseToUser;
        
        return $this;
    }
            
    public function getIsEnrolled(): bool 
    {
        return (bool) $this->getStudentCourseToUser();
    }
    
    public function getEnrolledAt(): ?DateTime
    {
        $courseToUser = $this->getStudentCourseToUser();
        
        return $courseToUser ? $courseToUser->getCreatedAt() : null;
    }
            
    public function getCurrentLesson(): ?Lesson 
    {
        $courseToUser = $this->getStudentCourseToUser();
        
        try {
            return $courseToUser 
                && $courseToUser->getCurrentLesson() 
                && !$courseToUser->getCurrentLesson()->getDeletedAt() 
                ? $courseToUser->getCurrentLesson() 
                : null;
        } catch (EntityNotFoundException $exc) {
            return null;
        }
    }
        
    public function getLastLesson(): ?Lesson 
    {
        $courseToUser = $this->getStudentCourseToUser();
        
        try {
            return $courseToUser 
                && $courseToUser->getLastLesson()
                && !$courseToUser->getLastLesson()->getDeletedAt()
                ? $courseToUser->getLastLesson() 
                : null;
        } catch (EntityNotFoundException $exc) {
            return null;
        }
    }

    public function getIsFinished(): bool 
    {
        $courseToUser = $this->getStudentCourseToUser();
        
        return $courseToUser ? (bool) $courseToUser->getFinishedAt() : false;
    }
    
    public function getLessonsCount(): int 
    {
        $lessonCount = 0;
        foreach ($this->getSections() as $section) {
            if (!$section->getDeletedAt()) {
                $lessonCount += $section->getLessonsCount();
            }
        }
        return $lessonCount;
    }
    
    public function getDoneByPercents(): ?int
    {
        $courseToUser = $this->getStudentCourseToUser();
        
        return 
            $courseToUser 
            ? $this->calculateDoneByPercents($courseToUser) 
            : null;
    }
    
    protected function calculateDoneByPercents(CourseToUser $courseToUser): int
    {
        return $this->getLessonsCount() > 0
            ? round($courseToUser->getNumberOfFinishedLessons() / $this->getLessonsCount() * 100) 
            : 0;
    }
    
    public function getDoneByLessonsCount(): ?int
    {
        $courseToUser = $this->getStudentCourseToUser();
        
        return 
            $courseToUser 
            ? $courseToUser->getNumberOfFinishedLessons() 
            : null;
    }
    
    public function isCompletelyFinished()
    {
        return $this->getDoneByPercents() === 100;
    }
        
    /**
     * @Groups({"acl"})
     * 
     * @return bool
     */
    public function getIsAllowedToEnrollUsers(): bool
    {
        return $this->isAllowed(self::ACL_ACTION_ENROLL_USERS);
    }
}
