<?php

namespace Frontend\Model\Module\Payment;

use \KupNajisto\KupNajistoApi,
	\KupNajisto\KupNajistoException,
	\Frontend\Model\Data\Commission as ModelCommission,
	\Frontend\Model\Data\Commission\Item as ModelCommissionItem,
	\Frontend\Model\Data\Commission\Reseller as ModelCommissionReseller;

/**
 * KupNajisto API model
 * 
 * Model for processing KupNajisto payments
 * 
 * @author Jiri Pazdernik <jiri@pazdernik.net>
 */ 
class Kupnajisto extends \Frontend\Model\Module\Base
{
	
	/**
	 * Temp to store order data returned from KupNajistoApi
	 * 
	 * @var array
	 */
	private $__ordersTemp;
	
	/**
	 * Append invoice content to the existing order
	 * 
	 * @param integer $externalId external ID of the order
	 * @param string $content invoice content
	 * @param string $mimeType MIME type
	 * @param string $fileName file name for invoice
	 * @param \KupNajisto\KupNajistoApi $api (OPTIONAL) instance of API class; login credentials MUST BE SET
	 * @throws \KupNajisto\KupNajistoException
	 * @return \ArrayObject
	 */
	private function __appendInvoiceContent($externalId, $content, $mimeType, $fileName, $api = null)
	{
		if (null === $api) {
			$api = $this->getApiInstance();
		}
		
		$index = $api->storeOrderData(true);
		
		try {
			$api->addInvoiceContent($content, $mimeType, $fileName);
			$api->orderUpdate($externalId);
			$result = $api->send();
			
			$api->loadOrderData($index);
		} catch (KupNajistoException $e) {
			$api->loadOrderData($index);
			
			$this->logger->app->error("Kup Najisto - Error when trying to append invoice content to order '{$externalId}': " . $e->getMessage());
			throw $e;
		}
	}
	
	/**
	 * Append invoice content to the existing order
	 * 
	 * @param integer $externalId external ID of the order
	 * @param string $content invoice content
	 * @param string $mimeType MIME type
	 * @param string $fileName file name for invoice
	 * @throws \KupNajisto\KupNajistoException
	 * @return \ArrayObject
	 */
	public function appendInvoiceContent($externalId, $content, $mimeType, $fileName)
	{
		$this->logger->app->debug("Kup Najisto - Append invoice content to order '{$externalId}', MIME type: '{$mimeType}', file name: '{$fileName}'");
		
		$this->__appendInvoiceContent($externalId, $content, $mimeType, $fileName);
		
		$this->logger->app->debug("Kup Najisto - Append invoice content to order '{$externalId}' done");
		
		return $result;
	}
	
	/**
	 * Confirm order only
	 * 
	 * @param integer $externalId
	 * @param \KupNajisto\KupNajistoApi $api (OPTIONAL) instance of API class; login credentials MUST BE SET
	 * @throws KupNajistoException
	 * @return \ArrayObject
	 */
	private function __confirmOrder($externalId, $api = null)
	{
		if (null === $api) {
			$api = $this->getApiInstance();
		}
		
		$index = $api->storeOrderData(true);
		
		try {
			$api->orderUpdate($externalId);
			$result = $api->send();
			
			$api->loadOrderData($index);
		} catch (KupNajistoException $e) {
			$api->loadOrderData($index);
			
			$this->logger->app->error("Kup Najisto - Error when trying to confirm order '{$externalId}': " . $e->getMessage());
			throw $e;
		}
		
		return $result;
	}
	
