<?php

namespace App\Presenters;

use App;
use Nette;


abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /**
     * @var App\Facade\Project
     * @inject
     */
    public $projectFacade;



    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            $this->redirect('Auth:login');
        }

        $this->projectFacade->filterByUser($this->user);
    }



    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->projects = $this->projectFacade->getAll();
    }

}
