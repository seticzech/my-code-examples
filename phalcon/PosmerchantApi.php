<?php

namespace Frontend\Model\Module\Payment;

use \Frontend\Model\Data\Commission AS ModelCommission,
	\Frontend\Model\Data\Mailing AS ModelMailing,
	\Frontend\Model\Data\Module\Payment\Eet AS ModelEet,
	\Frontend\Model\Data\Module\Payment\PosmerchantApi AS ModelPosmerchantApi,
	\Frontend\Model\Data\Module\Payment\PosmerchantApi\Request AS ModelPosmerchantApiRequest,
	\Frontend\Model\Data\Module\Payment\PosmerchantApi\Refund AS ModelPosmerchantApiRefund,
	\POSMerchant\POSMerchantApi AS API,
	\POSMerchant\Extension\Eet AS EET;

/**
 * ČSOB POS Merchant API model
 * 
 * Model for processing credit/debet cards payments
 * 
 * @author Jiri Pazdernik <jiri@pazdernik.net>
 */ 
class PosmerchantApi extends \Frontend\Model\Module\Base
{
	
	/**
	 * Common confirm method
	 * 
	 * Method is called from public confirm methods like cart confirm or exchange confirm
	 * 
	 * @param integer $commissionId ID of commission
	 * @throws \Exception
	 * @return boolean|string
	 */
	private function __confirm($commissionId)
	{
		$payment = ModelPosmerchantApi::findLastByCommissionId($commissionId);
		
		if (false === $payment) {
			// payment isn't exists yet
			$payment = ModelPosmerchantApi::createPayment($commissionId);
			if (false === $payment->getLastResult()) {
				$message = $payment->getMessagesAsString();
				throw new \Exception("Error when creating payment record: " . $message);
			}
			$payment = $this->__apiPaymentInit($commissionId, $payment);
		} else if (null === $payment->pay_id) {
			// payment exists but at last request was an error (cURL or init request)
			$payment = $this->__apiPaymentInit($commissionId, $payment);
		} else if ((API::PAYMENT_STATUS_CANCELLED == $payment->payment_status) || (API::PAYMENT_STATUS_REJECTED == $payment->payment_status)) {
			// create new payment record
			$payment = ModelPosmerchantApi::createPayment($commissionId);
			if (false === $payment->getLastResult()) {
				$message = $payment->getMessagesAsString();
				throw new \Exception("Error when creating payment record: " . $message);
			}
			$payment = $this->__apiPaymentInit($commissionId, $payment);
		} else if (API::PAYMENT_STATUS_WAITING == $payment->payment_status) {
			$this->_message = "Platba byla provedena a čeká na zaúčtování a potvrzení.";
			return false;
		} else if (API::PAYMENT_STATUS_CONFIRMED == $payment->payment_status) {
			$this->_message = "Platba byla provedena a potvrzena.";
			return false;
		} else if ((API::PAYMENT_STATUS_NEW != $payment->payment_status) && (API::PAYMENT_STATUS_PROCESSING != $payment->payment_status)) {
			// other payment statuses are unacceptable
			throw new \Exception("Payment ID: '{$payment->posmerchant_api_id}' has an unacceptable status: '{$payment->payment_status}' for init new payment");
		}
		
		$url = $this->__apiPaymentProcess($payment);
		
		return $url;
	}
	
	/**
	 * Cart confirm - last step in cart
	 * 
	 * @param integer $commissionId ID of commission
	 * @return string|false
	 */
	public function cartConfirm($commissionId)
	{
		try {
			$url = $this->__confirm($commissionId);
		} catch (\Exception $e) {
			$this->logger->app->error("POS Merchant API - Cart confirmation error (commission ID '{$commissionId}'): " . $e->getMessage());
			return false;
		}
		
		return $url;
	}
	
	/**
	 * Exchange confirm - last step in exchange or return goods
	 * 
	 * @param integer $commissionId ID of commission
	 * @return string|false
	 */
	public function exchangeConfirm($commissionId)
	{
		$commission = ModelCommission::findFirst($commissionId);
		
		if ($commission->getTotalAmount(false) <= 0) {
			return false;
		}
		
		try {
			$url = $this->__confirm($commissionId);
		} catch (\Exception $e) {
			$this->logger->app->error("POS Merchant API - Exchange confirmation error (commission ID '{$commissionId}'): " . $e->getMessage());
			return false;
		}
		
		return $url;
	}
	