	/**
	 * Try to confirm order and send mail to the customer
	 * 
	 * @param integer $externalId
	 * @param boolean $sendMail (OPTIONAL) if FALSE no mail will be sent
	 * @throws KupNajistoException
	 * @return \ArrayObject
	 */
	public function callbackProcess($externalId, $sendMail = true)
	{
		$this->logger->app->info("Kup Najisto - Trying to confirm order '{$externalId}'");
		
		$commres = \Frontend\Model\Data\Commission\Reseller::getInstance()->findFirstByResellerId(1, $externalId);
		if (false === $commres) {
			throw new KupNajistoException("Order with external ID '{$externalId}' not found in system");
		}
		
		// info data about external commission
		$commresData = (null === $commres->data) ? array() : $commres->data;
		$commresData["confirmation"] = 1;
		
		$commission = \Frontend\Model\Data\Commission::getInstance()->findFirst($commres->commission_id);
		if (false === $commission) {
			throw new KupNajistoException("Commission with ID '{$commres->commission_id}' not found in system");
		}
		
		$api = $this->getApiInstance();
		
		// get order data and set order current state
		$result = $this->getOrder($externalId, $api);
		$commresData["state"] = $result->state;
		
		// order is confirmed already
		if (KupNajistoApi::ORDER_STATE_CONFIRMED == $result->state) {
			$commres->setDataAndSave($commresData);
			
			$this->logger->app->info("Kup Najisto - Order '{$externalId}' is confirmed already");
			return $result;
		}
		
		if (KupNajistoApi::ORDER_STATE_APPROVED == $result->state) {
			$this->__confirmOrder($externalId, $api);
			
			// set invoice number
			$commission->setInvoiceNumber();
				
			// generate invoice
			$htmlData = \Invoice::generate($commission);
			$pdfData = \Pdf::generate($htmlData);
				
			$invoiceFileName = "svetbot_faktura_" . $commission->faktura . ".pdf";
			
			$this->__appendInvoiceContent($externalId, $pdfData, "application/x-pdf", $invoiceFileName, $api);
				
			// Update expedice
			$exp = \Expedition::getExpedition($commission->expedice);
			if (false !== $exp) {
				$exp->stav = 1;
				$exp->save();
			}
			
			// send mail to the customer
			if ((boolean) $sendMail) {
				\Order::sendKupNajistoPaymentSuccess($commission);
			}
			
			// set new order state
			$commresData["state"] = KupNajistoApi::ORDER_STATE_CONFIRMED;
			
			$this->logger->app->info("Kup Najisto - Confirm order '{$externalId}' done");
		} else {
			// send mail to the customer
			if ((boolean) $sendMail) {
				\Order::sendKupNajistoPaymentFailure($commission);
			}
			
			$this->logger->app->info("Kup Najisto - Confirm order '{$externalId}' failed, order status: '{$result->state}'");
		}
		
		$commres->setDataAndSave($commresData);
		
		return $result;
	}
	
	/**
	 * Handle new commission on Kup Najisto
	 * 
	 * @param integer $commissionId ID of new commission
	 * @param integer $carrierId carrier ID
	 * @param array $deliveryAddress (OPTIONAL) delivery address if it is different from billing address
	 * @throws KupNajistoException
	 * @return boolean|string FALSE or paygate URL
	 */
	public function cartConfirm($commissionId, $carrierId, $deliveryAddress = array())
	{
		$commission = ModelCommission::getInstance()->findFirst((int) $commissionId);
		if (false === $commission) {
			$this->logger->app->error("Kup Najisto - Cart confirmation error: unknown commission ID: '{$commissionId}'");
			return false;
		}
		
		$items = ModelCommissionItem::getInstance()->getItems($commissionId);
		if ($items->count() < 1) {
			$this->logger->app->error("Kup Najisto - Cart confirmation error: no items exists commission ID: '{$commissionId}'");
			return false;
		}
		
		$api = $this->getApiInstance();
		
		foreach ($items as $item) {
			$item = $item->toArray();
			
			$id = null;
			$state = KupNajistoApi::DELIVERY_STATE_NEW;
			$type = ModelCommissionItem::determineItemType($item);
			
			switch ($type) {
				case ModelCommissionItem::ITEM_TYPE_PRODUCT:
					$id = $item["velikost"];
					$state = KupNajistoApi::DELIVERY_STATE_NEW;
					break;
				case ModelCommissionItem::ITEM_TYPE_FEE:
					$id = "fee_" . $item["id"];
					$state = KupNajistoApi::DELIVERY_STATE_SENT;
					break;
				case ModelCommissionItem::ITEM_TYPE_VOUCHER:
					$id = "voucher_" . $item["id"];
					$state = KupNajistoApi::DELIVERY_STATE_SENT;
					break;
				case ModelCommissionItem::ITEM_TYPE_DISCOUNT:
					$id = "discount_" . $item["id"];
					$state = KupNajistoApi::DELIVERY_STATE_SENT;
					break;
				default:
					$id = "item_" . $item["id"];
					$state = KupNajistoApi::DELIVERY_STATE_SENT;
					break;
			}
			
			$api->addProduct($id, $item["nazev"], $item["cena"], 1, $state);
		}
		
		$api->setCustomerInfo($commission->jmeno, $commission->email);
		$api->setBillingAddress($commission->jmeno, $commission->ulice, $commission->obec, $commission->psc, $commission->stat);
		
		if (! empty($deliveryAddress)) {
			$api->setDeliveryAddress(
				$deliveryAddress["djmeno"], 
				$deliveryAddress["dulice"], 
				$deliveryAddress["dobec"], 
				$deliveryAddress["dpsc"], 
				$deliveryAddress["dstat"]
			);
		}
		
		$carrierId = $this->convertCarrier($carrierId);
		$api->setDeliveryCarrier($carrierId);
		
		$api->setCallBackUrl($this->url->getFull("/api/kupnajisto"));
		$api->setSuccessUrl($this->url->getFull("/payment/success?c=" . $commission->id));
		$api->setFailureUrl($this->url->getFull("/payment/failure?c=" . $commission->id));
		
		$p = $api->orderNew($commission->id, $commission->variabilni, $commission->telefon, $_SERVER["REMOTE_ADDR"]);
		
		try {
			$result = $api->send();
			
			if (! isset($result->id)) {
				throw new KupNajistoException("Missing key 'id' in the data of newly created order");
			}
			if (! isset($result->paygate_url)) {
				throw new KupNajistoException("Missing key 'paygate_url' in the data of newly created order");
			}
			
			// TODO @@ Connect reseller with payment system
			$commission->setReseller(1, $result->id);
			
			$commission->setItemsUnchanged();
		} catch (KupNajistoException $e) {
			$this->logger->app->error("Kup Najisto - Cart confirmation error: " . $e->getMessage());
			return false;
		}
		
		// result is the full paygate URL
		$result = $api->getApiUrl() . $result->paygate_url;
		
		return $result;
	}
	
