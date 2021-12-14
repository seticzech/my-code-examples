<?php

namespace Backend\Module;

use \Backend\Model\Data\Payment as ModelBase,
	\Backend\Model\Data\Language as ModelLanguage,
	\Backend\Model\Data\Shipping as ModelShipping,
	\Backend\Model\Data\Store as ModelStore;

/**
 * Payment controller
 * 
 * @author Jiri Pazdernik <jiri@pazdernik.net>
 */ 
class PaymentController extends \Pel\Mvc\Controller
{
	
	// use traits
	use \Backend\Traits\Controller\BaseTrait,
		\Backend\Traits\Controller\StatusManageTrait;
	
	/**
	 * Initialization
	 *
	 * @return void
	 */
	public function initialize()
	{
		$this->__modelBase = ModelBase::getInstance();
		$this->setBaseViewParams();
	}
	
	/**
	 * Index action
	 *
	 * Redirect to the list action
	 *
	 * @return void
	 */
	public function indexAction()
	{
		$this->dispatcher->forward(array("action" => "list"));
	}
	
	/**
	 * Edit action
	 * 
	 * Edit specific payment system
	 *
	 * @return void
	 */
	public function editAction()
	{
		$id = $this->request->get("id", "int");
		
		if (null === $id) {
			return $this->redirectCancel("Neznámý nebo chybějící parametr");
		}
		
		$record = ModelBase::findFirst($id);
		if (false === $record) {
			return $this->redirectCancel("Položka nebyla nalezena");
		}
		
		// load additional JS
		$this->assets->addJs("javascript/admin/ckeditor/ckeditor.js");
		
		$this->view->title = "Úprava platebního systému '{$record->name}'";
		
		// load data
		$languages = ModelLanguage::find();
		$shippingsList = ModelShipping::getList(true);
		$storesList = ModelStore::getList();
		
		// create form
		$code = ucfirst(strtolower($record->code));
		$formClassName = "\Backend\Form\Module\Payment\\" . $code;
		if(class_exists($formClassName)) {
			$form = new $formClassName;
		} else {
			$form = new \Backend\Form\Module\Payment();
		}
		$form->createForUpdate($languages, $shippingsList, $storesList);
		
		if ($this->request->isPost()) {
			$post = $this->request->getPost();
			
			$postObject = new \Pel\Forms\PostObject($post);
			$form->getValidation()->bind($postObject, array());
			
			$form->bind($post, $record);
			
			if ($form->isValid()) {
				try {
					// try to save valid data
					if (true === $record->save($form->getValues())) {
						return $this->redirectCancel("Záznam '{$record->name}' byl upraven", self::$FLASH_MESSAGE_TYPE_SUCCESS);
					} else {
						$this->view->errorMessage = "Při úpravě záznamu v databázi došlo k chybě:";
						$this->view->errorMessages = $record->getMessages();
					}
				} catch (\Exception $e) {
					$this->view->errorMessage = "Při úpravě záznamu v databázi došlo k chybě: " . $e->getMessage();
				}
			} else {
				$this->view->errorMessage = "Formulář obsahuje chyby";
			}
		} else {
			// populate form
			$formData = $record->toArray();
			$formData["shippings"] = $record->getShippings();
			$formData["stores"] = $record->getStores();
			$formData["setting"] = $record->getSettings();
			$form->bind($formData, array());
		}
		
		$this->view->form = $form;
		$this->view->languages = $languages;
		
		$this->view->pick("payment/edit");
	}
	
	/**
	 * Insert action
	 * 
	 * Insert new payment system
	 *
	 * @return void
	 */
	public function insertAction()
	{
		$this->view->title = "Vytvoření nového platebního systému";
		
		// create form
		$form = new \Backend\Form\Module\Payment();
		$form->createForInsert();
		
		if ($this->request->isPost()) {
			$post = $this->request->getPost();
			
			$postObject = new \Pel\Forms\PostObject($post);
			$form->getValidation()->bind($postObject, array());
			
			if ($form->isValid()) {
				try {
					// try to create database record
					$newRecord = ModelBase::createRecord($form->getValues());
					if (true === $newRecord->getLastResult()) {
						$id = $newRecord->payment_id;
						$this->response->redirect($this->getCustomBaseRedirect("edit", array("id" => $id)));
						return $this->response->send();
					} else {
						$this->view->errorMessage = "Při vytváření záznamu v databázi došlo k chybě:";
						$this->view->errorMessages = $newRecord->getMessages();
					}
				} catch (\Exception $e) {
					$this->view->errorMessage = "Při vytváření záznamu v databázi došlo k chybě: " . $e->getMessage();
				}
			} else {
				$this->view->errorMessage = "Formulář obsahuje chyby";
			}
		}
		
		$this->view->form = $form;
		$this->view->languages = $languages;
		
		$this->view->pick("payment/insert");
	}
	
