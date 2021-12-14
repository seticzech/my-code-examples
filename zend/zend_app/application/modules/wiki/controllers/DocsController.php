<?php

/**
 * Správa souborů - explorer
 * 
 * @author Jiří Pazderník <pazdernik.j@atlas.cz>
 */
class Wiki_DocsController extends Eal_Controller_Action
{
	
	const SESSION_CURRENT_FOLDER_ID = 'currentFolderId';
	const SESSION_SELECTED_TEMPLATES = 'selectedTemplates';
	
	public $vfs_plugin = null;
	
	public function init()
	{
		parent::init();
		
		$this->registerSessionNamespace(__CLASS__);
		
		$this->vfs_plugin = Core_Plugin_Vfs::getInstance();
	}
	
	/**
	 * Šablona pro zobrazení položek
	 * 
	 * @param object $selectedFolder
	 * @return string
	 */
	protected function _getViewTemplateName($selectedFolder)
	{
		$folderId = $selectedFolder->id;
		$templates = $this->getSessionParam(self::SESSION_SELECTED_TEMPLATES, array());
		
		$templateName = $this->getRequest()->getParam('view', null);
		
		if ((null === $templateName) && isset($templates[$folderId])) {
			$templateName = $templates[$folderId];
		} else {
			$templates[$folderId] = $templateName;
			$this->setSessionParam(self::SESSION_SELECTED_TEMPLATES, $templates);
		}
		if (null === $templateName) {
			$templateName = $this->vfs_plugin->getInodeViewTemplate($folderId);
			$templates[$folderId] = $templateName;
			$this->setSessionParam(self::SESSION_SELECTED_TEMPLATES, $templates);
		}
		
		return $templateName;
	}
	
	/**
	 * Generování náhledu obrázků
	 * 
	 * @param integer $folderId
	 * @param Eal_Db_Result_Rowset $files
	 * @return void
	 */
	protected function _generateThumbnails($selectedFolder, $records)
	{
		$imageExt = array('gif', 'jpg', 'jpeg', 'png');
		$fullPath = $selectedFolder->base_path;
		
		foreach ($records as $rec) {
			$pathInfo = $rec->path_info->toArray();
			
			// náhled pro soubor s příponou uvedenou v $imageExt
			if (in_array(strtolower($pathInfo['extension']), $imageExt)) {
				$tmbnName = sprintf('%d.tmbn.%s', $rec->id, 'jpg');
				$sourceName = $fullPath . $rec->real_name;
			} else {
				$tmbnName = sprintf('inodetype_%d.tmbn.%s', $rec->node_type->id, 'jpg');
				$sourceName = ROOT_PATH . $rec->node_type->icon;
			}
			
			if (!file_exists($sourceName)) {
				$sourceName = ROOT_PATH . 'public/img/ico/vfs/ico_file.png';
			}
			zdtofile($rec->toArray());
			zdToFile(($sourceName));
			if (!file_exists(TMBS_PATH . $tmbnName)) {
				require_once 'phpThumb/phpthumb.class.php';
				$thumb = new phpthumb();
				$thumb->setSourceFilename($sourceName);
				$thumb->setParameter('w', 100);
				$thumb->setParameter('h', 100);
				$thumb->setParameter('bg', 'ffffff');
				$thumb->setParameter('bc', 'cccccc');
				$thumb->setParameter('far', 'C');
				$thumb->setParameter('fltr', "bord|1|0|0|cccccc");
				
				if ($thumb->GenerateThumbnail()) {
					if (!$thumb->RenderToFile(TMBS_PATH . $tmbnName)) {
						throw new Eal_Application_Exception("Error when creating thumbnail for file: '{$rec->name}'", 1);
					}
				}
			}
			
			$rec->thumbnail = TMBS_URL_PATH . $tmbnName;
		}
		$records->rewind();
	}
	
	/**
	 * Připrava šablony pro zobrazení
	 * 
	 * @param Eal_Countable $selectedFolder
	 * @param Eal_Countable $records
	 * @return string
	 */
	protected function _prepareForView($selectedFolder, $records)
	{
		$templateName = $this->_getViewTemplateName($selectedFolder);
		
		if (Core_Plugin_Vfs::VIEW_TEMPLATE_THUMBNAILS == $templateName) {
			$this->_generateThumbnails($selectedFolder, $records);
		}
		
		// šablona pro trash
		if ($selectedFolder->id == Core_Plugin_Vfs::INODE_TRASH_ID) {
			$templateName = 'trash-' . $templateName;
		}
		
		return $templateName . '.phtml';
	}
	