	/**
	 * Pay confirm - return from paygate
	 * 
	 * @param integer $commissionId ID of commission
	 * @return string|false
	 */
	public function payConfirm($commissionId)
	{
		$commission = ModelCommission::findFirst($commissionId);
		
		if ($commission->getTotalAmount(false) <= 0) {
			return false;
		}
		
		try {
			$url = $this->__confirm($commissionId);
		} catch (\Exception $e) {
			$this->logger->app->error("POS Merchant API - Pay confirmation error (commission ID '{$commissionId}'): " . $e->getMessage());
			return false;
		}
		
		return $url;
	}
	
	/**
	 * Init new payment
	 * 
	 * @param integer $commissionId ID of commission
	 * @param \Frontend\Model\Data\Module\Payment\PosmerchantApi $payment database payment record
	 * @throws \Exception
	 * @return \Frontend\Model\Data\Module\Payment\PosmerchantApi database payment record
	 */
	private function __apiPaymentInit($commissionId, $payment)
	{
		// create request database record
		$request = ModelPosmerchantApiRequest::createRecord($payment->posmerchant_api_id);
		if (false === $request->getLastResult()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when creating payment init request record: " . $message);
		}
		
		$commission = ModelCommission::findFirst($commissionId);
		
		// prepare new API instance
		$api = $this->__createApiInstance();
		$api->addToCart("Nakup SvetBot.cz", $commission->castka, 1, "Nakup bot a doplnku na SvetBot.cz");
		
		// add extension
		$eet = $this->__createExtensionEET($commission);
		if (null !== $eet) {
			$api->addExtension($eet);
		}
		
		// create payment init data
		$data = array(
			"orderNo" => $commission->order_number,
			"payOperation" => API::PAY_OPERATION_PAYMENT,
			"payMethod" => API::PAY_METHOD_CARD,
			"totalAmount" => $commission->amount * 100,
			"currency" => "CZK",
			"closePayment" => true,
			"returnUrl" => $this->url->getFull("/payment/process", array("c" => $commissionId, "p" => $payment->posmerchant_api_id)),
			"returnMethod" => "POST",
			"description" => "Nakup bot a doplnku na SvetBot.cz v hodnote: {$commission->castka} CZK",
			"language" => "CZ",
		);
		
		// prepare and send API action
		$requestData = $api->prepareInitAction($data);
		$result = $api->send();
		
		// get response data from API call
		$responseData = $result->responseData;
		
		// process response data
		$request->action = $requestData["action"];
		$request->url = $result->url;
		$request->http_status = $result->httpStatus;
		$request->request_data = $result->requestData;
		$request->response_data = $result->response; // response in its original format, not JSON decoded
		$request->curl_error = $result->curlError;
		$request->curl_message = empty($result->curlMessage) ? null : $result->curlMessage;
		if (null !== $responseData) {
			$request->payment_status = $responseData["paymentStatus"]; // can be NULL if resultCode has not value of 0
			$request->result_code = $responseData["resultCode"];
			$request->result_message = $responseData["resultMessage"];
		}
		
		// update request and response data in database
		if (false === $request->save()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when updating payment init request record: " . $message);
		}
		
		if ($result->curlError > 0) {
			throw new \Exception("cURL error: '{$result->curlError}' when sending action '{$requestData["action"]}': " . $result->curlMessage);
		}
		if (null === $responseData) {
			throw new \Exception("Response data has NULL value when sending action '{$requestData["action"]}'");
		}
		if ($responseData["resultCode"] > 0) {
			throw new \Exception("Error: '{$responseData->resultCode}' when sending action '{$requestData["action"]}': " . $responseData->resultMessage);
		}
		
		// update payment values
		if (null === $payment->pay_id) {
			// set pay ID to payment record
			$payment->pay_id = $responseData["payId"];
		}
		$payment->payment_status = $responseData["paymentStatus"];
		if (false === $payment->save()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when updating payment init payment record: " . $message);
		}
		
		
		return $payment;
	}
	
