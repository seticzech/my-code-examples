<?php

namespace App\Form;

use Nette\Application\UI\Form;


class Proposition
{

    public function create(array $duration, array $customerType): Form
    {
        $form = new Form(null,  'propositionForm');

        $form->addHidden('id');
        $form->addHidden('project_id');
        $form->addHidden('active_at')
            ->setDefaultValue(0);
        $form->addHidden('deleted')
            ->setDefaultValue(0);

        $form->addText('name')
            ->setRequired('Please fill product name')
            ->setHtmlAttribute('oninput', "setCustomValidity('')")
            ->setHtmlAttribute('oninvalid', "setCustomValidity('Please fill product name.')");
        $form->addText('proposition_id')
            ->setRequired('Please fill proposition ID')
            ->setHtmlAttribute('oninput', "setCustomValidity('')")
            ->setHtmlAttribute('oninvalid', "setCustomValidity('Please fill proposition ID.')");
        $form->addText('product_id')
            ->setRequired('Please fill product ID')
            ->setHtmlAttribute('oninput', "setCustomValidity('')")
            ->setHtmlAttribute('oninvalid', "setCustomValidity('Please fill product ID.')");
        $form->addText('article_elk_id')
            ->setRequired('Please fill Article Elk ID')
            ->setHtmlAttribute('oninput', "setCustomValidity('')")
            ->setHtmlAttribute('oninvalid', "setCustomValidity('Please fill Article Elk ID.')");
        $form->addText('article_gas_id')
            ->setRequired('Please fill Article Gas ID')
            ->setHtmlAttribute('oninput', "setCustomValidity('')")
            ->setHtmlAttribute('oninvalid', "setCustomValidity('Please fill Article Gas ID.')");
        $form->addRadioList('duration', null, $duration)
            ->setRequired('Please select duration')
            ->setHtmlAttribute('onclick', "clearCustomValidity('duration')")
            ->setHtmlAttribute('oninvalid', "setCustomValidity('Please select duration.')");
        $form->addRadioList('customer_type', null, $customerType)
            ->setRequired('Please select customer type')
            ->setHtmlAttribute('onclick', "clearCustomValidity('customer_type')")
            ->setHtmlAttribute('oninvalid', "setCustomValidity('Please select customer type.')");

        //$form->addCheckbox('active_for_period');

        //$form->addText('active_from');
        //$form->addText('active_to');

        return $form;
    }

}