	/**
	 * Verify order state
	 * 
	 * Method verifies order state, try to confirm approved order,
	 * save proper order data structure and in case of order
	 * is not valid (rejected order) set commission items as unchanged
	 * 
	 * @param integer $externalId external ID of the order
	 * @param \KupNajisto\KupNajistoApi $api API instance
	 * @return boolean
	 */
	private function __verifyOrderState($externalId, $api = null)
	{
		if (null === $api) {
			$api = $this->getApiInstance();
		}
		
		$confirmed = false;
		$result = true;
		
		$commres = ModelCommissionReseller::getInstance()->findFirstByExternalId($externalId);
		
		// info data about external commission
		$commresData = (null === $commres->data) ? array() : $commres->data;
		// repair old key
		if (isset($commresData["confirm"])) {
			$commresData["confirmation"] = $commresData["confirm"];
			unset($commresData["confirm"]);
		}
		
		$this->logger->app->debug("Kup Najisto - Verify order state");
		
		if (! empty($commresData) && isset($commresData["state"])) {
			if (KupNajistoApi::ORDER_STATE_CONFIRMED == $commresData["state"]) {
				$this->logger->app->debug("Kup Najisto - Order is confirmed by data structure");
				$confirmed = true;
			} elseif (KupNajistoApi::ORDER_STATE_APPROVED == $commresData["state"]) {
				// try to confirm approved order
				try {
					$this->logger->app->debug("Kup Najisto - Order is approved by data structure, trying to confirm");
					$this->__confirmOrder($externalId, $api);
					$this->logger->app->debug("Kup Najisto - Order is confirmed, saving new data structure");
					$commresData["state"] = KupNajistoApi::ORDER_STATE_CONFIRMED;
					$commres->setDataAndSave($commresData);
					$confirmed = true;
					$this->logger->app->debug("Kup Najisto - New data structure saved successfully");
				} catch (KupNajistoException $e) {
					$this->logger->app->error("Kup Najisto - Error when trying to confirm order with external ID '{$externalId}', commission ID '{$external->commission_id}': " . $e->getMessage());
					$result = false;
				}
			} else {
				// order is not valid, set items as unchanged and continue
				$stateText = $api->getTextForOrderState($commresData["state"]);
				$this->logger->app->debug("Kup Najisto - Order is not valid by data structure, current state for order is '{$stateText}'");
				
				$this->logger->app->debug("Kup Najisto - Trying to set commission items as unchanged");
				ModelCommission::getInstance()->setItemsUnchanged($commres->commission_id);
				$this->logger->app->debug("Kup Najisto - Commission items was successfully set as unchanged");
				$result = false;
			}
		}
			
		// go to next order
		if (false === $result) {
			return false;
		}
			
		if (false === $confirmed) {
			try {
				$this->logger->app->debug("Kup Najisto - Unknown order state by data structure, trying get info from external API");
				$result = $this->getOrder($externalId, $api);
					
				$stateText = $api->getTextForOrderState($result->state);
				$this->logger->app->debug("Kup Najisto - Current order state is: '{$stateText}'");
					
				switch ((int) $result->state) {
					case KupNajistoApi::ORDER_STATE_CONFIRMED:
						$this->logger->app->debug("Kup Najisto - Saving new data structure");
						$commresData["state"] = KupNajistoApi::ORDER_STATE_CONFIRMED;
						$commres->setDataAndSave($commresData);
						$this->logger->app->debug("Kup Najisto - New data structure saved successfully");
						break;
					case KupNajistoApi::ORDER_STATE_APPROVED:
						// try to confirm approved order
						$this->logger->app->debug("Kup Najisto - Trying confirm approved order");
						$this->__confirmOrder($externalId, $api);
						$this->logger->app->debug("Kup Najisto - Order is confirmed, saving new data structure");
						$commresData["state"] = KupNajistoApi::ORDER_STATE_CONFIRMED;
						$commres->setDataAndSave($commresData);
						$this->logger->app->debug("Kup Najisto - New data structure saved successfully");
						break;
					default:
						// other states
						$this->logger->app->debug("Kup Najisto - Order is no valid, saving new data structure");
						$commresData["state"] = (int) $result->state;
						$commres->setDataAndSave($commresData);
						$this->logger->app->debug("Kup Najisto - New data structure saved successfully");
						
						$this->logger->app->debug("Kup Najisto - Trying to set commission items as unchanged");
						ModelCommission::getInstance()->setItemsUnchanged($commres->commission_id);
						$this->logger->app->debug("Kup Najisto - Commission items was successfully set as unchanged");
						
						$result = false;
						break;
				}
			} catch (KupNajistoException $e) {
				$this->logger->app->error("Kup Najisto - Error when trying to verify order with external ID '{$externalId}', commission ID '{$external->commission_id}': " . $e->getMessage());
				$result = false;
			}
		}
		
		return $result;
	}
	
