<?php

/**
 * Přihlášení a autentifikace uživatele.
 * 
 * @author Jiří Pazderník <pazdernik.j@atlas.cz>
 */
class LoginController extends Eal_Controller_Action 
{
	
	/**
	 * Flash messenger pro předání zpráv do view.
	 *
	 * @var Zend_Controller_Action_Helper_FlashMessenger
	 */
	protected $_flash = null;
	
	/**
	 * Přihlašovací formulář.
	 *
	 * @var Eal_Form
	 */
	protected $_form = null;
	
	/**
	 * Inicializace kontroléru.
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();
		$this->_helper->layout->setLayout('login');
		
		$this->_flash = $this->_helper->getHelper('FlashMessenger');
		$this->_form = new Default_Form_Login();
	}
	
	/**
	 * Hlavní akce, zobrazí formulář, nebo zavolá metodu pro ověření uživatele.
	 *
	 * @return void
	 */
	public function indexAction()
	{
		if ($this->getRequest()->isPost()) {
			$this->autenticate();
		}
		$this->view->form = $this->_form;
	}
	
	/**
	 * Provede ověření uživatele a přesměruje na danou akci.
	 *
	 * @return boolean false při nevalidním formuláři
	 */
	protected function autenticate()
	{
		$request = $this->getRequest()->getPost();
		if (!$this->_form->isValid($request)) {
			return;
		}
		$values = $this->_form->getValues();
		
		$authAdapter = new Eal_Auth_Adapter();
		$authAdapter->setIdentity($values[Default_Form_Login::ELEMENT_LOGIN_NAME])
					->setCredential($values[Default_Form_Login::ELEMENT_PASSWORD_NAME]);
		
		$auth = Zend_Auth::getInstance();
		$result = $auth->authenticate($authAdapter);
		if ($result->isValid()) {
			$data = $authAdapter->getResultRowObject(null, 'password');
			$auth->getStorage()->write($data);
			
			$table = Core_Model_Sys_Users::getInstance();
			$table->updateLoginInfo($data->id);
			
			$this->_helper->redirector('index', 'index');
		} else {
			$this->_flash->addMessage(__('error:default_bad_login_data'));
			$this->_helper->redirector('index');
		}
	}
}