	/**
	 * Process payment and create URL for redirect to paygate
	 * 
	 * @param \Frontend\Model\Data\Module\Payment\PosmerchantApi $payment
	 * @throws \Exception
	 * @return string
	 */
	private function __apiPaymentProcess($payment)
	{
		// create request record
		$request = ModelPosmerchantApiRequest::createRecord($payment->posmerchant_api_id);
		if (false === $request->getLastResult()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when creating payment process request record: " . $message);
		}
		
		// prepare new API instance
		$api = $this->__createApiInstance();
		
		// create payment process data
		$data = array(
			"payId" => $payment->pay_id
		);
		
		// prepare and send API action
		$requestData = $api->prepareProcessAction($data);
		$result = $api->send();
		
		// process response data
		$request->action = $requestData["action"];
		$request->url = $result->url;
		$request->http_status = $result->httpStatus;
		$request->request_data = $result->requestData;
		$request->response_data = $result->response; // response in its original format, not JSON decoded
		$request->curl_error = $result->curlError;
		$request->curl_message = $result->curlMessage;
		
		// update request and response data in database
		if (false === $request->save()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when updating payment process request record: " . $message);
		}
		
		// update payment status
		$payment->payment_status = API::PAYMENT_STATUS_PROCESSING;
		if (false === $payment->save()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when updating payment process payment record: " . $message);
		}
		
		
		return $request->url;
	}
	
	/**
	 * Check payment status and update DB record
	 * 
	 * @param \Frontend\Model\Data\Module\Payment\PosmerchantApi $payment
	 * @throws \Exception
	 * @return string URL for redirect to the paygate
	 */
	public function __apiPaymentStatus($payment)
	{
		// create request record
		$request = ModelPosmerchantApiRequest::createRecord($payment->posmerchant_api_id);
		if (false === $request->getLastResult()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when creating payment process request record: " . $message);
		}
		
		// prepare new API instance
		$api = $this->__createApiInstance();
		
		// create payment status data
		$data = array(
			"payId" => $payment->pay_id
		);
		
		// prepare and send API action
		$requestData = $api->prepareStatusAction($data);
		$result = $api->send();
		$responseData = $result->responseData;
		
		// process response data
		$request->action = $requestData["action"];
		$request->url = $result->url;
		$request->http_status = $result->httpStatus;
		$request->request_data = $result->requestData;
		$request->response_data = $result->response; // response in its original format, not JSON decoded
		$request->curl_error = $result->curlError;
		$request->curl_message = $result->curlMessage;
		if (null !== $responseData) {
			$request->payment_status = $responseData["paymentStatus"]; // can be NULL if resultCode has not value of 0
			$request->result_code = $responseData["resultCode"];
			$request->result_message = $responseData["resultMessage"];
		}
		
		// update request and response data in database
		if (false === $request->save()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when updating payment status request record: " . $message);
		}
		
		if ($result->curlError > 0) {
			throw new \Exception("cURL error: '{$result->curlError}' when sending action '{$requestData["action"]}': " . $result->curlMessage);
		}
		if (null === $responseData) {
			throw new \Exception("Response data has NULL value when sending action '{$requestData["action"]}'");
		}
		if ($responseData["resultCode"] > 0) {
			throw new \Exception("Error: '{$responseData->resultCode}' when sending action '{$requestData["action"]}': " . $responseData->resultMessage);
		}
		
		// update EET
		$this->__eetInitOrUpdate($payment, $result);
		
		// update payment status
		$payment->payment_status = $responseData["paymentStatus"];
		if (false === $payment->save()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when updating payment status of payment record ID: '{$payment->posmerchant_api_id}': " . $message);
		}
		
		return $payment;
	}
	