	/**
	 * Process commissions with changed items
	 * 
	 * @return void
	 */
	public function changedProcess()
	{
		$t1 = microtime(true);
		
		$this->logger->app->info("Kup Najisto - Trying to process orders with changed items");
		
		// TODO @@ fix reseller ID determination
		$changed = ModelCommissionReseller::getInstance()->getChanged(1);
		
		if ($changed->count() < 1) {
			$this->logger->app->info("Kup Najisto - There is no changed items to process");
			return true;
		}
		
		$api = $this->getApiInstance();
		
		$modelItems = ModelCommissionItem::getInstance();
		
		$errorCount = 0;
		
		foreach ($changed as $external) {
			$externalId = $external->external_id;
			
			$this->logger->app->debug("---------------------------------------------------------------");
			$this->logger->app->debug("Kup Najisto - Processing order with external ID '{$externalId}'");
			
			// verify order state
			if (false === $this->__verifyOrderState($externalId)) {
				// verification failed, go to the next order
				continue;
			}
			$this->logger->app->debug("Kup Najisto - Trying to prepare data for update order");
			
			$api->setOrderId($externalId);
			$items = $modelItems->getItemsByExternalId($externalId);
			
			$processedItems = $this->__recreateOrderItems($items, $api);
			
			reset($items);
			$orderDeliveryState = $this->__setOrderDeliveryState($items, $api, $externalId);
			if (0 === $orderDeliveryState) {
				$this->logger->app->notice("Kup Najisto - Cannot set delivery state for order with external ID '{$externalId}', but items are processing");
			}
			
			try {
				if ($orderDeliveryState !== KupNajistoApi::DELIVERY_STATE_RETURNED) {
					$this->logger->app->debug("Kup Najisto - Trying to update order");
					$api->orderUpdate();
					
					$data = $api->getOrderData();
					$data = zd($data, null, false);
					$this->logger->app->debug("Kup Najisto - Order data to send: " . $data);
					
					$api->send();
				} else {
					$this->logger->app->debug("Kup Najisto - Update order cancelled for delivery state 'returned'");
				}
					
				// Set all processed items unchanged
				$this->logger->app->debug("Kup Najisto - Order was updated succesfully, setting processed items as unchanged");
				$modelItems->setItemUnchaged($processedItems);
				$this->logger->app->debug("Kup Najisto - Processed items was set as unchanged");
			} catch (\KupNajisto\KupNajistoException $e) {
				$this->logger->app->error("Kup Najisto - Error on process changed items for order with external ID '{$externalId}', commission ID '{$external->commission_id}': " . $e->getMessage());
				
				$data = $api->getOrderData();
				$data = zd($data, null, false);
				$this->logger->app->debug("Kup Najisto - Order data: " . $data);
				
				$errorCount++;
				
				if ($errorCount > 5) {
					break;
				}
			}
		}
		
		$t2 = microtime(true);
		$t0 = round($t2 - $t1, 2);
		
		if (0 === $errorCount) {
			$logMessage = "Kup Najisto - Processing of changed items done successfully without errors. ";
		} else {
			$logMessage = "Kup Najisto - Processing of changed items done with count of errors: {$errorCount}. ";
		}
		$logMessage .= "Duration: {$t0} seconds";
		
		$this->logger->app->info($logMessage);
	}
	
