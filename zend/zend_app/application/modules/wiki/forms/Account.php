<?php

/**
 * Formulář pro správu uživatelských účtu
 *
 * @author Jiří Pazderník <pazdernik.j@atlas.cz>
 */
class Wiki_Form_Account extends Eal_Form_Noerror_Basic
{
	
	public function init()
	{
		parent::init();
		
		$m_countries = Core_Model_Codelist_Countries::getInstance();
		$countriesOpts = $m_countries->listRecords();
		
		$title = new Zend_Form_Element_Text('title');
		$title->setLabel(__('label:title_before'));
		
		$email = new Zend_Form_Element_Text('email');
		$email->setLabel(__('label:email'))
			->setRequired(true)
			->addValidator(new Zend_Validate_EmailAddress());
		
		$password1 = new Zend_Form_Element_Password('password');
		$password1->setLabel(__('label:password'));
		
		$password2 = new Zend_Form_Element_Password('password_retype');
		$password2->setLabel(__('label:password_retype'))
			->addValidator(new Eal_Validate_MatchPassword())
			->setIgnore(true); // hodnota nebude obsažena při $form->getValues()
		
		$first_name = new Zend_Form_Element_Text('first_name');
		$first_name->setLabel(__('label:first_name'));
		
		$last_name = new Zend_Form_Element_Text('last_name');
		$last_name->setLabel(__('label:last_name'));
		
		$address = new Zend_Form_Element_Text('address');
		$address->setLabel(__('label:address'));
		
		$city = new Zend_Form_Element_Text('city');
		$city->setLabel(__('label:city'));
		
		$zip = new Zend_Form_Element_Text('zip');
		$zip->setLabel(__('label:zip'));
		
		$country = new Zend_Form_Element_Select('country');
		$country->setLabel(__('label:country'))
			->setMultiOptions($countriesOpts);
		
		$cell_phone = new Zend_Form_Element_Text('cell_phone');
		$cell_phone->setLabel(__('label:cell_phone'));
		
		$phone = new Zend_Form_Element_Text('phone');
		$phone->setLabel(__('label:phone'));
		
		$fax = new Zend_Form_Element_Text('fax');
		$fax->setLabel(__('label:fax'));
		
		$comment = new Zend_Form_Element_Textarea('comment');
		$comment->setLabel(__('label:notes'))
			->setAttrib('rows', '4');
		
		$butSubmit = new Zend_Form_Element_Submit('butSubmit');
		$butSubmit->setIgnore(true);
		
		$this->addElements(array(
			$email,
			$password1,
			$password2,
			$title,
			$first_name,
			$last_name,
			$address,
			$city,
			$zip,
			$country,
			$cell_phone,
			$phone,
			$fax,
			$comment,
			$butSubmit
		));
		
		$this->setElementDecorators($this->elementDecorators);
		//$username->setDecorators($this->elementDecoratorsUser);
		$butSubmit->setDecorators($this->buttonDecorators);
		
		$callback = new Zend_Form_Element_Hidden('callback');
		$callback->setValue('checkUsername')
			->setDecorators($this->elementDecoratorsHidden)
			->setIgnore(true);
		
		$this->addElement($callback);
	}
	
	public function checkUsername()
	{
		$userName = $this->getElement('email');
		$userNameOld = $this->getElement('email_old');
		
		if (null !== $userNameOld) {
			// pokud se jméno nezměnilo, není potřeba nic kontrolovat
			if (strcmp($userName->getValue(), $userNameOld->getValue()) == 0) {
				return true;
			}
		}
		
		$m_users = Core_Model_Sys_Users::getInstance();
		$userId = (isset($this->id_user)) ? $this->id_user->getValue() : 0;
		
		if (!$m_users->checkUsername($userName, $userId)) {
			$this->login->addError(__('validate:usernameAlreadyExists'));
			return false;
		}
		
		return true;
	}
	
	public function formAdd($roleId)
	{
		$this->setAction('/wiki/account/add');
		
		$this->butSubmit->setLabel(__('button:add'));
		
		$this->password->setRequired(true);
		$this->password_retype->setRequired(true);
		
		$id_role = new Zend_Form_Element_Hidden('id_role');
		$id_role->setValue($roleId)
			->setDecorators($this->elementDecoratorsHidden);
		
		$butSubmitNew = new Zend_Form_Element_Submit('butSubmitNew');
		$butSubmitNew->setLabel(__('button:add_and_new'))
			->setIgnore(true)
			->setDecorators($this->buttonDecorators);
		
		$this->addElements(array(
			$butSubmitNew,
			$id_role
		));
		
		$butSubmitNew->setDecorators($this->buttonDecorators);
				
		$this->_setClasses();
		
		return $this;
	}
	
	/**
	 * Nastavení formuláře pro editaci uživatele.
	 * 
	 * @param integer $userId ID uživatele
	 * @return FormUser
	 */
	public function formEdit($userId)
	{
		$this->setAction('/wiki/account/edit/' . $userId);
		
		$this->butSubmit->setLabel(__('button:save'));
		
		$id_user = new Zend_Form_Element_Hidden('id_user');
		$id_user->setValue($userId)
			->setIgnore(true)
			->setDecorators($this->elementDecoratorsHidden);
		
		$login_old = new Zend_Form_Element_Hidden('login_old');
		$login_old->setIgnore(true)
			->setDecorators($this->elementDecoratorsHidden);
		
		$this->addElements(array(
			$id_user,
			$login_old,
		));
		
		$this->_setClasses();
		
		return $this;
	}
	
}
