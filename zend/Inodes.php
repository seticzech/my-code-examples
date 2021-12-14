<?php

/**
 * Model pro tabulku záznamů virtuálního souborového systému
 */
class Core_Model_Vfs_Inodes extends Eal_Db_Table_Abstract
{

	const TABLE_NAME = 'vfs_inodes';
	
	/**
	 * Typy záznamů
	 */
	const INODE_ENTRY_TYPE_ALL = 'all';
	const INODE_ENTRY_TYPE_ALL_FILES = 'files';
	const INODE_ENTRY_TYPE_DIRECTORY = 'd';
	const INODE_ENTRY_TYPE_FILE = 'f';
	const INODE_ENTRY_TYPE_LINK = 'l';
	const INODE_ENTRY_TYPE_ROOT = 'r';
	const INODE_ENTRY_TYPE_TRASH = 't';
	const INODE_ENTRY_TYPE_WIKI = 'w';
	
	/**
	 * ID trash záznamu
	 * 
	 * @var string
	 */
	const INODE_TRASH_ID = 'trash';
	
	/**
	 * Název tabulky.
	 * 
	 * @var string
	 */
	protected $_name = self::TABLE_NAME;
	
	/**
	 * Zend Date
	 * 
	 * @var Zend_Date
	 */
	private $_date = null;
	
	/**
	 * Instance objektu
	 * 
	 * @var object
	 */
	private static $_instance = null;
	
	/**
	 * Vytvoření instance objektu
	 * 
	 * @return Core_Model_Vfs_Inodes
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			$className = __CLASS__;
			self::$_instance = new $className;
		}
		return self::$_instance;
	}
	
	/**
	 * Vytvoření kořenového (root) INODE
	 * 
	 * @return Eal_Countable
	 */
	protected function _getTrashRootInode()
	{
		$date = new Zend_Date();
		$dateString = $date->toString($this->_timestampFormat);
		
		$row = array(
			'id' => Core_Model_Vfs_Partitions::PARTITION_TRASH_ID,
			'id_user' => 1,
			'id_role' => 1,
			'id_parent' => null,
			'entry_type' => self::INODE_ENTRY_TYPE_TRASH,
			'name' => __('info:vfs_trash_folder_name'),
			'ext' => null,
			'real_name' => null,
			'size' => 0,
			'created' => $dateString,
			'id_created_by' => 1,
			'modified' => $dateString,
			'id_modified_by' => 1,
			'deleted' => null,
			'id_deleted_by' => null,
			'active' => 1,
			'items' => array()
		);
		
		return new Eal_Countable($row);
	}
	
	/**
	 * Kontrola záznamu pro duplicitu
	 * 
	 * Zkontroluje se, zda již existuje záznam se stejným jménem a typem.
	 * Parametr $inode musí obsahovat minimálně následující položky:
	 * - ['name']       název položky
	 * - ['id_parent']  ID rodiče
	 * - ['entry_type'] typ položky; viz konstanty INODE_ENTRY_TYPE_*
	 * 
	 * Parametr $inode může být pole nebo objekt, který umožňuje převést
	 * svá data na pole - obsahuje metodu toArray()
	 * 
	 * @param mixed $inode informace o záznamu ke kontrole
	 * @param boolean $activeOnly porovnat pouze aktivní záznamy 
	 * @return integer|false ID záznamu, pokud záznam existuje, jinak FALSE
	 */
	public function checkInodeDuplicateName($inode, $activeOnly = true)
	{
		if (!is_array($inode)) {
			if (method_exists($inode, 'toArray')) {
				$inode = $inode->toArray();
			} else {
				throw new Eal_Application_Exception('iNode must be an array or object with defined toArray() method');
			}
		}
		$where = array(
			$this->getAdapter()->quoteInto('id_parent = ?', $inode['id_parent'], 'INTEGER'),
			$this->getAdapter()->quoteInto('name LIKE ?', $inode['name'])
		);
		
		if (isset($inode['id'])) {
			$where[] = $this->getAdapter()->quoteInto('id != ?', $inode['id'], 'INTEGER');
		}
		if ($activeOnly) {
			$where[] = $this->getAdapter()->quoteInto('active = ?', 1, 'INTEGER');
		}
		
		$rows = $this->fetchAll($where);
		
		if ($rows->count() > 0) {
			return $rows->current()->id;
		}
		
		return false;
	}
	