	/**
	 * Process refund in the payment company
	 * 
	 * @param \Frontend\Model\Data\Module\Payment\PosmerchantApi\Refund $refund refudn DB record
	 * @throws \Exception
	 * @return \Frontend\Model\Data\Module\Payment\PosmerchantApi payment DB record
	 */
	private function __apiRefundProcess($refund)
	{
		// find payment
		$payment = ModelPosmerchantApi::findFirst($refund->posmerchant_api_id);
		if (false === $payment) {
			throw new \Exception("POSMerchant API record ID: {$refund->posmerchant_api_id} not found");
		}
		// payment status has to be verified and settled
		$allowableStatuses = array(
			API::PAYMENT_STATUS_SETTLED,
			API::PAYMENT_STATUS_REFUND_WAIT, // previous partial refund
			API::PAYMENT_STATUS_REFUNDED // previous partial refund
		);
		if (! in_array($payment->payment_status, $allowableStatuses)) {
			$this->logger->app->info("Payment ID: '{$refund->posmerchant_api_id}' has payment status: '{$payment->payment_status}', refund is not possible");
			return;
		}
		
		// find commission
		$commission = ModelCommission::findFirst($refund->commission_id);
		
		// create request record
		$request = ModelPosmerchantApiRequest::createRecord($refund->posmerchant_api_id);
		if (false === $request->getLastResult()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when creating payment process request record: " . $message);
		}
		
		// increase attempts counter
		$refund->increaseAttempts();
		
		// prepare new API instance
		$api = $this->__createApiInstance();
		
		// add extension
		$eet = $this->__createExtensionEET($commission);
		if (null !== $eet) {
			$api->addExtension($eet);
		}
		
		// create payment refund data
		$data = array(
			"payId" => $refund->pay_id
		);
		if (isset($refund->amount)) {
			$data["amount"] = abs($refund->amount * 100);
		}
		
		// prepare and send API action
		$requestData = $api->prepareRefundAction($data);
		
		$result = $api->send();
		$responseData = $result->responseData;
		
		// process request and response data
		$request->action = $requestData["action"];
		$request->url = $result->url;
		$request->http_status = $result->httpStatus;
		$request->request_data = $result->requestData;
		$request->response_data = $result->response; // response in its original format, not JSON decoded
		$request->curl_error = $result->curlError;
		$request->curl_message = $result->curlMessage;
		if (null !== $responseData) {
			$request->payment_status = $responseData["paymentStatus"]; // can be NULL if resultCode has not value of 0
			$request->result_code = $responseData["resultCode"];
			$request->result_message = $responseData["resultMessage"];
		}
		
		// update request and response data in database
		if (false === $request->save()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when updating payment status request record: " . $message);
		}
		
		if ($result->curlError > 0) {
			throw new \Exception("cURL error: '{$result->curlError}' when sending action '{$requestData["action"]}': " . $result->curlMessage);
		}
		if (null === $responseData) {
			throw new \Exception("Response data has NULL value when sending action '{$requestData["action"]}'");
		}
		if ($responseData["resultCode"] > 0) {
			throw new \Exception("Error: '{$responseData->resultCode}' when sending action '{$requestData["action"]}': " . $responseData->resultMessage);
		}
		
		// update payment status
		$payment->payment_status = $responseData["paymentStatus"];
		if (false === $payment->save()) {
			$message = $request->getMessagesAsString();
			throw new \Exception("Error when updating payment status of payment record ID: '{$payment->posmerchant_api_id}': " . $message);
		}
		
		// update refund state
		if (false === $refund->setProcessed()) {
			$message = $refund->getMessagesAsString();
			throw new \Exception("Error when updating refund record state: " . $message);
		}
		
		// send information email
		ModelMailing::createRecordForCommissionByEmailCode("refund_process_posmerchant_api", $refund->commission_id);
		
		return $payment;
	}
	