	/**
	 * Convert system carrier ID to the Kup Najisto carrier ID
	 * 
	 * @param integer $carrierId
	 * @return integer
	 */
	public function convertCarrier($carrierId)
	{
		switch ($carrierId) {
			case 1:
				$carrierId = KupNajistoApi::CARRIER_CZECH_POST_PACKAGE_TO_HAND;
				break;
			case 2:
				$carrierId = KupNajistoApi::CARRIER_DPD;
				break;
			case 4:
				$carrierId = KupNajistoApi::CARRIER_CZECH_POST_PACKAGE_TO_OFFICE;
				break;
			case 8:
				$carrierId = KupNajistoApi::CARRIER_IN_TIME;
				break;
			case 10:
				$carrierId = KupNajistoApi::CARRIER_GLS;
				break;
		}
		
		return $carrierId;
	}
	
	/**
	 * Exchange or return confirmation
	 * 
	 * This method add new items to the Kup Najisto order only.
	 * Returned items will be processed by regularly called
	 * method changedProcess() when they will be returned by customer.
	 * 
	 * @param integer $originalCommissionId
	 * @param integer $newCommissionId
	 * @return boolean
	 */
	public function exchangeConfirm($originalCommissionId, $newCommissionId)
	{
		$newCommission = ModelCommission::findFirst((int) $newCommissionId);
		if (false === $newCommission) {
			$this->logger->app->error("Kup Najisto - Exchange process error: unknown new commission ID: '{$newCommissionId}'");
			return false;
		}
		
		$modelReseller = ModelCommissionReseller::getInstance();
		
		$extCommission = $modelReseller->findFirstByCommissionId($originalCommissionId);
		if (false === $extCommission) {
			$this->logger->app->error("Kup Najisto - Exchange process error: connection to the reseller for the original commission ID: '{$originalCommissionId}' not found");
			return false;
		}
		
		$config = $this->getConfig();
		
		// set account number for refund
		$newCommission->ucet = $config->refund->accountNumber;
		$newCommission->save();
		
		$externalId = $extCommission->external_id;
		
		$modelItems = ModelCommissionItem::getInstance();
		$items = ModelCommissionItem::getInstance()->getItemsByExternalId($externalId);
		
		$api = $this->getApiInstance();
		
		$processedItems = $this->__recreateOrderItems($items, $api);
		
		try {
			$api->orderUpdate($externalId);
			$api->send();
			
			// Set all processed items unchanged
			$modelItems->setItemUnchaged($processedItems);
		} catch (\KupNajisto\KupNajistoException $e) {
			$this->logger->app->error("Kup Najisto - Exchange confirmation error: " . $e->getMessage());
			return false;
		}
		
		return true;
	}
	