	/**
	 * Přidání nového záznamu
	 * 
	 * @param array $data
	 * @return integer ID přidaného záznamu
	 */
	public function addRecord($data)
	{
		$date = new Zend_Date();
		$timestamp = $date->toString($this->_timestampFormat);
		
		$userId = (null === $this->_userIdentity)
			? 1
			: $this->_userIdentity->id;
		$roleId = (null === $this->_userIdentity)
			? 1
			: $this->_userIdentity->id_role;
		
		if (!isset($data['id_user'])) {
			$data['id_user'] = $userId;
		}
		if (!isset($data['id_role'])) {
			$data['id_role'] = $roleId;
		}
		if (!isset($data['created'])) {
			$data['created'] = $timestamp;
		}
		if (!isset($data['id_created_by'])) {
			$data['id_created_by'] = $userId;
		}
		if (!isset($data['modified'])) {
			$data['modified'] = $timestamp;
		}
		if (!isset($data['id_modified_by'])) {
			$data['id_modified_by'] = $userId;
		}
		
		$newId = $this->insert($data);
		
		return $newId;
	}
	
	/**
	 * Generování části stromu souborového systému
	 * 
	 * @param object $parentNode INODE, pro který má být strom vygenerován
	 * @param integer $selectedItemId (OPTIONAL) ID zvoleného INODE
	 * @param boolean $expandSelected (OPTIONAL) zda rozbalit strom od zvoleného INODE
	 * @return void
	 */
	public function buildFullTreeBranch($parentNode, $selectedItemId = null, $expandSelected = false)
	{
		$rows = $this->getRecords($parentNode->id, self::INODE_ENTRY_TYPE_DIRECTORY, 'name ASC');
		
		foreach ($rows as $row) {
			$row->selected = $row->id == $selectedItemId;
			$row->expanded = false;
			if ($expandSelected) {
				$row->expanded = $row->selected;
			}
			$this->buildFullTreeBranch($row, $selectedItemId, $expandSelected);
			if ($row->selected || $row->expanded) {
				$parentNode->expanded = true;
			}
		}
		$rows->rewind();
		
		$parentNode->items = $rows;
	}
	
	/**
	 * Generování plného stromu pro souborový systém
	 * 
	 * @param integer $rootItemId ID kořenového INODE
	 * @param integer $selectedItemId (OPTIONAL) ID aktuálně zvoleného INODE
	 * @param boolean $expandSelected (OPTIONAL) zda rozbalit strom od zvoleného INODE
	 * @return Eal_Countable
	 */
	public function buildFullTree($rootItemId, $selectedItemId = null, $expandSelected = false)
	{
		$tree = new Eal_Countable();
		
		$root = $this->getRecord($rootItemId);
		if (null !== $root) {
			$tree->fromArray($root->toArray(), true);
		}
		
		foreach ($tree as $row) {
			$row->selected = $row->id == $selectedItemId;
			$row->expanded = false;
			if ($expandSelected) {
				$row->expanded = $row->selected;
			}
			$this->buildFullTreeBranch($row, $selectedItemId, $expandSelected);
		}
		$tree->rewind();
		
		return $tree;
	}
	
