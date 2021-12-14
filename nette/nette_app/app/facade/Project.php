<?php

namespace App\Facade;

use stdClass;
use Nette;
use Nettrine\ORM\EntityManager;


class Project
{

    /**
     * @var array
     */
    protected $projects = [];



    public function __construct(string $file)
    {
        $this->setProjectsFromFile($file);
    }



    public function filterByUser(Nette\Security\User $user)
    {
        $this->projects = array_filter($this->projects, function($project) use ($user){
            return $user->isAllowed('account:' . $project->accountId);
        });
    }



    public function getAll(): array
    {
        return $this->projects;
    }



    /**
     * @param int $projectId
     * @return stdClass|null
     */
    public function getProject(int $projectId)
    {
        foreach ($this->projects as $project) {
            if ($project->projectId == $projectId) {
                return $project;
            }
        }

        return null;
    }



    protected function setProjectsFromFile($file): void
    {
        if (file_exists($file)) {
            $this->projects = json_decode(file_get_contents($file));
        }
    }

}