	/**
	 * Payment process - return from paygate
	 * 
	 * @param \Frontend\Model\Data\Commission $commission
	 * @param \Phalcon\Http\Request $request
	 */
	public function paymentProcess($commission, $request)
	{
		try {
			$this->_message = "<h3>Děkujeme,</h3>" .
				"<p>Vaše platba byla úspěšně autorizována.</p>";
			
			$this->_errorMessage = "<p>Při zpracování platby došlo k chybě.</p>" .
				"<p>Pokud Vám do 2 hodin nedojde e-mailové upozornění s fakturou, " .
				"kontaktujte nás, prosím, na telefonním čísle +420 725 333 131 (pondělí - pátek 8:30 - 17:00)</p>" .
				"<p>Omlouváme se Vám za případné zdržení.</p>";
			
			if ($commission->isPaid()) {
				$this->logger->app->notice("POS Merchant API - Payment process notice: commission ID: '{$commission->id}' was paid already");
				return true;
			}
			
			// get PUT/POST params
			$posmerchantApiId = $request->get("p");
			$resultCode = $request->get("resultCode");
			$resultMessage = $request->get("resultMessage");
			$paymentStatus = $request->get("paymentStatus");
			$authCode = $request->get("authCode");
			
			if (null === $posmerchantApiId) {
				throw new \Exception("Payment ID has NULL value (probably missing parameter 'p' in URL)");
			}
			
			// payment request
			$req = ModelPosmerchantApiRequest::findLastRequestByPosmerchantApiId($posmerchantApiId, API::ACTION_PROCESS);
			if (false === $req) {
				throw new \Exception("Unknown payment ID: '{$posmerchantApiId}', request not found");
			}
			
			// payment record
			$payment = ModelPosmerchantApi::findFirst($posmerchantApiId);
			if (false === $payment) {
				throw new \Exception("Unknown payment ID: '{$posmerchantApiId}', payment not found");
			}
			
			// check payment status
			if ((API::PAYMENT_STATUS_NEW != $payment->payment_status) && (API::PAYMENT_STATUS_PROCESSING != $payment->payment_status)) {
				throw new \Exception("Payment ID: '{$payment->posmerchant_api_id}' has an unacceptable status: '{$payment->payment_status}'");
			}
			
			// update previously created request
			$req->payment_status = $paymentStatus;
			$req->result_code = $resultCode;
			$req->result_message = $resultMessage;
			$req->auth_code = $authCode;
			$req->save();
			
			// update payment
			$payment->payment_status = $paymentStatus;
			$payment->save();
			
			// set error message by result code
			if ($resultCode > 0) {
				$msg = null;
				switch ($resultCode) {
					case 120:
						$msg = "<p>Při zpracování platby došlo ze strany platební brány k zamítnutí: nemáte povoleny platby kartou přes Internet.</p>";
						break;
					case 130: 
						$msg = "<p>Při zpracování platby došlo ze strany platební brány k zamítnutí: byla překročena možná doba sezení.</p>";
						break;
					case 500:
						$msg = "<p>Při zpracování platby došlo ze strany platební brány k zamítnutí: Finanční správou bylo odmínuto hlášení EET.</p>";
						break;
					default:
						$msg = "<p>Při zpracování platby došlo ze strany platební brány k zamítnutí.</p>";
						break;
						
				}
				$this->_errorMessage = $msg;
				throw new \Exception("Payment ID: '{$payment->posmerchant_api_id}' has not accepted by paygate due to result code: '{$resultCode}' with message: '{$resultMessage}'");
			}
			
			// set error message by payment status
			switch ($paymentStatus) {
				case API::PAYMENT_STATUS_CANCELLED:
					$this->_errorMessage = "<p>Při zpracování platby došlo ze strany platební brány k zamítnutí: zrušení platby zákazníkem.</p>";
					throw new \Exception("Payment ID: '{$payment->posmerchant_api_id}' was rejected by customer'");
					break;
			}
			
			// everything is OK so continue
			
			// init EET record
			$this->__eetInitOrUpdate($payment);
			
			// set commission as paid
			$commission->setPaid();
		} catch (\Exception $e) {
			$this->logger->app->error("POS Merchant API - Payment process error (commission ID: '{$commission->id}'): " . $e->getMessage());
			return false;
		}
		
		return true;
	}
	
