<?php

/**
 * Odhlášení uživatele.
 * 
 * @author Jiří Pazderník <pazdernik.j@atlas.cz>
 */

class LogoutController extends Eal_Controller_Action 
{
	
	/**
	 * Provede odhlášení uživatele, uzavření sessions a přesměrování na domovskou stránku
	 *
	 * @return void
	 */
	public function indexAction()
	{
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$auth->clearIdentity();
			Zend_Session::writeClose();
		}
		$this->_helper->redirector('index', 'index');
	}
	
}
