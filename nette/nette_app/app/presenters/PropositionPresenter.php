<?php

namespace App\Presenters;

use App;
use App\Form\Proposition as PropositionForm;
use Nette\Application\UI\Form;
use stdClass;


class PropositionPresenter extends BasePresenter
{

    /**
     * @var string[]
     */
    protected $duration = [
        '1' => '1 Year',
        '3' => '3 Years',
    ];


    protected $customerType = [
        '1' => 'Company',
        '2' => 'Individual',
    ];


    /**
     * @var array
     */
    protected $formDefaults;


    /**
     * @var App\Facade\Proposition
     */
    protected $propositionFacade;



    public function __construct(App\Facade\Proposition $propositionFacade)
    {
        $this->propositionFacade = $propositionFacade;
    }



    protected function createComponentPropositionForm(): Form
    {
        $form = (new PropositionForm())->create($this->duration, $this->customerType);
        $form->onSuccess[] = [$this, 'formSuceeded'];

        if ($this->formDefaults) {
            $form->setDefaults($this->formDefaults);
        }

        return $form;
    }



    public function formSuceeded(Form $form, stdClass $values)
    {
        $data = iterator_to_array($values);
        $projectId = $data['project_id'];

        switch ($this->getAction()) {
            case 'add':
                $project = $this->projectFacade->getProject($projectId);
                $proposition = $this->propositionFacade->create($data, $project);
                //(new App\NotifySlack())->newProposition($proposition);
                $this->redirect('list', $projectId);
                break;
            case 'edit':
                $proposition = $this->propositionFacade->update($data);
                //(new App\NotifySlack())->updatedProposition($proposition);
                $this->redirect('list', $projectId);
                break;
        }
    }



    public function renderAdd($projectId)
    {
        $project = $this->projectFacade->getProject($projectId);

        if (!$project) {
            $this->flashMessage(sprintf('Project with ID: \'%d\' not found.', $projectId));
            $this->redirect('Home:default');
        }

        $this->formDefaults = [
            'project_id' => $projectId
        ];

        $this->template->project = $project;
    }



    public function renderEdit($id)
    {
        $proposition = $this->propositionFacade->getProposition($id);
        $project = $this->projectFacade->getProject($proposition->getProjectId());

        if (!$proposition || !$project) {
            $this->flashMessage(sprintf('Proposition with ID: \'%d\' not found.', $id));
            $this->redirect('Home:default');
        }

        $this->formDefaults = $proposition->serialize();
        $this->template->project = $project;


    }



    public function renderList($projectId)
    {
        $project = $this->projectFacade->getProject($projectId);

        if (!$project) {
            $this->flashMessage(sprintf('Project with ID: \'%d\' not found.', $projectId));
            $this->redirect('Home:default');
        }

        $this->template->duration = $this->duration;
        $this->template->project = $project;
        $this->template->propositions = $this->propositionFacade->getByProjectId($projectId);
    }



    public function actionRemove($id)
    {
        $proposition = $this->propositionFacade->getProposition($id);
        $project = $this->projectFacade->getProject($proposition->getProjectId());

        if (!$proposition || !$project) {
            $this->flashMessage(sprintf('Proposition with ID: \'%d\' not found.', $id));
            $this->redirect('Home:default');
        }

        //(new App\NotifySlack())->removedProposition($proposition);

        $this->propositionFacade->remove($proposition);

        $this->redirect('list', $proposition->getProjectId());
    }

}