	/**
	 * Run task service
	 * 
	 * @return void
	 */
	public function task()
	{
		$this->logger->app->info("POS Merchant API task started");
		
		// confirmed payments (customer doesn't return from paygate)
		try {
			$payments = ModelPosmerchantApi::findByStatus(API::PAYMENT_STATUS_CONFIRMED);
			
			if ($payments->count() > 0) {
				// statuses for send payment confirm mail
				$statuses = array(
					API::PAYMENT_STATUS_WAITING,
					API::PAYMENT_STATUS_SETTLED
				);
				foreach ($payments as $payment) {
					try {
						$payment = $this->__apiPaymentStatus($payment);
						// set commission as paid if payment status is waiting or settled
						if (in_array($payment->payment_status, $statuses)) {
							ModelCommission::setPaidByCommissionId($payment->commission_id);
						}
					} catch (\Exception $e) {
						$this->logger->app->error("POS Merchant API task error - payment status update (payment ID: '{$payment->posmerchant_api_id}'): " . $e->getMessage());
					}
				}
			}
		} catch (\Phalcon\Mvc\Model\Exception $e) {
			$this->logger->app->error("POS Merchant API task error - payments update: " . $e->getMessage());
		}
		
		// process payments waiting for settle
		try {
			$payments = ModelPosmerchantApi::findByStatus(API::PAYMENT_STATUS_WAITING);
			
			if ($payments->count() > 0) {
				foreach ($payments as $payment) {
					try {
						$payment = $this->__apiPaymentStatus($payment);
					} catch (\Exception $e) {
						$this->logger->app->error("POS Merchant API task error - payment status update (payment ID: '{$payment->posmerchant_api_id}'): " . $e->getMessage());
					}
				}
			}
		} catch (\Phalcon\Mvc\Model\Exception $e) {
			$this->logger->app->error("POS Merchant API task error - payments update: " . $e->getMessage());
		}
		
		// process payment refunds waiting for confirm
		try {
			$payments = ModelPosmerchantApi::findPaymentRefundsToConfirm();
			
			if ($payments->count() > 0) {
				foreach ($payments as $payment) {
					try {
						$payment = $this->__apiPaymentStatus($payment);
					} catch (\Exception $e) {
						$this->logger->app->error("POS Merchant API task error - payment refund status update (payment ID: '{$payment->posmerchant_api_id}'): " . $e->getMessage());
					}
				}
			}
		} catch (\Phalcon\Mvc\Model\Exception $e) {
			$this->logger->app->error("POS Merchant API task error - payments update: " . $e->getMessage());
		}
		
		// offline EET
		try {
			$payments = ModelPosmerchantApi::findByEetStatus(EET::EET_STATUS_OFFLINE);
			
			if ($payments->count() > 0) {
				foreach ($payments as $payment) {
					try {
						$this->__eetInitOrUpdate($payment);
					} catch (\Exception $e) {
						$this->logger->app->error("POS Merchant API task error - EET status update (payment ID: '{$payment->posmerchant_api_id}'): " . $e->getMessage());
					}
				}
			}
		} catch (\Phalcon\Mvc\Model\Exception $e) {
			$this->logger->app->error("POS Merchant API task error - EET status update: " . $e->getMessage());
		}
		
		// process new refunds
		try {
			$refunds = ModelPosmerchantApi::findRefundsToProcess();
			
			if ($refunds->count() > 0) {
				foreach ($refunds as $refund) {
					try {
						$this->__apiRefundProcess($refund);
					} catch (\Exception $e) {
						$this->logger->app->error("POS Merchant API task error - refund processing (refund ID: '{$refund->posmerchant_api_refund_id}'): " . $e->getMessage());
					}
				}
			}
		} catch (\Phalcon\Mvc\Model\Exception $e) {
			$this->logger->app->error("POS Merchant API task error - refunds process: " . $e->getMessage());
		}
		
		$this->logger->app->info("POS Merchant API task finished");
	}
	
	/**
	 * Create POS Merchant API library instance
	 * 
	 * @return \POSMerchant\POSMerchantApi
	 */
	private function __createApiInstance()
	{
		$config = $this->getConfig("posmerchantapi");
		
		$api = API::factory($config->merchantId);
		$pKey = file_get_contents($config->privateKeyFile);
		
		$api->setPrivateKey($pKey)
			->setUrl($config->apiUrl);
		
		return $api;
	}
	
