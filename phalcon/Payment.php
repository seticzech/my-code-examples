<?php

namespace Backend\Form\Module;

use \Pel\Forms\Element\CheckGroup,
	\Pel\Forms\Element\Check,
	\Pel\Forms\Element\Text,
	\Pel\Forms\Element\TextArea,
	\Pel\Forms\Element\Select,
	\Phalcon\Validation\Validator\StringLength;

/**
 * Class for payment form
 * 
 * @author Jiri Pazdernik <jiri@pazdernik.net>
 */
class Payment extends \Backend\Form\Base
{
	
	/**
	 * Create form for insert new method
	 * 
	 * @return void
	 */
	public function createForInsert()
	{
		$e = new Text("name");
		$e->setLabel("Název");
		$e->setUserOption("mandatory", true);
		$e->setUserOption("hint", "Obecný název pro použití v administraci (zadejte 1 až 255 znaků).");
		$e->addValidator(new StringLength(
			array(
				"min" => 1,
				"max" => 255,
				"messageMinimum" => "Název musí být zadán",
				"messageMaximum" => "Název může obsahovat max. 255 znaků"
			))
		);
		$this->add($e);
		
		$e = new Text("code");
		$e->setLabel("Kód");
		$e->setUserOption("mandatory", true);
		$e->setUserOption("hint", "Kód specifikuje názvy tříd pro umístění funkcí pro zpracování platby.");
		$e->addValidator(new StringLength(
			array(
				"min" => 1,
				"max" => 64,
				"messageMinimum" => "Kód musí být zadán",
				"messageMaximum" => "Kód může obsahovat max. 255 znaků"
			))
		);
		$this->add($e);
	}
	
	/**
	 * Create form for update method
	 * 
	 * @return void
	 */
	public function createForUpdate($languages, $shippingsList, $storesList)
	{
		$this->createGroup("general", "Obecné");
		$this->createGroup("setting", "Nastavení");
		//$this->createGroup("description", "Lokalizace");
		
		$this->_createGeneral($shippingsList, $storesList);
		//$this->_createDescription($languages);
		$this->_createSetting();
	}
	
	/**
	 * Create form for localization
	 * 
	 * @return void
	 */
	public function createForLocalize($languages, $storesList)
	{
		$this->createGroup("description", "Lokalizace");
		$this->_createDescription($languages);
	}
	
	/**
	 * Create group general
	 * 
	 * @param array $stores list of available stores
	 * @return void
	 */
	protected function _createGeneral($shippingsList, $storesList)
	{
		$group = $this->getGroup("general");
		
		$e = new Text("name");
		$e->setLabel("Název");
		$e->setUserOption("mandatory", true);
		$e->addValidator(new StringLength(
			array(
				"min" => 1,
				"max" => 255,
				"messageMinimum" => "Název musí být zadán",
				"messageMaximum" => "Název může obsahovat max. 255 znaků"
			))
		);
		$group->add($e);
		
		$e = new CheckGroup("stores");
		$e->setLabel("Obchody");
		$e->setUserOption("hint", "Obchody, pro které je tento typ platby povolen.");
		$group->add($e);
		foreach ($storesList as $id => $name) {
			$ch = new Check("stores[{$id}]", array(
				"id" => "stores[{$id}]",
				"value" => $id
			));
			$ch->setLabel($name);
			$e->add($ch);
		}
		
		$e = new CheckGroup("shippings");
		$e->setLabel("Doprava");
		$e->setUserOption("hint", "Doprava, pro kterou je tento typ platby povolen.");
		$group->add($e);
		foreach ($shippingsList as $id => $name) {
			$ch = new Check("shippings[{$id}]", array(
				"id" => "shippings[{$id}]",
				"value" => $id
			));
			$ch->setLabel($name);
			$e->add($ch);
		}
		
		$e = new Select("cash");
		$e->setLabel("Hotovost");
		$e->setUserOption("hint", "Je při platbě používána hotovost?");
		$e->setOptions(array(
			1 => "Ano",
			0 => "Ne"
		));
		$group->add($e);
		
		$e = new Select("status");
		$e->setLabel("Stav");
		$e->setOptions(array(
			1 => "Povoleno",
			0 => "Zakázáno"
		));
		$group->add($e);
	}
	
	/**
	 * Create group description
	 * 
	 * @param \Backend\Model\Data\Language $languages available languages
	 * @return void
	 */
	protected function _createDescription($languages)
	{
		$parentGroup = $this->getGroup("description");
		
		foreach ($languages as $language) {
			$langId = $language->language_id;
			
			//$group = $this->createGroup("lang-" . $langId, $language->name);
			$group = $parentGroup->createSubGroup("lang-" . $langId, $language->name);
			
			$e = new Text("description[{$langId}][title]");
			$e->setLabel("Název");
			$e->setUserOption("mandatory", true);
			$e->addValidator(new StringLength(
				array(
					"min" => 1,
					"max" => 255,
					"messageMinimum" => "Název musí být zadán",
					"messageMaximum" => "Název může obsahovat max. 255 znaků"
				))
			);
			$group->add($e);
				
			$e = new Text("description[{$langId}][summary_title]");
			$e->setLabel("Souhrnný název");
			$e->setUserOption("mandatory", true);
			$e->setUserOption("hint", "Text se používá v souhrnech např. v košíku, fakturách, u přeprodejců apod.");
			$e->addValidator(new StringLength(
				array(
					"min" => 1,
					"max" => 255,
					"messageMinimum" => "Souhrnný název musí být zadán",
					"messageMaximum" => "Souhrnný název může obsahovat max. 255 znaků"
				))
			);
			$group->add($e);
			
			$e = new TextArea("description[{$langId}][description]");
			$e->setLabel("Popis");
			$group->add($e);
			$this->addJS("CKEDITOR.replace('description[{$langId}][description]');");
			
			$e = new TextArea("description[{$langId}][payment_success]");
			$e->setLabel("Úspěšná platba");
			$group->add($e);
			$this->addJS("CKEDITOR.replace('description[{$langId}][payment_success]');");
			
			$e = new TextArea("description[{$langId}][payment_failure]");
			$e->setLabel("Neúspěšná platba");
			$group->add($e);
			$this->addJS("CKEDITOR.replace('description[{$langId}][payment_failure]');");
		}
	}
	
	/**
	 * Create group settings (override for any method settings)
	 * 
	 * @return void
	 */
	protected function _createSetting()
	{
		
	}
	
}