	/**
	 * Základní akce
	 *
	 * Provede přesměrování na akci 'get'
	 *
	 * @return void
	 */
	public function indexAction()
	{
		$this->_helper->redirector('get');
	}
	
	
	/**
	 * Výpis aktuálního adresáře do zvolené šablony exploreru
	 *
	 * @return void
	 */
	public function getAction()
	{
		$folderId = $this->getRequest()->getParam('id', 
			$this->getSessionParam(self::SESSION_CURRENT_FOLDER_ID));
		
		// kontrola aktuálního adresáře, zda nebyl smazán
		if ((null !== $folderId) && (Core_Plugin_Vfs::INODE_TRASH_ID !== $folderId)) {
			if (!$this->vfs_plugin->isTreeSelectableNode($folderId)) {
				$folderId = null;
			}
		}
		
		// zvolení prvního oddílu, nebo trash, pokud není definován žádný oddíl
		if (null === $folderId) {
			$partitions = $this->vfs_plugin->getPartitions();
			$folderId = $partitions->current()->id_root;
		}
		
		$this->setSessionParam(self::SESSION_CURRENT_FOLDER_ID, $folderId);
		
		// načtení stromu
		$folderTree = $this->vfs_plugin->buildSystemTree($folderId, true);
		
		// aktuální adresář
		$selectedFolder = $this->vfs_plugin->getInodeEx($folderId);
		
		// výpis položek
		$records = $this->vfs_plugin->buildInodesListEx($folderId);
		
		// šablona pro zvolený adresář a generování náhledů
		$templateName = $this->_prepareForView($selectedFolder, $records);
		
		$this->view->folderTree = $folderTree;
		$this->view->selectedFolder = $selectedFolder;
		$this->view->records = $records;
		$this->view->template = $templateName;
	}
	
	/**
	 * Zvolení aktuálního adresáře
	 *
	 * @return void
	 */
	public function selectAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$folderId = $this->getRequest()->getParam('id', null);
		$this->setSessionParam(self::SESSION_CURRENT_FOLDER_ID, $folderId);
		
		// aktuální adresář
		$selectedFolder = $this->vfs_plugin->getInodeEx($folderId);
		
		// výpis položek
		$records = $this->vfs_plugin->buildInodesListEx($folderId);
		
		// šablona pro zvolený adresář a generování náhledů
		$templateName = $this->_prepareForView($selectedFolder, $records);
		
		$this->view->selectedFolder = $selectedFolder;
		$this->view->records = $records;
		
		$view = $this->view->render('docs/' . $templateName);
		echo $view;
		