	/**
	 * Create EET extension
	 * 
	 * @param \Frontend\Model\Data\Commission $commission
	 * @throws \Exception
	 * @return \POSMerchant\Extension\Eet|null
	 */
	private function __createExtensionEET($commission = null)
	{
		$eet = null;
		$config = $this->getConfig("eet");
		
		// prepare EET
		if ($config->enabled) {
			$eetData = array(
				"premiseId" => $config->premiseId,
				"cashRegisterId" => $config->cashRegisterId,
			);
			
			// create data for commission
			if (null !== $commission) {
				$eetData["totalPrice"] = $commission->getTotalAmount();
				
				// set base prices and VATs
				$prices = $commission->getCalculatePricesForEet();
				
				// check calculated VAT rates for in config non-existent VAT rate
				$shopVatRates = array_values($config->vatRates->toArray());
				foreach ($prices as $vatRate => $price) {
					if (! in_array($vatRate, $shopVatRates)) {
						throw new \Exception("VAT rate: '{$vatRate}' in calculated commission prices does not mapped into EET VAT rate");
					}
				}
				
				// set EET data by VAT rates
				foreach ($config->vatRates as $eetVatRate => $shopVatRate) {
					if (array_key_exists($shopVatRate, $prices)) {
						$price = $prices[$shopVatRate];
						if ($price["total"] == 0) {
							// zero prices are not included in EET data
							continue;
						}
						switch ($eetVatRate) {
							case "standard":
								$eetData["priceStandardVat"] = $price["base"];
								$eetData["vatStandard"] = $price["vat"];
								break;
							case "reduced1":
								$eetData["priceFirstReducedVat"] = $price["base"];
								$eetData["vatFirstReduced"] = $price["vat"];
								break;
							case "reduced2":
								$eetData["priceSecondReducedVat"] = $price["base"];
								$eetData["vatSecondReduced"] = $price["vat"];
								break;
						}
					}
				}
			}
			
			$eet = new EET();
			if ($config->verification) {
				$eet->verificationMode = true;
			}
			$eet->setData($eetData);
		}
		
		return $eet;
	}
	