	/**
	 * Recreate items for add to updated Kup Najisto order
	 * 
	 * Method clears all data in API instance
	 * 
	 * @param array $items
	 * @param \KupNajisto\KupNajistoApi $api API instance
	 * @return array ID of commission items for set as unchanged 
	 */
	private function __recreateOrderItems($items, $api)
	{
		$result = array();
		
		$api->clearOrder();
		
		foreach ($items as $item) {
			$id = null;
			$type = ModelCommissionItem::determineItemType($item);
				
			$state = KupNajistoApi::DELIVERY_STATE_NEW;
			if ((boolean) $item["status_sent"] || (boolean) $item["status_awaiting"]) {
				$state = KupNajistoApi::DELIVERY_STATE_SENT;
			} elseif ((boolean) $item["status_returned"]) {
				$state = KupNajistoApi::DELIVERY_STATE_RETURNED;
			}
			
			switch ($type) {
				case ModelCommissionItem::ITEM_TYPE_PRODUCT:
					$id = $item["velikost"];
					break;
				case ModelCommissionItem::ITEM_TYPE_FEE:
					$id = "fee_" . $item["id"];
					break;
				case ModelCommissionItem::ITEM_TYPE_VOUCHER:
					$id = "voucher_" . $item["id"];
					break;
				case ModelCommissionItem::ITEM_TYPE_DISCOUNT:
					$id = "discount_" . $item["id"];
					break;
				default:
					$id = "item_" . $item["id"];
					break;
			}
				
			$api->addProduct($id, $item["nazev"], $item["cena"], 1, $state);
			
			$result[] = $item["id"];
		}
		
		return $result;
	}
	
	/**
	 * Return newly created API instance with a set login credentials
	 * 
	 * @return \KupNajisto\KupNajistoApi
	 */
	public function getApiInstance()
	{
		$config = $this->getConfig();
		$api = new KupNajistoApi($config->apiUrl);
		$api->setLoginCredentials($config->username, $config->password);
		
		return $api;
	}
	
	/**
	 * Get configuration for Kup Najisto
	 * 
	 * @return \Phalcon\Config\Adapter
	 */
	public function getConfig()
	{
		return $this->config->kupnajisto;
	}
	
	/**
	 * Get order data from Kup Najisto
	 * 
	 * @param integer $externalId
	 * @param \KupNajisto\KupNajistoApi $api (OPTIONAL) instance of API class; login credentials MUST BE SET
	 * @throws KupNajistoException
	 * @return \ArrayObject
	 */
	public function getOrder($externalId, $api = null)
	{
		if (null === $api) {
			$api = $this->getApiInstance();
		}
		
		try {
			$api->orderGet($externalId);
			$result = $api->send();
		} catch (KupNajistoException $e) {
			$this->logger->app->error("Kup Najisto - Error when obtaining data for order '{$externalId}': " . $e->getMessage());
			throw $e;
		}
		
		return $result;
	}
	
	/**
	 * Set order delivery state by commission items states
	 * 
	 * @param \KupNajisto\KupNajistoApi $api API instance
	 * @return integer
	 */
	private function __setOrderDeliveryState($items, $api, $externalId = null)
	{
		$this->logger->app->debug("Kup Najisto - Calculate and set new order delivery state");
		
		$orderDeliveryState = KupNajistoApi::DELIVERY_STATE_NEW;
		$itemDeliveryStates = array(
			KupNajistoApi::DELIVERY_STATE_NEW => 0,
			KupNajistoApi::DELIVERY_STATE_SENT => 0,
			KupNajistoApi::DELIVERY_STATE_RETURNED => 0,
		);
		
		foreach ($items as $item) {
			$state = KupNajistoApi::DELIVERY_STATE_NEW;
			
			if ((boolean) $item["status_sent"] || (boolean) $item["status_awaiting"]) {
				$state = KupNajistoApi::DELIVERY_STATE_SENT;
			} elseif ((boolean) $item["status_returned"]) {
				$state = KupNajistoApi::DELIVERY_STATE_RETURNED;
			}
			
			$itemDeliveryStates[$state]++;
		}
		
		if ($itemDeliveryStates[KupNajistoApi::DELIVERY_STATE_NEW] > 0) {
			// there are NEW items, no change for order delivery state
		} elseif ($itemDeliveryStates[KupNajistoApi::DELIVERY_STATE_SENT] > 0) {
			// there are one or more items with SENT state
			$orderDeliveryState = KupNajistoApi::DELIVERY_STATE_SENT;
		} else {
			// there are only returned items
			$orderDeliveryState = KupNajistoApi::DELIVERY_STATE_RETURNED;
		}
		
		$stateText = $api->getTextForOrderDeliveryState($orderDeliveryState);
		$this->logger->app->debug("Kup Najisto - Calculated new order delivery state is '{$stateText}'");
		
		// verify delivery state against Kup Najisto
		if (null !== $externalId) {
			$r = $this->__verifyOrderDeliveryState($orderDeliveryState, $externalId, $api);
			if (false === $r) {
				return 0;
			}
			
			if ($r == $orderDeliveryState) {
				// no change for delivery state
				$this->logger->app->debug("Kup Najisto - Current and new order delivery states of order with external ID '{$externalId}' are the same, no change");
				return $orderDeliveryState;
			}
		}
			
		if ($orderDeliveryState !== KupNajistoApi::DELIVERY_STATE_NEW) {
			$this->logger->app->debug("Kup Najisto - New order delivery state should be '{$stateText}'");
			
			$api->setDeliveryState($orderDeliveryState);
		} else {
			$this->logger->app->debug("Kup Najisto - Order delivery state remains as '{$stateText}', no change");
		}
		
		return $orderDeliveryState;
	}
	
