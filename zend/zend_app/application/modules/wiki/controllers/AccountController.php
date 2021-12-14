<?php

/**
 * Práce s uživatelskými účty
 * 
 * @author Jiří Pazderník <pazdernik.j@atlas.cz>
 */
class Wiki_AccountController extends Eal_Controller_Action
{
	
	/**
	 * Základní akce
	 * 
	 * Přesměruje na akci 'list'
	 *
	 * @return void
	 */
	public function indexAction()
	{
		$this->_helper->redirector('list');
	}
	
	/**
	 * Výpis všech nesystémových uživatelů
	 * 
	 * @return void
	 */
	public function listAction()
	{
		$page = $this->getRequest()->getParam('page', 1);
		
		$model = Core_Model_Sys_Users::getInstance();
		
		$records = $model->getRecords(Core_Model_Sys_Users::FILTER_LIST_NON_SYSTEM_USERS, 
			null, 'last_name', false);
		
		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Iterator($records));
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(15);
		
		$this->view->records = $paginator;
	}
	
	/**
	 * Přidání nového uživatele
	 * 
	 * @return void
	 */
	public function addAction()
	{
		$this->_helper->layout->setLayout('dialog');
		
		$this->view->success = false;
		$this->view->closeDialog = false;
		
		$ifrm = new Wiki_Form_Account();
		$form = $ifrm->formAdd(100);
		
		if ($this->getRequest()->isPost()) {
			$post = $this->getRequest()->getPost();
			
			// validace dat a uložení změn
			if ($form->isValid($post)) {
				$callback = true;
				
				// volání callback funkce, pokud je definována
				if (isset($form->callback)) {
					if (is_callable(array($form, $form->callback->getValue()))) {
						$callback = call_user_func(array($form, $form->callback->getValue()));
					}
				}
				
				// uložení dat
				if ($callback) {
					$model = Core_Model_Sys_Users::getInstance();
					$values = $form->getValues();
					
					// jako login je použit email uživatele
					$values['login'] = $values['email'];
					
					$model->addRecord($this->userIdentity, $values);
					
					if (isset($post['butSubmitNew'])) {
						$this->_helper->redirector('add');
					}
					
					$this->view->success = true;
					$this->view->closeDialog = true;
					return;
				}
			}
		}
		
		$this->view->form = $form;
	}
	
	/**
	 * Úprava existujícího uživatele
	 * 
	 * @return void
	 */
	public function editAction()
	{
		$this->_helper->layout->setLayout('dialog');
		//$this->_helper->viewRenderer->setNoRender();
		
		$this->view->success = false;
		$this->view->closeDialog = false;
		
		$userId = $this->getRequest()->getParam('id', null);
		
		if (null === $userId) {
			throw new Eal_Application_Exception('Unknown user, cannot edit account');
		}
		
		// tabulka
		$model = Core_Model_Sys_Users::getInstance();
		
		// vytvoření formuláře
		$ifrm = new Wiki_Form_Account();
		$form = $ifrm->formEdit($userId);
		
		if ($this->getRequest()->isPost()) {
			// validace dat a uložení změn
			if ($form->isValid($this->getRequest()->getPost())) {
				$callback = true;
				
				// volání callback funkce, pokud je definována
				if (isset($form->callback)) {
					if (is_callable(array($form, $form->callback->getValue()))) {
						$callback = call_user_func(array($form, $form->callback->getValue()));
					}
				}
				
				// uložení dat
				if ($callback) {
					$values = $form->getValues();
					
					// jako login je použit email uživatele
					$values['login'] = $values['email'];
					
					$model->updateRecord($userId, $values);
					
					$this->view->success = true;
					$this->view->closeDialog = true;
					return;
				}
			}
		} else {
			// naplnění formuláře daty
			$data = $model->getRecord($userId);
			if (!$data->hasData()) {
				throw new Eal_Application_Exception('User ID: ' . $userId . ' not found', 1);
			}
			// původní uživatelské jméno pro porovnání, zda došlo ke změně
			$data->email_old = $data->email;
			$form->populate($data->toArray());
		}
		
		$this->view->form = $form;
	}
	
	/**
	 * Zneplatnění uživatele
	 * 
	 * @return void
	 */
	public function deleteAction()
	{
		$m_sys_users = Core_Model_Sys_Users::getInstance();
			
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest() || $this->isAjaxRequest()) {
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			
			$json = $this->getRequest()->getParam('data', null);
			$json = stripslashes_deep($json);
			
			$params = Zend_Json::decode($json);
			
			foreach ($params as $p) {
				if ($m_sys_users->disableRecord($p['id']) == 0) {
					throw new Eal_Application_Exception('Cannot delete record ID: ' . $p['id'], 1);
				}
			}
		} else {
			$recordId = $this->getRequest()->getParam('id', null);
			
			if (null === $recordId) {
				throw new Eal_Application_Exception("Bad or missing requested parameter 'id'", 1);
			}
			
			if ($m_sys_users->disableRecord($recordId) == 0) {
				throw new Eal_Application_Exception("Cannot delete record ID: $recordId", 1);
			}
			
			$this->_helper->redirector('list');
		}
	}
	
	/**
	 * Obnovení zneplatněného uživatele
	 * 
	 * @return void
	 */
	public function restoreAction()
	{
		$m_sys_users = Core_Model_Sys_Users::getInstance();
			
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest() || $this->isAjaxRequest()) {
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			
			$json = $this->getRequest()->getParam('data', null);
			$json = stripslashes_deep($json);
			
			$params = Zend_Json::decode($json);
			
			foreach ($params as $p) {
				if ($m_sys_users->enableRecord($p['id']) == 0) {
					throw new Eal_Application_Exception('Cannot restore record ID: ' . $p['id'], 1);
				}
			}
		} else {
			$recordId = $this->getRequest()->getParam('id', null);
			
			if (null === $recordId) {
				throw new Eal_Application_Exception("Bad or missing requested parameter 'id'", 1);
			}
			
			if ($m_sys_users->enableRecord($recordId) == 0) {
				throw new Eal_Application_Exception("Cannot restore record ID: $recordId", 1);
			}
			
			$this->_helper->redirector('list');
		}
	}
	
	/**
	 * Export uživatelů jako CSV výpis
	 * 
	 * @return void
	 */
	public function exportAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$fileSource = TEMP_PATH . 'export.csv';
		$handle = fopen($fileSource, 'w');
		
		$headerArray = array(
			__('label:first_name'),
			__('label:last_name'),
			__('label:email'),
			__('label:address'),
			__('label:city'),
			__('label:zip'),
			__('label:country'),
			__('label:phone'),
			__('label:cell_phone'),
		);
		$header = implode(';', $headerArray) . "\n";
		fwrite($handle, $header);
		
		$m_sys_users = Core_Model_Sys_Users::getInstance();
		$records = $m_sys_users->getRecords($this->_roleId);
		
		foreach ($records as $row) {
			$itemArray = array(
				$row->first_name,
				$row->last_name,
				$row->email,
				$row->address,
				$row->city,
				$row->zip,
				$row->country,
				$row->phone,
				$row->cell_phone,
			);
			$item = implode(';', $itemArray) . "\n";
			fwrite($handle, $item);
		}
		
		fclose($handle);
		
		$group = $this->m_roles->getRecord($this->_roleId);
		$fileName = sprintf("%s.csv", str_ireplace(' ', '_', $group->name));
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . filesize($fileSource));
		
		$bytes = @readfile($fileSource);
		if ($bytes == 0) {
			echo 'Error when downloading file: ' . $fileName;
		}
	}
	
}

