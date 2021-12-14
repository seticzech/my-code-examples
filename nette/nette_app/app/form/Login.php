<?php

namespace App\Form;

use Nette\Application\UI\Form;


class Login
{

    public function create()
    {
        $form = new Form(null, 'loginForm');

        $form->addText('username')
            ->setHtmlAttribute('oninput', "setCustomValidity('')")
            ->setHtmlAttribute('oninvalid', "setCustomValidity('Please fill username.')")
            ->setRequired('Please fill username.');
        $form->addPassword('password')
            ->setHtmlAttribute('oninput', "setCustomValidity('')")
            ->setHtmlAttribute('oninvalid', "setCustomValidity('Please fill password.')")
            ->setRequired('Please fill password.');

        return $form;
    }

}