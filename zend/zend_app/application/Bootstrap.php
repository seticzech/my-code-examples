<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	
	protected function _initDispatcher()
	{
		Zend_Controller_Front::getInstance()->setDispatcher(new Eal_Controller_Dispatcher_Standard());
	}
	
	protected function _initResponse()
	{
		Zend_Controller_Front::getInstance()->setResponse(new Eal_Controller_Response_Http());
	}
	
	protected function _initView()
	{
		$view = new Eal_View();
		$view->setScriptPath('views')
			 ->addHelperPath('Eal/View/Helper/', 'Eal_View_Helper_')
			 ->assign('locale', $this->locale)
			 ->doctype('XHTML1_STRICT');
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view);
		Zend_Controller_Front::getInstance()->setParam('view', $view);
		
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginatorControl.phtml');
		
		return $view;
	}
	
}