	/**
	 * List action
	 * 
	 * List all payment systems
	 *
	 * @return void
	 */
	public function listAction()
	{
		$listItems = ModelBase::find();
		
		$this->view->title = "Platební systémy";
		$this->view->listItems = $listItems;
		
		$this->view->pick("payment/list");
	}
	
	/**
	 * Refund action
	 * 
	 * Refund amount to the customer according to payment system
	 *
	 * @return void
	 */
	public function refundAction()
	{
		// ID of the payment system
		$id = $this->request->get("id", "int");
		
		if (null === $id) {
			return $this->redirectCancel("Neznámý nebo chybějící parametr");
		}
		
		if ($this->request->isPost()) {
			$commissionId = $this->request->get("commission_id");
			$refundId = $this->request->get("refund_id");
			if (! empty($commissionId)) {
				if (null !== $refundId) {
					// cancel refund
					try {
						\Asl\Model\Data\Module\Payment\PosmerchantApi::refundToProcessCancel($refundId);
					} catch (\Exception $e) {
						$this->view->errorMessage = $e->getMessage();
					}
				} else {
					try {
						\Asl\Model\Data\Module\Payment\PosmerchantApi::refund($commissionId);
					} catch (\Exception $e) {
						$this->view->errorMessage = $e->getMessage();
					}
				}
			}
		}
		
		// generate the list of possible refunds
		$listItems = \Backend\Model\Data\Module\Payment\PosmerchantApi::findForRefund($id);
		// generate the list of refunds waiting to process by payment company
		$waitItems = \Backend\Model\Data\Module\Payment\PosmerchantApi::findRefundsWaitingForProcess();
		// generate the list of processed refunds wating for settle
		$processedItems = \Backend\Model\Data\Module\Payment\PosmerchantApi::findRefundsWaitingForSettle();
		// generate the list of refunds history
		$doneItems = \Backend\Model\Data\Module\Payment\PosmerchantApi::findRefunds();
		
		$this->view->title = "Vrácení peněz na kartu";
		$this->view->listItems = $listItems;
		$this->view->waitItems = $waitItems;
		$this->view->processedItems = $processedItems;
		$this->view->doneItems = $doneItems;
		
		$this->view->pick("payment/refund");
	}
	
	/**
	 * Localize action
	 * 
	 * Edit localization for specific payment system
	 *
	 * @return void
	 */
	public function localizeAction()
	{
		// ID of the payment system
		$id = $this->request->get("id", "int");
		
		if (null === $id) {
			return $this->redirectCancel("Neznámý nebo chybějící parametr");
		}
		
		$record = ModelBase::findFirst($id);
		if (false === $record) {
			return $this->redirectCancel("Položka nebyla nalezena");
		}
		
		$this->view->title = "Úprava lokalizace platebního systému '{$record->name}'";
		
		// load additional JS
		$this->assets->addJs("javascript/admin/ckeditor/ckeditor.js");
		
		// load data
		$languages = ModelLanguage::find();
		
		// create form
		$code = ucfirst(strtolower($record->code));
		$formClassName = "\Backend\Form\Module\Payment\\" . $code;
		if(class_exists($formClassName)) {
			$form = new $formClassName;
		} else {
			$form = new \Backend\Form\Module\Payment();
		}
		$form->createForLocalize($languages);
		
		if ($this->request->isPost()) {
			$post = $this->request->getPost();
			
			$postObject = new \Pel\Forms\PostObject($post);
			$form->getValidation()->bind($postObject, array());
			
			$form->bind($post, $record);
			
			if ($form->isValid()) {
				// try to save valid data
				try {
					$record->saveDescription($form->getValues());
					return $this->redirectCancel("Záznam '{$record->name}' byl upraven", self::$FLASH_MESSAGE_TYPE_SUCCESS);
				} catch (\Exception $e) {
					$this->view->errorMessage = "Při úpravě záznamu v databázi došlo k chybě: " . $e->getMessage();
				}
			} else {
				$this->view->errorMessage = "Formulář obsahuje chyby";
			}
		} else {
			$formData = $record->toArray();
			$formData["description"] = $record->getDescriptions();
			$form->bind($formData, array());
		}
		
		$this->view->form = $form;
		$this->view->languages = $languages;
		
		$this->view->pick("payment/edit");
	}
	
}