	/**
	 * Odstranění INODE
	 * 
	 * @param integer $recordId ID INODE pro odstranění
	 * @param integer $trash ID zvoleného koše pro INODE
	 * @param boolean $toTrash zda má být INODE přesunut do koše, nebo odstraněn úplně
	 * @return integer počet odstraněných položek
	 */
	public function deleteRecord($recordId, $trash = null, $toTrash = true)
	{
		$row = $this->find($recordId)->current();
		if ((null === $trash) && $toTrash) {
			$trash = $this->getTrashForInode($recordId);
		}
		
		// odstranění podčlenů, pokud jde o adresář
		if ($row->entry_type == self::INODE_ENTRY_TYPE_DIRECTORY) {
			$where = $this->getAdapter()->quoteInto('id_parent = ?', $recordId, 'INTEGER');
			$rows = $this->fetchAll($where);
			foreach ($rows as $r) {
				$this->deleteRecord($r->id, $trash, $toTrash);
			}
		}
		
		if ($row->active == 1) {
			if ($toTrash) {
				// odstranění do koše
				if (null === $this->_date) {
					$this->_date = new Zend_Date();
				} else {
					$this->_date->setTimestamp(time());
				}
				
				$row->id_trash = $trash->id;
				$row->deleted = $this->_date->toString($this->_timestampFormat);
				$row->id_deleted_by = $this->_userIdentity->id;
				$row->active = 0;
				
				return $row->save();
			} else {
				$basePath = $this->getFullPath($row->id);
				
				if (is_file($basePath . $row->real_name)) {
					unlink($basePath . $row->real_name);
				}
				
				// výmaz historie
				Core_Model_Vfs_History::getInstance()->deleteHistory($recordId);
				
				return $row->delete();
			}
		} else {
			$basePath = $this->getFullPath($row->id);
			
			if (is_file($basePath . $row->real_name)) {
				unlink($basePath . $row->real_name);
			}
			
			// výmaz historie
			Core_Model_Vfs_History::getInstance()->deleteHistory($recordId);
			
			return $row->delete();
		}
	}
	
	// OBSOLETE
	/*
	public function filterList($list, $filterType)
	{
		$filter = $this->_getFilter($filterType);
		
		$oldList = $list->toArray();
		$newList = array();
		
		foreach ($oldList as $item) {
			$pathInfo = $this->pathInfo($item['name']);
			if (in_array($pathInfo['extension'], $filter)) {
				$newList[] = $item;
			}
		}
		
		unset($list, $oldList);
		return new Eal_Countable($newList);
	}
	*/
	
	/**
	 * Základní URL pro INODE
	 * 
	 * @param integer $recordId ID INODE
	 * @return string
	 */
	public function getBaseUrl($recordId)
	{
		// TODO @@ přemístit basePath do konfigurace
		$basePath = 'storage';
		$basePath = str_replace('\\', '/',  $basePath);
		
		return '/' . $basePath . '/';
	}
	
