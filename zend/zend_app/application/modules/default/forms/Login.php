<?php

/**
 * Formulář pro přihlášení.
 *
 * @author Jiří Pazderník <pazdernik.j@atlas.cz>
 */
class Default_Form_Login extends Eal_Form_Noerror_Basic 
{
	
	const ELEMENT_LOGIN_NAME = 'login_login';
	const ELEMENT_PASSWORD_NAME = 'login_password';
	
	public $elementDecoratorsLogin = array(
		array('ViewHelper'),
		array('Description', array('placement' => 'append', 'class' => 'formHint')),
		array('Label', array('placement' => 'prepend', 'class' => 'login')),
	);
	
	public $elementDecoratorsPassword = array(
		array('ViewHelper'),
		array('Description', array('placement' => 'append', 'class' => 'formHint')),
		array('Label', array('placement' => 'prepend', 'class' => 'password')),
	);
	
	protected $_elementDecoratorsSmall = array(
		array('ViewHelper'),
		array('Errors', array('placement' => 'append')),
		array('Label', array('tag' => 'div', 'class' => 'form-label')),
		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'form-element')),
		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	);
	
	public $_buttonDecoratorsSmall = array(
		'ViewHelper',
		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'form-button')),
		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	);
	
	/**
	 * Vytvoření prvků formuláře.
	 *
	 */
	public function init()
	{
		$this->setAction('/login');
		
		$username = new Zend_Form_Element_Text(self::ELEMENT_LOGIN_NAME);
		$username->setLabel(__('label:default_username'))
			->addErrorMessage(__('validate:default_isEmptyUsername'))
			->setRequired(true)
			->setDecorators($this->elementDecoratorsLogin);
				 
		$password = new Zend_Form_Element_Password(self::ELEMENT_PASSWORD_NAME);
		$password->setLabel(__('label:default_password'))
			->setRequired(true)
			->setDecorators($this->elementDecoratorsPassword);
		
		$submit = new Zend_Form_Element_Submit('butLogin');
		$submit->setLabel(__('button:default_login'));
		
		$this->addElements(array($username, $password, $submit));
		
		$submit->setDecorators($this->buttonDecorators);
	}
	
	public function login($action = null)
	{
		if (null !== $action) {
			$this->setAction($action);
		}
		return $this;
	}
	
	/**
	 * Použití odlišných dekoratérů pro menší formulář.
	 *
	 * @return FormLogin
	 */
	public function small()
	{
		$this->setElementDecorators($this->_elementDecoratorsSmall);
		$this->getElement('submit')->setDecorators($this->_buttonDecoratorsSmall);
		return $this;
	}
}