	/**
	 * Verify calculated delivery state against Kup Najisto
	 * 
	 * @param integer $newDeliveryState calculated new delivery state
	 * @param integer $externalId external ID of the order
	 * @param \KupNajisto\KupNajistoApi $api API instance
	 * @return boolean
	 */
	private function __verifyOrderDeliveryState($newDeliveryState, $externalId, $api = null)
	{
		if (null === $api) {
			$api = $this->getApiInstance();
		}
		
		$this->logger->app->debug("Kup Najisto - Trying to verify delivery state for order with external ID '{$externalId}'");
		
		try {
			$api->orderGet($externalId);
			
			$orderData = $api->send();
			$currentDeliveryState = $orderData->delivery_state;
			
			$stateText = $api->getTextForOrderDeliveryState($currentDeliveryState);
			$this->logger->app->debug("Kup Najisto - Delivery state of order with external ID '{$externalId}' is '{$stateText}'");
			
			if ((KupNajistoApi::DELIVERY_STATE_NEW === $newDeliveryState) && ($newDeliveryState != $currentDeliveryState)) {
				// current order delivery state is SENT but there all still some undispatched products
				$stateText = $api->getTextForOrderDeliveryState($currentDeliveryState);
				$this->logger->app->warning("Kup Najisto - Order delivery state for order with external ID '{$externalId}' is set to '{$stateText}' but there are still unsent products");
				return false;
			} elseif ((KupNajistoApi::DELIVERY_STATE_SENT === $newDeliveryState) &&(KupNajistoApi::DELIVERY_STATE_RETURNED == $currentDeliveryState)) {
				// current order delivery state is RETURNED but there all still some products not returned by customer
				$stateText = $api->getTextForOrderDeliveryState($currentDeliveryState);
				$this->logger->app->warning("Kup Najisto - Order delivery state for order with external ID '{$externalId}' is set to '{$stateText}' but some products are still not returned by customer");
				return false;
			} elseif ((KupNajistoApi::DELIVERY_STATE_RETURNED === $newDeliveryState) && (KupNajistoApi::DELIVERY_STATE_NEW === $currentDeliveryState)) {
				// if current delivery state is NEW, try to set it as SENT before it will be marked as RETURNED 
				$this->logger->app->debug("Kup Najisto - Order delivery state for order with external ID '{$externalId}' is set to 'new' but it should be 'sent', trying to set proper value");
				
				$index = $api->storeOrderData(true);
				$api->setDeliveryState(KupNajistoApi::DELIVERY_STATE_SENT);
				$api->send();
				$api->loadOrderData($index);
				
				$this->logger->app->debug("Kup Najisto - Order delivery state for order with external ID '{$externalId}' was successfully set to 'sent'");
			}
		} catch (KupNajistoException $e) {
			$this->logger->app->warning("Kup Najisto - Cannot verify order delivery state for order with external ID '{$externalId}': " . $e->getMessage());
			return false;
		}
		
		return $currentDeliveryState;
	}
	
	/**
	 * For testing purposes
	 * 
	 * @param array $items
	 * @param integer $externalId
	 * @return array
	 */
	public function testOrderUpdate($items, $externalId)
	{
		$api = $this->getApiInstance();
		$api->setOrderId($externalId);
			
		$processedItems = $this->__recreateOrderItems($items, $api);
		
		$api->orderUpdate();
		
		return $api->getOrderData();
	}
	
}