	/**
	 * Vrátí plnou cestu k INODE
	 * 
	 * @param integer $recordId ID INODE
	 * @return string
	 */
	public function getFullPath($recordId)
	{
		// TODO @@ přemístit basePath do konfigurace
		$basePath = 'storage';
		
		return ROOT_PATH . $basePath . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Vrátí záznam o oddílu VFS pro daný INODE záznam.
	 * 
	 * @param integer $recordId ID INODE záznamu
	 * @return Eal_Db_Result_Row
	 */
	public function getPartitionForInode($recordId)
	{
		$rootId = $this->getRootForInode($recordId, false)->id;
		
		$m_partitions = Core_Model_Vfs_Partitions::getInstance();
		
		return $m_partitions->getRecordByRootId($rootId);
	}
	
	/**
	 * Vrátí všechny nadřazené záznamy pro daný INODE až po ROOT záznam
	 * 
	 * @param integer $recordId ID INODE záznamu
	 * @return Eal_Countable
	 */
	public function getPathToInode($recordId)
	{
		$path = array();
		
		$row = $this->getRecord($recordId, false);
		
		while (null !== $row->id_parent) {
			array_unshift($path, $row->toArray());
			$row = $this->getRecord($row->id_parent, false);
		}
		array_unshift($path, $row->toArray());
		
		return new Eal_Countable($path);
	}
	
	/**
	 * Vrátí ROOT (kořenový) záznam pro daný INODE záznam.
	 * 
	 * @param integer $recordId ID INODE záznamu
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getRootForInode($recordId, $retrieveInfo = true)
	{
		$row = $this->getRecord($recordId, $retrieveInfo);
		
		while ($row->id_parent) {
			$row = $this->getRecord($row->id_parent, $retrieveInfo);
		}
		
		return $row;
	}
	
	/**
	 * Vrátí TRASH záznam pro daný INODE záznam.
	 * 
	 * @param integer $recordId ID INODE záznamu
	 * @return Eal_Db_Result_Row
	 */
	public function getTrashForInode($recordId)
	{
		$root = $this->getRootForInode($recordId);
		
		$where = array(
			$this->getAdapter()->quoteInto('id_parent = ?', $root->id, 'INTEGER'),
			$this->getAdapter()->quoteInto('entry_type LIKE ?', self::INODE_ENTRY_TYPE_TRASH),
		);
		
		return new Eal_Db_Result_Row($this->fetchRow($where));
	}
	
	/**
	 * Vrátí INODE záznam.
	 * 
	 * @param integer $recordId ID INODE záznamu
	 * @param boolean $retrieveInfo zjistit informace o záznamu (člen id_node_type)
	 * @return Eal_Countable
	 */
	public function getRecord($recordId, $retrieveInfo = true)
	{
		if (self::INODE_TRASH_ID == $recordId) {
			$row = $this->_getTrashRootInode();
		} else {
			$row = new Eal_Db_Result_Row($this->find($recordId)->current());
			if (!$row->hasData()) {
				return $row;
			}
		}
		
		$row = $row->toArray();
		
		if ((count($row) > 0) && $retrieveInfo) {
			$row['base_url'] = $this->getBaseUrl($row['id']);
			$row['base_path'] = $this->getFullPath($row['id']);
		
			$row['node_type'] = Core_Model_Vfs_Inode_Types::getInstance()
				->getTypeInfo($row)->toArray();
		} else {
			$row['node_type'] = array();
		}
		
		return new Eal_Countable($row);
	}
	
	/**
	 * Nastavení rozšířených informací pro INODE
	 * 
	 * @param object $row INODE záznam
	 * @return void
	 */
	private function _setRecordExInfo($row)
	{
		$m_users = Core_Model_Sys_Users::getInstance();
		$m_roles = Core_Model_Sys_Acl_Roles::getInstance();
		
		if (null === $this->_date) {
			$this->_date = new Zend_Date();
		}
		$lcle = Zend_Controller_Front::getInstance()->getParam('locale');
		
		if (isset($row->created) && !is_null($row->created)) {
			$this->_date->set($row->created, null, 'en_CA');
			$fmt = array(
				'date' => $this->_date->get(Zend_Date::DATES, $lcle),
				'time' => $this->_date->get(Zend_Date::TIMES, $lcle),
			);
			$row->created = __('info:date_time_full', $fmt);
		}
		if (isset($row->modified) && !is_null($row->modified)) {
			$this->_date->set($row->modified, null, 'en_CA');
			$fmt = array(
				'date' => $this->_date->get(Zend_Date::DATES, $lcle),
				'time' => $this->_date->get(Zend_Date::TIMES, $lcle),
			);
			$row->modified = __('info:date_time_full', $fmt);
		}
		if (isset($row->deleted) && !is_null($row->deleted)) {
			$this->_date->set($row->deleted, null, 'en_CA');
			$fmt = array(
				'date' => $this->_date->get(Zend_Date::DATES, $lcle),
				'time' => $this->_date->get(Zend_Date::TIMES, $lcle),
			);
			$row->deleted = __('info:date_time_full', $fmt);
		}
		
		$row->user = $m_users->getRecord($row->id_user);
		$row->role = $m_roles->getRecord($row->id_role);
		
		if (isset($row->id_created_by) && !is_null($row->id_created_by)) {
			$row->created_by = $m_users->getRecord($row->id_created_by);
		}
		if (isset($row->id_modified_by) && !is_null($row->id_modified_by)) {
			$row->modified_by = $m_users->getRecord($row->id_modified_by);
		}
		if (isset($row->id_deleted_by) && !is_null($row->id_deleted_by)) {
			$row->deleted_by = $m_users->getRecord($row->id_deleted_by);
		}
		
		$row->path_info = $this->pathInfo($row->name);
	}
	
	/**
	 * Získat INODE záznam v rozšířeném tvaru
	 * 
	 * @param integer $recordId ID požadovaného INODE
	 * @param boolean $retrieveInfo zda určit typ, cestu a výchozí URL 
	 * @return Eal_Db_Result_Row
	 */
	public function getRecordEx($recordId, $retrieveInfo = true)
	{
		$row = $this->getRecord($recordId, $retrieveInfo);
		
		if ($row->hasData()) {
			$this->_setRecordExInfo($row);
		}
		
		return $row;
	}
	
	/**
	 * Získat informace k adresáři / složce
	 * 
	 * @param object $inode INODE objekt
	 * @param integer $parentId ID nadřazeného INODE
	 * @return void
	 */
	protected function _getFolderInfo($inode, $parentId = null)
	{
		// inicializace členů
		if (!isset($inode->node_info)) {
			$inode->node_info = new Eal_Countable(
				array(
					'size' => 0,
					'files_count' => 0,
					'folders_count' => 0,
				)
			);
		}
		
		if (null === $parentId) {
			$parentId = $inode->id_parent;
		}
		
		// zpracování folderů
		$rows = $this->getRecords($parentId, self::INODE_ENTRY_TYPE_DIRECTORY);
		foreach ($rows as $row) {
			$this->_getFolderInfo($inode, $row->id);
			$inode->node_info->size += $row->size;
			$inode->node_info->folders_count++;
		}
		
		// zpracování souborů
		$rows = $this->getRecords($parentId, self::INODE_ENTRY_TYPE_FILE);
		foreach ($rows as $row) {
			$inode->node_info->size += $row->size;
			$inode->node_info->files_count++;
		}
		$rows = $this->getRecords($parentId, self::INODE_ENTRY_TYPE_WIKI);
		$inode->node_info->files_count += $rows->count();
	}
	
	/**
	 * Získat informace k souboru
	 * 
	 * @param object $inode INODE objekt
	 * @return void
	 */
	protected function _getFileInfo($inode)
	{
		// inicializace členů
		if (!isset($inode->node_info)) {
			$inode->node_info = new Eal_Countable(
				array(
					'width' => null,
					'height' => null,
				)
			);
		}
		
		// zpracování obrázku, rozměry
		$imageExt = array('gif', 'jpg', 'jpeg', 'png');
		
		if (in_array(strtolower($inode->path_info->extension), $imageExt)) {
			$fileName = $this->getFullPath($inode->id) . $inode->real_name;
			if (file_exists($fileName)) {
				$size = getimagesize($fileName);
				$inode->node_info->width = $size[0];
				$inode->node_info->height = $size[1];
			} else {
				$inode->node_info->width = 0;
				$inode->node_info->height = 0;
			}
		}
	}
	
	/**
	 * Získat informace k WIKI stránce
	 * 
	 * @param object $inode INODE objekt
	 * @return void
	 */
	protected function _getWikiInfo($inode)
	{
		$pageInfo = Wiki_Model_Pages::getInstance()->getRecordEx($inode->real_name);
		$inode->node_info = $pageInfo;
	}
	
	/**
	 * Získat informace k INODE
	 * 
	 * @param integer $recordId ID požadovaného INODE
	 * @return Eal_Db_Result_Row
	 */
	public function getRecordInfo($recordId)
	{
		$inode = $this->getRecordEx($recordId);
		
		switch ($inode->entry_type) {
			case self::INODE_ENTRY_TYPE_DIRECTORY:
			case self::INODE_ENTRY_TYPE_ROOT:
				$this->_getFolderInfo($inode, $inode->id);
				break;
			case self::INODE_ENTRY_TYPE_FILE:
				$this->_getFileInfo($inode);
				break;
			case self::INODE_ENTRY_TYPE_WIKI:
				$this->_getWikiInfo($inode);
				break;
			default:
		}
		
		return $inode;
	}
	
	/**
	 * Vrátí seznam INODE záznamů
	 * 
	 * @param integer $parentId ID rodiče (vlastníka)
	 * @param mixed $entryType (OPTIONAL) typ INODE záznamů
	 * @param string|array $order (OPTIONAL) řazení položek v seznamu
	 * @param boolean $activeOnly (OPTIONAL) pokud je TRUE, seznam bude obsahovat pouze aktivní (nesmazané) INODE záznamy
	 * @param boolean $retrieveInfo (OPTIONAL) zda zjistit informace
	 * @return Eal_Countable
	 */
	public function getRecords($parentId, $entryType = null, 
		$order = null, $activeOnly = true, $retrieveInfo = true)
	{
		if (null === $entryType) {
			$entryType = self::INODE_ENTRY_TYPE_ALL;
		}
		
		$where = array();
		
		if (null !== $parentId) {
			$where[] = $this->getAdapter()->quoteInto('id_parent = ?', $parentId, 'INTEGER');
		}
		
		if ($entryType != self::INODE_ENTRY_TYPE_ALL) {
			if ($entryType == self::INODE_ENTRY_TYPE_ALL_FILES) {
				$where[] = $this->getAdapter()->quoteInto('entry_type != ?', self::INODE_ENTRY_TYPE_DIRECTORY);
				$where[] = $this->getAdapter()->quoteInto('entry_type != ?', self::INODE_ENTRY_TYPE_ROOT);
				$where[] = $this->getAdapter()->quoteInto('entry_type != ?', self::INODE_ENTRY_TYPE_TRASH);
			} else {
				$where[] = $this->getAdapter()->quoteInto('entry_type = ?', $entryType);
			}
		}
		
		if ($activeOnly) {
			$where[] = $this->getAdapter()->quoteInto('active = ?', 1, 'INTEGER');
		}
		
		$rows = new Eal_Countable($this->fetchAll($where, $order)->toArray());
		
		foreach ($rows as $row) {
			if (self::INODE_ENTRY_TYPE_WIKI == $row->entry_type) {
				$page = Wiki_Model_Pages::getInstance()->getRecord($row->real_name);
				$row->version = $page->version;
			}
			if ($retrieveInfo) {
				// typy položek
				$row->base_url = $this->getBaseUrl($row->id);
				$row->base_path = $this->getFullPath($row->id);
				
				$row->node_type = new Eal_Countable(Core_Model_Vfs_Inode_Types::getInstance()
					->getTypeInfo($row)->toArray());
			}
		}
		$rows->rewind();
		
		return $rows;
	}
	
	/**
	 * Vrátí rozšířený seznam INODE záznamů
	 * 
	 * Vrátí rozšířený seznam INODE záznamů. Kromě informací o INODE záznamech převede 
	 * datumy na národní tvar a přidá informace o uživatelích: vlastník záznamu a uživatel,
	 * který provedl poslední změnu záznamu.
	 * 
	 * @param integer $parentId ID rodiče (vlastníka)
	 * @param mixed $entryType (OPTIONAL) typ INODE záznamů
	 * @param string|array $order (OPTIONAL) řazení položek v seznamu
	 * @param boolean $activeOnly (OPTIONAL) pokud je TRUE, seznam bude obsahovat pouze aktivní (nesmazané) INODE záznamy
	 * @param boolean $retrieveInfo (OPTIONAL) zda zjistit informace
	 * @return Eal_Countable
	 */
	public function getRecordsEx($parentId, $entryType = null, 
		$order = null, $activeOnly = true, $retrieveInfo = true)
	{
		$rows = $this->getRecords($parentId, $entryType, $order, $activeOnly, $retrieveInfo);
		
		foreach ($rows as $row) {
			$this->_setRecordExInfo($row);
		}
		$rows->rewind();
		
		return $rows;
	}
	
	/**
	 * Vrátí seznam INODE záznamů umístěných v daném TRASH
	 * 
	 * Vrátí seznam INODE záznamů umístěných v daném TRASH. Pokud trashId není zadán,
	 * vrátí seznam všech INODE ve všech koších celého VFS.
	 * 
	 * @param integer $trashId (OPTIONAL) ID TRASH záznamu
	 * @param mixed $inodeType (OPTIONAL) typ INODE záznamů
	 * @param string|array $order (OPTIONAL) řazení položek v seznamu
	 * @return Eal_Countable
	 */
	public function getTrashRecords($trashId = null, $entryType = self::INODE_ENTRY_TYPE_ALL, 
		$order = null)
	{
		$where = array(
			$this->getAdapter()->quoteInto('active = ?', 0, 'INTEGER')
		);
		
		if ((null !== $trashId) && (self::INODE_TRASH_ID != $trashId)) {
			$where[] = $this->getAdapter()->quoteInto('id_trash = ?', $trashId, 'INTEGER');
		}
		
		if ($entryType != self::INODE_ENTRY_TYPE_ALL) {
			$where[] = $this->getAdapter()->quoteInto('entry_type LIKE ?', $entryType);
		}
		
		$rows = new Eal_Countable($this->fetchAll($where, $order)->toArray());
		
		// typy položek
		foreach ($rows as $row) {
			$row->base_url = $this->getBaseUrl($row->id);
			$row->base_path = $this->getFullPath($row->id);
			
			$row->node_type = new Eal_Countable(Core_Model_Vfs_Inode_Types::getInstance()
				->getTypeInfo($row)->toArray());
		}
		$rows->rewind();
		
		return $rows;
	}
	
	/**
	 * Vrátí rozšířený seznam INODE záznamů umístěných v daném TRASH
	 * 
	 * Vrátí rozšířený seznam INODE záznamů. Kromě informací o INODE záznamech převede 
	 * datumy na národní tvar a přidá informace o uživatelích: vlastník záznamu a uživatel,
	 * který provedl poslední změnu záznamu.
	 * 
	 * Pokud trashId není zadán, vrátí seznam všech INODE ve všech koších celého VFS.
	 * 
	 * @param integer $trashId (OPTIONAL) ID TRASH záznamu
	 * @param mixed $inodeType (OPTIONAL) typ INODE záznamů
	 * @param string|array $order (OPTIONAL) řazení položek v seznamu
	 * @return Eal_Countable
	 */
	public function getTrashRecordsEx($trashId = null, $entryType = self::INODE_ENTRY_TYPE_ALL, 
		$order = null)
	{
		$rows = $this->getTrashRecords($trashId, $entryType, $order);
		
		foreach ($rows as $row) {
			$this->_setRecordExInfo($row);
		}
		$rows->rewind();
		
		return $rows;
	}
	
	// OBSOLETE - přemístěno do Core_Plugin_Vfs
	/**
	 * Ověří, zda daný INODE záznam existuje a zda se jedná o adresář.
	 * 
	 * @param integer $recordId ID INODE záznamu.
	 * @param boolean $activeOnly pokud je TRUE, budou se brát v potaz pouze aktivní (nesmazané) INODE záznamy
	 * @return boolean
	 */
	/*
	public function isFolder($recordId, $activeOnly = true)
	{
		$row = $this->find($recordId)->current();
		
		if (null === $row) {
			return false;
		}
		if ($activeOnly) {
			if (!$row->active) {
				return false;
			}
		}
		
		return $row->type == self::INODE_ENTRY_TYPE_DIRECTORY;
	}
	*/
	
	/**
	 * Zjistí, zda daný INODE existuje a zda je možno ho zvolit / vybrat
	 * 
	 * @param integer $recordId ID INODE
	 * @param boolean $activeOnly zda brát v potaz pouze aktivní položky
	 * @return boolean
	 */
	public function isTreeSelectableNode($recordId, $activeOnly = true)
	{
		$row = $this->find($recordId)->current();
		
		if (null === $row) {
			return false;
		}
		if ($activeOnly) {
			if (!$row->active) {
				return false;
			}
		}
		
		return ($row->entry_type == self::INODE_ENTRY_TYPE_ROOT) 
			|| ($row->entry_type == self::INODE_ENTRY_TYPE_DIRECTORY) 
			|| ($row->entry_type == self::INODE_ENTRY_TYPE_TRASH);
	}
	
	/**
	 * Rozloží řetězec s názvem souboru na části
	 * 
	 * Pro řetězec '/usr/local/www/index.php' bude výsledek následující:
	 * ['dirname']   => '/usr/local/www'
	 * ['basename']  => 'index.php'
	 * ['filename']  => 'index'
	 * ['extension'] => 'php'
	 * 
	 * @param string $filename řetězec s názvem souboru
	 * @return array
	 */
	public function pathInfo($fileName)
	{
		$info = pathinfo($fileName);
		if (!isset($info['filename'])) {
			$length = strlen($info['basename']);
			$length += (isset($info['extension'])) ? -strlen($info['extension']) - 1 : 0;
			$info['filename'] = substr($info['basename'], 0, $length);
		}
		if (!isset($info['extension'])) {
			$info['extension'] = '';
		}
		return $info;
	}
	
	/**
	 * Přejmenování INODE
	 * 
	 * @param integer $recordId ID INODE
	 * @param string $name nový název
	 * @param boolean $setUpdateInfo zda nastavit datum poslední změny a uživatele
	 * @return mixed primární klíč záznamu
	 */
	public function renameRecord($recordId, $name, $setUpdateInfo = true)
	{
		$inode = $this->find($recordId)->current();
		
		// záznam do historie
		if(($inode->entry_type == self::INODE_ENTRY_TYPE_FILE)) {
			Core_Model_Vfs_History::getInstance()->addRecord($inode->id, false);
			$inode->version++;
		}
		
		$pathInfo = $this->pathInfo($name);
		
		$inode->name = $name;
		if ($inode->entry_type != self::INODE_ENTRY_TYPE_WIKI) {
			$inode->ext = (strlen($pathInfo['extension']) == 0) ? null : $pathInfo['extension'];
		}
		
		if ($setUpdateInfo) {
			if (null === $this->_date) {
				$this->_date = new Zend_Date();
			} else {
				$this->_date->setTimestamp(time());
			}
			$inode->modified = $this->_date->toString($this->_timestampFormat);
			$inode->id_modified_by = $this->_userIdentity->id;
		}
		
		return $inode->save();
	}
	
	/**
	 * Obnovení INODE z TRASH
	 * 
	 * @param integer $recordId ID INODE
	 * @return mixed primární klíč záznamu
	 */
	public function restoreRecord($recordId)
	{
		$inode = $this->find($recordId)->current();
		
		if (1 == $inode->active) {
			return 1;
		}
		
		// obnovení nadřazené položky
		$this->restoreRecord($inode->id_parent);
		
		// kontrola na položku se stejným názvem
		$inodeArray = $inode->toArray();
		$copyCount = 1;
		$duplicates = $this->checkInodeDuplicateName($inodeArray);
		
		while ($duplicates > 0) {
			$copyCount++;
			$pathInfo = $this->pathInfo($inodeArray['name']);
			
			// odstranění případné části '(kopie xx) z názvu souboru
			$pos = strpos($pathInfo['filename'], sprintf('(%d)', $copyCount));
			
			if (false !== $pos) {
				$copyCount++;
				$pathInfo['filename'] = substr($pathInfo['filename'], 0, $pos - 1);
			}
			
			if (null !== $inode->ext) {
				$newName = sprintf('%s (%d).%s', $pathInfo['filename'], $copyCount, $pathInfo['extension']);
			} else {
				$newName = sprintf('%s (%d)', $pathInfo['filename'], $copyCount);
			}
			$inodeArray['name'] = $newName;
			$duplicates = $this->checkInodeDuplicateName($inodeArray);
		}
		
		// obnovení položky
		$inode->id_trash = null;
		$inode->name = $inodeArray['name'];
		$inode->deleted = null;
		$inode->id_deleted_by = null;
		$inode->active = 1;
		
		return $inode->save();
	}
	
	/**
	 * Změna INODE
	 * 
	 * @param integer $recordId ID INODE
	 * @param array $data nová datapro INODE
	 * @param boolean $setUpdateInfo zda nastavit datum poslední změny a uživatele
	 * @return mixed primární klíč záznamu
	 */
	public function updateRecord($recordId, $data, $setUpdateInfo = true)
	{
		$row = $this->find($recordId)->current();
		
		if (null === $row) {
			throw new Eal_Application_Exception('Record not found', 1);
		}
		
		if (false !== $this->checkInodeDuplicateName($row)) {
			throw new Eal_Application_Exception("Record with name: '{$row->name}' already exists", 1);
		}
		
		foreach ($data as $key => $val) {
			$row->$key = $val;
		}
		
		if ($setUpdateInfo) {
			if (null === $this->_date) {
				$this->_date = new Zend_Date();
			} else {
				$this->_date->setTimestamp(time());
			}
			
			$row->modified = $this->_date->toString($this->_timestampFormat);
			$row->id_modified_by = $this->_userIdentity->id;
		}
		
		return $row->save();
	}
	
}