	/**
	 * Init or update EET record
	 * 
	 * @param \Frontend\Model\Data\Payment $payment
	 * @param \Pel\ArrayObject $response (OPTIONAL)
	 * @throws \Exception
	 * @return \Asl\Model\Data\Module\Payment
	 */
	private function __eetInitOrUpdate($payment, $response = null)
	{
		$eet = $this->__createExtensionEET();
		if (null === $eet) {
			return $payment;
		}
		
		$api = $this->__createApiInstance();
		if (null === $response) {
			$result = $api->getExtensionStatus($payment->pay_id, $eet);
		} else {
			$result = $api->extractExtensionDataFromResponse($response, $eet);
		}
		//zd($result); return;
		if (false === $result) {
			// retrieving EET status failed
			$result = $api->getResponseData();
			$responseData = $result->responseData;
			
			// check for errors
			if ($result->curlError > 0) {
				throw new \Exception("cURL error: '{$result->curlError}' when trying to obtain EET status: " . $result->curlMessage);
			}
			if (null === $responseData) {
				throw new \Exception("Response data has NULL value when trying to obtain EET status");
			}
			if ($responseData["resultCode"] > 0) {
				throw new \Exception("Error: '{$responseData->resultCode}' when trying to obtain EET status: " . $responseData->resultMessage);
			}
			
			return $payment;
		}
		
		// update standard EET record
		$eetReport = $result->report;
		$eetRecord = ModelEet::createOrFindFirstRecord($payment->commission_id);
		if (false === $eetRecord->getLastResult()) {
			throw new \Exception("Error when trying to create or find EET record for commission ID: '{$payment->commission_id}'");
		}
		if ($eetReport->eetStatus != $payment->eet_status) {
			$this->__updateEetRecord($eetRecord, $eetReport);
			// update payment
			$payment->eet_status = $eetReport->eetStatus;
			$payment->save();
		}
		
		// update cancel (refund) EET records if exists
		if (isset($result->cancel)) {
			foreach ($result->cancel as $eetReport) {
				// try to find EET record by UUID
				$eetRecord = ModelEet::findFirstByUuid($eetReport["uuid"]);
				
				// try to find unrefunded record by amount
				$refund = null;
				if (false === $eetRecord) {
					$refunds = ModelPosmerchantApiRefund::findNotCancelledByPosmerchantApiId($payment->posmerchant_api_id);
					if ($refunds->count() > 0) {
						foreach ($refunds as $ref) {
							if (! $ref->isRefunded() && ($ref->amount == $eetReport->data->totalPrice)) {
								$eetRecord = ModelEet::createOrFindFirstRecord($ref->commission_id);
								if (false === $eetRecord->getLastResult()) {
									throw new \Exception("Error when trying to create or find EET record for commission ID: '{$ref->commission_id}'");
								}
								$refund = $ref;
								break;
							}
							
						}
					}
				}
				if (false === $eetRecord) {
					throw new \Exception("Error when trying to pair refund with EET cancel data for payment ID: '{$payment->posmerchant_api_id}'; refund amount: {$eetReport->data->totalPrice}");
				}
				$eetRecord = $this->__updateEetRecord($eetRecord, $eetReport);
				
				if (null === $refund) {
					// find existing refund
					$refund = ModelPosmerchantApiRefund::findFirstNotCancelledByCommissionId($eetRecord->commission_id);
					if (false === $refund) {
						throw new \Exception("Error when trying to find refund for commission ID: '{$eetRecord->commission_id}'; payment ID: '$payment->posmerchant_api_id'");
					}
				}
				
				// set as refunded
				$refund->setRefunded($eetReport->eetStatus);
				ModelCommission::setRefundedByCommissionId($refund->commission_id);
				
				// send mail if not sent already
				$mail = \Frontend\Model\Data\Mailing::findFirstForCommissionByEmailCode("refund_confirm_posmerchant_api", $refund->commission_id);
				if (false === $mail) {
					\Frontend\Model\Data\Mailing::createRecordForCommissionByEmailCode("refund_confirm_posmerchant_api", $refund->commission_id);
				}
			}
		}
		
		return $payment;
	}
	
	/**
	 * Update EET record in database
	 * 
	 * @param \Frontend\Model\Data\Module\Payment\PosmerchantApi\Eet $eetRecord
	 * #param ArrayObject $eetReport EET report data from payment company
	 * @throws \Exception
	 * return \Frontend\Model\Data\Module\Payment\PosmerchantApi\Eet $eetRecord
	 */
	private function __updateEetRecord($eetRecord, $eetReport)
	{
		$eetRecord->uuid = $eetReport->uuid;
		$eetRecord->receipt_number = $eetReport->receiptNumber;
		$eetRecord->evidence_mode = $eetReport->evidenceMode;
		$eetRecord->date_send = $eetReport->sendTime;
		$eetRecord->date_accept = $eetReport->acceptTime;
		if (! empty($eetReport->rejectTime)) {
			$eetRecord->date_reject = $eetReport->rejectTime;
		}
		if (! empty($eetReport->bkp)) {
			$eetRecord->bkp = $eetReport->bkp;
		}
		if (! empty($eetReport->pkp)) {
			$eetRecord->pkp = $eetReport->pkp;
		}
		if (! empty($eetReport->fik)) {
			$eetRecord->fik = $eetReport->fik;
		}
		$eetRecord->workshop_id = $eetReport->data["premiseId"];
		$eetRecord->register_id = $eetReport->data["cashRegisterId"];
		if (! empty($eetReport->error)) {
			$eetRecord->error_code = $eetReport->error["code"];
			$eetRecord->error_message = $eetReport->error["desc"];
		}
		if (false === $eetRecord->save()) {
			throw new \Exception("Error when trying to update EET record ID: '{$eetRecord->eet_id}'");
		}
		
		// add warning messages if any
		if (! empty($eetReport->warning)) {
			foreach ($eetReport->warning as $warn) {
				$eetRecord->addWarning($warn["code"], $warn["desc"]);
			}
		}
		
		return $eetRecord;
	}
	
}
