<?php

namespace App\Presenters;

use App\Form\Login as LoginForm;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;


class AuthPresenter extends Nette\Application\UI\Presenter
{

    /**
     * @var Nette\Security\SimpleAuthenticator
     */
    protected $authenticator;



    public function __construct(Nette\Security\SimpleAuthenticator $authenticator)
    {
        parent::__construct();

        $this->authenticator = $authenticator;
    }



    protected function createComponentLoginForm($name)
    {
        $form = (new LoginForm())->create();
        $form->onSuccess[] = [$this, 'formSuceeded'];

        return $form;
    }



    public function formSuceeded(Form $form, $values)
    {
        try {
            $this->user->setAuthenticator($this->authenticator);
            $this->user->login($values->username, $values->password);
        } catch (AuthenticationException $e) {
            $this->flashMessage('You entered bad credentials');
            $this->redirect('Auth:login');
        }

        $this->redirect('Home:default');
    }



    public function loginRender()
    {

    }



    public function actionLogout()
    {
        if ($this->user->isLoggedIn()) {
            $this->user->logout();
        }

        $this->redirect('Auth:login');
    }

}