		exit (0);
	}
	
	/**
	 * Vytvoření položky
	 * 
	 * @return unknown_type
	 */
	public function addAction()
	{
		$this->_helper->layout->setLayout('dialog');
		$this->_helper->viewRenderer->setNoRender();
		
		$folderId = $this->getSessionParam(self::SESSION_CURRENT_FOLDER_ID);
		$itemType = $this->getRequest()->getParam('type', 'folder');
		
		$this->view->success = false;
		$this->view->closeDialog = false;
		
		$formClassName = 'Wiki_Form_Vfs_Add_' . ucfirst($itemType);
		$templateName = 'add-' . $itemType . '.phtml';
		
		if (!class_exists($formClassName)) {
			throw new Eal_Application_Exception("Cannot find form '$formClassName'");
		}
		
		$ifrm = new $formClassName;
		$form = $ifrm->formAdd($itemType);
		
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
					$values = $form->getValues();
					
					// typ vytvářené položky
					switch ($itemType) {
						case 'wiki':
							$data = array(
								'title' => $values['name']
							);
							$newPageId = Wiki_Model_Pages::getInstance()->addRecord($data);
							$newId = $this->vfs_plugin->createWikiPage($folderId, $values, $newPageId);
							break;
						case 'folder':
						default:
							$newId = $this->vfs_plugin->createFolder($folderId, $values);
							break;
					}
					
					$createdItem = $this->vfs_plugin->getInode($newId);
					
					// aktuální adresář
					$selectedFolder = $this->vfs_plugin->getInodeEx($folderId);
					
					// výpis položek
					$records = $this->vfs_plugin->buildInodesListEx($folderId);
					
					// šablona pro zvolený adresář a generování náhledů
					$templateName = $this->_prepareForView($selectedFolder, $records);
					
					$this->view->selectedFolder = $selectedFolder;
					$this->view->records = $records;
					
					$newContent = $this->view->render('docs/' . $templateName);
					
					$this->view->success = true;
					$this->view->closeDialog = true;
					$this->view->createdItem = $createdItem;
					$this->view->newContent = $newContent;
					
					return;
				}
			}
		}
		
		$this->view->form = $form;
		
		echo $this->view->render('docs/' . $templateName);
	}
	
	/**
	 * Odstranění položky do koše; AJAX
	 *
	 * @return void
	 */
	public function deleteAction()
	{
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest() || $this->isAjaxRequest()) {
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			
			$json = $this->getRequest()->getParam('data', null);
			$json = stripslashes_deep($json);
			
			$params = Zend_Json::decode($json);
			
			foreach ($params as $p) {
				if ($this->vfs_plugin->deleteInode($p['id']) == 0) {
					throw new Eal_Application_Exception('Cannot delete record ID: ' . $p['id'], 1);
				}
			}
		} else {
			throw new Zend_Controller_Action_Exception('Action "delete" is an AJAX action', 404);
		}
	}
	
	/**
	 * Stažení položky (souboru)
	 *
	 * @return void
	 */
	public function downloadAction()
	{
		$id = $this->getRequest()->getParam('id', null);
		
		if ($id) {
			$this->_helper->layout->disableLayout();
			
			$inode = $this->vfs_plugin->getInode($id);
			
			$filesource = $inode->base_path . $inode->real_name;
			$filename = $inode->name;
			$filesize = $inode->size;
			
			$this->view->filesource = $filesource;
			$this->view->filename = $filename;
			$this->view->filesize = $filesize;
		}
	}
	
	/**
	 * Výpis adresářového stromu
	 *
	 * @return void
	 */
	public function listAction()
	{
		$this->_helper->layout->setLayout('dialog');
		
		$folderId = $this->getSessionParam(self::SESSION_CURRENT_FOLDER_ID);
		
		// načtení stromu
		$folderTree = $this->vfs_plugin->buildSystemTree($folderId, true, true, false);
		
		$this->view->headMessage = __('label:select_target_folder');
		$this->view->folderTree = $folderTree;
	}
	
	/**
	 * Přemístění položky/položek do jiného adresáře; AJAX
	 *
	 * @return void
	 */
	public function moveAction()
	{
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest() || $this->isAjaxRequest()) {
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			
			$json = $this->getRequest()->getParam('data', null);
			$json = stripslashes_deep($json);
			
			$params = Zend_Json::decode($json);
			
			$targetId = $params['target'];
			unset($params['target']);
			
			foreach ($params as $p) {
				$data = array(
					'id_parent' => $targetId
				);
				
				$this->m_inodes->updateRecord($this->userIdentity, $p['id'], $data);
			}
		} else {
			throw new Zend_Controller_Action_Exception('Action "move" is an AJAX action', 404);
		}
	}
	
	/**
	 * Úprava vlatností položky
	 *
	 * @return void
	 */
	public function propertiesAction()
	{
		$this->_helper->layout->setLayout('dialog');
		$this->_helper->viewRenderer->setNoRender();
		
		$this->view->success = false;
		$this->view->closeDialog = false;
		
		$itemId = $this->getRequest()->getParam('id', null);
		
		if (null === $itemId) {
			throw new Eal_Application_Exception("Bad or missing requested parameter 'id'", 0);
		}
		
		$mainData = $this->vfs_plugin->getInodeInfo($itemId);
		//
		// vytvoření formuláře
		$ifrm = new Wiki_Form_Vfs_Properties();
		$form = $ifrm->formProperties($mainData);
		
		if ($this->getRequest()->isPost()) {
			$post = stripslashes_deep($this->getRequest()->getPost());
			
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
					$values = $form->getValues();
					
					$this->vfs_plugin->renameInode($itemId, $values['name']);
					
					$this->view->success = true;
					$this->view->closeDialog = true;
					//$this->view->successMessage = __('label:record_updated');
					return;
				}
			}
		} else {
			// naplnění formuláře daty
			$form->populate($mainData->toArray());
		}
		
		switch ($mainData->entry_type) {
			case Core_Plugin_Vfs::INODE_ENTRY_TYPE_WIKI:
				$templateName = 'docs/properties-wiki.phtml';
				break;
			case Core_Plugin_Vfs::INODE_ENTRY_TYPE_FILE:
				$templateName = 'docs/properties-file.phtml';
				break;
			case Core_Plugin_Vfs::INODE_ENTRY_TYPE_ROOT:
			case Core_Plugin_Vfs::INODE_ENTRY_TYPE_DIRECTORY:
			default:
				$templateName = 'docs/properties-folder.phtml';
				break;
		}
		
		$this->view->record = $mainData;
		$this->view->form = $form;
		
		echo $this->view->render($templateName);
	}
	
	/**
	 * Úplné odstranění položky/položek ze systému
	 *
	 * @return void
	 */
	public function removeAction()
	{
		$folderId = $this->getSessionParam(self::SESSION_CURRENT_FOLDER_ID);
		
		$current = $this->m_inodes->getRecord($folderId);
		$folders = $this->m_inodes->getRecords($current->id_parent, Core_Model_Vfs_Inodes::INODE_TYPE_DIRECTORY);
		
		if ($folders->count() == 1) {
			$folderId = $current->id_parent;
		} else {
			foreach ($folders as $f) {
				if ($f->id == $folderId) {
					break;
				}
			}
			$pos = $folders->position();
			if ($pos == $folders->count() - 1) {
				$folders->seek($pos - 1);
			} else {
				$folders->next();
			}
			$folderId = $folders->current()->id;
		}
		
		$this->setSessionParam(self::SESSION_CURRENT_FOLDER_ID, $folderId);
		
		$this->m_inodes->deleteRecord($this->userIdentity, $current->id);
		
		$this->_helper->redirector('get');
	}
	
	/**
	 * Přejmenování položky, AJAX
	 *
	 * @return void
	 */
	public function renameAction()
	{
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest() || $this->isAjaxRequest()) {
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			
			$id = $request->getParam('id', null);
			$name = $request->getParam('name', null);
			
			if (null === $id) {
				throw new Eal_Application_Exception("Bad requested parameter 'id'", 1);
			}
			if (null === $name) {
				throw new Eal_Application_Exception("Bad requested parameter 'name'", 1);
			}
			
			$data = array(
				'name' => $name,
			);
			
			$this->m_inodes->updateRecord($this->userIdentity, $id, $data);
		} else {
			throw new Zend_Controller_Action_Exception('Action "rename" is an AJAX action', 404);
		}
	}
	
	/**
	 * Obnovení položky/položek z koše; AJAX
	 *
	 * @return void
	 */
	public function restoreAction()
	{
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest() || $this->isAjaxRequest()) {
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			
			$json = $this->getRequest()->getParam('data', null);
			$json = stripslashes_deep($json);
			
			$params = Zend_Json::decode($json);
			
			foreach ($params as $p) {
				if ($this->vfs_plugin->restoreInode($p['id']) == 0) {
					throw new Eal_Application_Exception('Cannot restore record ID: ' . $p['id'], 1);
				}
			}
		} else {
			throw new Zend_Controller_Action_Exception('Action "restore" is an AJAX action', 404);
		}
	}
	
	/**
	 * Vytvoření nové položky z nahraného obsahu; AJAX
	 *
	 * @return void
	 */
	public function uploadAction()
	{
		if ($this->getRequest()->isPost()) {
			$folderId = $this->getSessionParam(self::SESSION_CURRENT_FOLDER_ID);
			
			if ($this->vfs_plugin->uploadFiles($folderId)) {
				exit (0);
			}
			
			header("HTTP/1.1 500 Internal Server Error");
			echo 'Error when uploading file: ' . $fileName;
			exit (1);
		} else {
			throw new Eal_Application_Exception('Action "upload" is an AJAX action', 404);
		}
	}
	
}

