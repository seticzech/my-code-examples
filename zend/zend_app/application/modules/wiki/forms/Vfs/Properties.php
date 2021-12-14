<?php

/**
 * Formulář pro nastavení vlastností VFS položky
 * 
 * @author Jiří Pazderník <pazdernik.j@atlas.cz>
 */
class Wiki_Form_Vfs_Properties extends Eal_Form_Noerror_Basic
{
	
	public function init()
	{
		parent::init();
		
		$name = new Zend_Form_Element_Text('name');
		$name->setLabel(__('label:name'))
			->setRequired(true);
		
		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel(__('label:description'))
			->setAttrib('rows', '4');
		
		$butSubmit = new Zend_Form_Element_Submit('butSubmit');
		$butSubmit->setLabel(__('button:save'))
			->setIgnore(true);
		
		$this->addElements(array(
			$name,
			$description,
			$butSubmit,
		));
		
		$this->setElementDecorators($this->elementDecorators);
		$butSubmit->setDecorators($this->buttonDecorators);
		
		$callback = new Zend_Form_Element_Hidden('callback');
		$callback->setValue('checkItemName')
			->setDecorators($this->elementDecoratorsHidden)
			->setIgnore(true);
		
		$this->addElement($callback);
	}
	
	/**
	 * Callback
	 * 
	 * @return boolean
	 */
	public function checkItemName()
	{
		$name = $this->getElement('name');
		$nameValue = $name->getValue();
		
		$dataSerialized = stripslashes($this->getElement('data')->getValue());
		$dataArray = unserialize($dataSerialized);
		
		if (strcmp($nameValue, $dataArray['name']) != 0) {
			if (Core_Plugin_Vfs::getInstance()->checkInodeDuplicateName($dataArray)) {
				$name->addError(__('validate:vfs_inodeAlreadyExists'));
				return false;
			}
		}
		
		return true;
	}
	
	public function formProperties($inode)
	{
		$this->setAction('/wiki/docs/properties/' . $inode->id);
		
		$dataArray = array(
			'id_parent' => $inode->id_parent,
			'name' => $inode->name,
			'entry_type' => $inode->entry_type,
		);
		$dataSerialized = serialize($dataArray);
		
		$data = new Zend_Form_Element_Hidden('data');
		$data->setValue($dataSerialized)
			->setIgnore(true)
			->setDecorators($this->elementDecoratorsHidden);
		
		$this->addElements(array(
			$data,
		));
		
		$this->_setClasses();
		
		return $this;
	}
	
}

