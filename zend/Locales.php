<?php

/**
 * Model pro tabulku národních prostředí
 */
class Application_Model_Locales extends Application_Model_Abstract
{
	
	/**
	 * Převodní tabulka DB engine IDENT na web locale ident
	 * 
	 * @var array
	 */
	protected static $_identToWebIdentList = array(
		'CZ' => 'cs-cz',
		'EN' => 'en',
	);
	
	protected static $_defaultRegionsList = array(
		"cs" => "CZ",
		"en" => "US"
	);
	
	/**
	 * Filtr pro seznam dostupných jazyků
	 * 
	 * @var array
	 */
	protected $_available;
	
	/**
	 * Seznam dostupných jazyků v databázi
	 * 
	 * @var array
	 */
	protected $_dbLocales;
	
	/**
	 * Constructor
	 * 
	 * @param array $availableLocales pole s klíči pro filtr dostupných jazyků
	 * @return void
	 */
	public function __construct($availableLocales = null)
	{
		parent::__construct();
		
		if (null !== $availableLocales) {
			$this->setAvailableLocales($availableLocales);
		}
	}
	
	/**
	 * Seznam dostupných jazyků pro web rozhraní
	 * 
	 * @throws Exception
	 * @return array
	 */
	private function _getDbLocales()
	{
		if (null === $this->_dbLocales) {
			if (null === $this->_available) {
				throw new Exception('Filter with available locales is empty, cannot retrieve locales from database');
			}
			
			$cache = null;
			try {
				$cache = Plugin_Application_Cache::getCache(Plugin_Application_Cache::CACHE_FAMILY_DATABASE);
				$cacheId = Plugin_Application_Cache::getIdPrefix(array(__CLASS__, __FUNCTION__));
			} catch (Plugin_Application_Cache_Exception $e) {
				// výjimka se ignoruje, nebude se načítat z cache
			} catch (Exception $e) {
				throw $e;
			}
			
			$rows = (null !== $cache) ? $cache->load($cacheId) : false;
			
			if (false === $rows) {
				$procName = $this->getProcNameByXMLResultset("WebJazykyList");
				$sql = $this->_db->quoteIntoProc($procName);
				$rows = $this->_db->fetchAll($sql);
				// seřazení výsledků pro nonXML resultset
				if (! $this->isXMLResultset()) {
					$rows = $this->orderBy($rows, array("NAZEV"));
				}
				
				$this->_dbLocales = array();
				foreach ($rows as $row) {
					$this->_dbLocales[$row["IDENT"]] = $row;
				}
				
				if (null !== $cache) {
					$cache->save($this->_dbLocales, $cacheId);
				}
			} else {
				$this->_dbLocales = $rows;
			} 
		}
		
		return $this->_dbLocales;
	}
	
	/**
	 * Seznam národních prostředí pro web v databázi
	 * 
	 * Metoda vrátí seznam dostupných locales jako pole hodnot IDENT => NAZEV,
	 * kde IDENT = klíč v DB formátu, např. 'cs-cz' a NAZEV = jméno jazyka.
	 * 
	 * @return array
	 */
	public function getLocales()
	{
		if (null === $this->_available) {
			throw new Exception('Filter with available locales is empty, cannot retrieve locales from database');
		}
		
		$locales = $this->_getDbLocales();
		$result = $this->_toAssocListSpec($locales);
		
		return $result;
		
		
		// DEBUG data
		/*
		return array(
			'cs-cz' => 'Čeština',
			'en' => 'English'
		);
		*/
	}
	
	/**
	 * Klíče jazyků pro komunikaci se zákazníkem, nadefinovaných v databázi
	 * 
	 * @return array
	 */
	public function getLocalesForCommunication()
	{
		$cache = Plugin_Application_Cache::getCache(Plugin_Application_Cache::CACHE_FAMILY_DATABASE);
		$cacheId = Plugin_Application_Cache::getIdPrefix(array(__CLASS__, __FUNCTION__));
			
		if (false === $result = $cache->load($cacheId)) {
			$procName = $this->getProcNameByXMLResultset("JazykyList");
			$sql = $this->_db->quoteIntoProc($procName);
			$locales = $this->_db->fetchAll($sql);
			// seřazení výsledků pro nonXML resultset
			if (! $this->isXMLResultset()) {
				$locales = $this->orderBy($locales, array("NAZEV"));
			}
			
			$result = $this->_toAssocList($locales);
			
			$cache->save($result, $cacheId);
		}
		
		return $result;
	}
	
	/**
	 * Vrátí resultset s nastavenými jazyky pro web a komunikaci u zákazníka
	 * 
	 * @param integer $customerId id zákazníka
	 * @return array
	 */
	public function getCustomerLocales($customerId)
	{
		$cache = Plugin_Application_Cache::getCache(Plugin_Application_Cache::CACHE_FAMILY_DATABASE);
		$cacheId = Plugin_Application_Cache::getIdPrefix(array(
			Plugin_Application_Session::getInstance()->getGlobalAppSessNS()->sessionCacheId,
			__CLASS__, __FUNCTION__
		));
		
		if (false === $result = $cache->load($cacheId)) {
			$sql = $this->_db->quoteIntoProc("ZakaznikJazyk", $customerId);
			$rows = $this->_db->fetchAll($sql);
			
			$result = array();
			if (count($rows) > 0) {
				$result = $rows[0];
			}
			
			$cache->save($result, $cacheId, array(), Plugin_Application_Session::getInstance()->getGlobalAppSessNS()->cacheLifetime);
		}
		
		return $result;
	}
	
	/**
	 * Vrátí klíč pro nastavený jazyk pro web u zákazníka
	 * 
	 * @param integer $customerId id zákazníka
	 * @return string|null
	 */
	public function getCustomerLocaleForWeb($customerId)
	{
		$cache = Plugin_Application_Cache::getCache(Plugin_Application_Cache::CACHE_FAMILY_DATABASE);
		$cacheId = Plugin_Application_Cache::getIdPrefix(array(
			Plugin_Application_Session::getInstance()->getGlobalAppSessNS()->sessionCacheId,
			__CLASS__, __FUNCTION__
		));
		
		if (false === $result = $cache->load($cacheId)) {
			$sql = $this->_db->quoteIntoProc("ZakaznikJazyk", $customerId);
			$rows = $this->_db->fetchAll($sql);
			
			$result = null;
			if (count($rows) > 0) {
				$result = self::specIdentToIdent($rows[0]['WEBJAZYK']);
			}
			
			$cache->save($result, $cacheId, array(), Plugin_Application_Session::getInstance()->getGlobalAppSessNS()->cacheLifetime);
		}
		
		return $result;
		
	}
	
	/**
	 * Vrátí klíče dostupných jazyků prostředí
	 * 
	 * @param array $locales pole s výčtem jazyků
	 * @param boolean $withTerritory pokud je FALSE, vrátí pouze jazyk (cs), jinak vrátí celý klíč (cs-cz)
	 * @return array
	 */
	public function getLocalesIdents($locales, $withTerritory = true)
	{
		if (!$withTerritory) {
			$result = array();
			foreach (array_keys($locales) as $key) {
				$result[] = substr($key, 0, 2);
			}
			return $result;
		}
		
		return array_keys($locales);
	}
	
	public static function getDefaultRegion($lang)
	{
		if (isset(self::$_defaultRegionsList[$lang])) {
			return self::$_defaultRegionsList[$lang];
		}
		
		return null;
	}
	
	/**
	 * Převod klíče jazyka z web ident formátu na Zend formát (mezinárodní)
	 * 
	 * @param string $ident web identifikátor jazyka
	 * @return string
	 */
	public static function webIdentToZendLocale($ident)
	{
		if (strlen($ident) > 2) {
			$identPcs = explode('-', $ident);
			$language = $identPcs[0];
			$teritory = isset($identPcs[1]) ? $identPcs[1] : null;
			$ident = implode('_', array(strtolower($language), strtoupper($teritory)));
		}
		
		if (!Zend_Locale::isLocale($ident, true)) {
			$errorMsg = "Unknown locale identifier '%s'";
			throw new Zend_Locale_Exception(sprintf($errorMsg, $ident));
		}
		
		return $ident;
	}
	
	/**
	 * Převod z web ident formátu na IDENT, používaný v engine databáze
	 * 
	 * @param string $ident
	 * @return string
	 */
	public static function webIdentToIdent($ident)
	{
		foreach (self::$_identToWebIdentList as $key => $val) {
			if (((2 === strlen($ident)) && (0 === strpos($val, $ident))) || ($val == $ident)) {
				return $key;
			}
		}
		
		return null;
	}
	
	/**
	 * Převod DB engine IDENT na ID
	 * 
	 * @param string $ident
	 * @return integer
	 */
	public function identToID($ident)
	{
		if (null === $this->_dbLocales) {
			$this->_getDbLocales();
		}
		
		$result = (isset($this->_dbLocales[$ident]))
			? $this->_dbLocales[$ident]["ID"]
			: 0;
		
		return $result;
	}
	
	public function webIdentToID($ident)
	{
		$result = 0;
		
		if (null !== $ident = self::webIdentToIdent($ident)) {
			$result = $this->identToID($ident);
		}
		
		return $result;
	}
	
	/**
	 * Převod ze speciálního IDENT jazyka, používaného v engine databáze na IDENT, používaný v Septimovi
	 * 
	 * @param string $specIdent identifikátor jazyka
	 * @return string|null
	 */
	public static function specIdentToIdent($specIdent)
	{
		if (isset(self::$_identToWebIdentList[$specIdent])) {
			return self::$_identToWebIdentList[$specIdent];
		}
		
		return null;
	}
	
	/**
	 * Převod jazyka z formátu pro Zend_Locale na web ident, užívaný v databázi
	 * 
	 * @param string $ident
	 * @param boolean $withTerritory pokud je FALSE, výsledek bude pouze jazyk bez teritoriální části
	 * @return string
	 */
	public static function zendLocaleToIdent($ident, $withTerritory = true)
	{
		if (!Zend_Locale::isLocale($ident, true)) {
			$errorMsg = $this->t->_("Unknown locale identifier '%s'");
			throw new Zend_Locale_Exception(sprintf($errorMsg, $ident));
		}
		
		if (strlen($ident) > 2) {
			$identPcs = explode('_', $ident);
			$language = $identPcs[0];
			$teritory = isset($identPcs[1]) ? $identPcs[1] : null;
			if ($withTerritory) {
				$ident = implode('-', array(strtolower($language), strtolower($teritory)));
			} else {
				$ident = strtolower($language);
			}
		}
		
		return $ident;
	}
	
	/**
	 * Nastaví filtr pro seznam dostupných jazyků
	 * 
	 * Dostupné jazyky v DB musí odpovídat filtru, jinak jsou ignorovány
	 * 
	 * @param array $values seznam klíčů dostupných jazyků
	 * @return Application_Model_Locales
	 */
	public function setAvailableLocales(array $values)
	{
		$filter = array();
		
		foreach ($values as $value) {
			if (!Zend_Locale::isLocale($value)) {
				$errorMsg = $this->t->_("Unknown locale '%s', cannot set available locales filter");
				throw new Zend_Locale_Exception(sprintf($errorMsg, $value));
			}
			$filter[] = substr($value, 0, 2);
		}
		
		if (count($filter) > 0) {
			$this->_available = $filter;
		}
		
		return $this;
	}
	
	/**
	 * Nastavení jazyka v databázi
	 * 
	 * Metoda nastaví použitý jazyk v databázi podle nastaveného
	 * jazyka ve webovém rozhraní.
	 * 
	 * @param integer $id
	 */
	public function setDbLanguage($id)
	{
		$sql = $this->_db->quoteIntoProc("SetLanguage", array($id));
		$this->_db->query($sql);
	}
	
	/**
	 * Převod záznamů na pole hodnot
	 * 
	 * Metoda převede záznamy v $resultset do jednorozměrného pole $key => $value 
	 * vhodného např. pro selectboxy formulářů.
	 * 
	 * @param array $resultset pole se záznamy
	 * @param string $key název sloupce pro hodnotu klíče
	 * @param string $value název sloupce pro hodnotu položky
	 * @return array
	 */
	protected function _toAssocList($resultset, $key = 'IDENT', $value = 'NAZEV')
	{
		$result = array();
		
		foreach($resultset as $row) {
			$result[$row[$key]] = ltrim($row[$value], '* ');
		}
			
		return $result;
	}
	
	/**
	 * Převod záznamů specifických jazyků Septima na pole hodnot
	 * 
	 * Metoda převede záznamy v $resultset do jednorozměrného pole $key => $value 
	 * vhodného např. pro selectboxy formulářů.
	 * 
	 * @param array $resultset pole se záznamy
	 * @param string $key název sloupce pro hodnotu klíče
	 * @param string $value název sloupce pro hodnotu položky
	 * @return array
	 */
	protected function _toAssocListSpec($resultset, $key = 'IDENT', $value = 'NAZEV')
	{
		$result = array();
		
		foreach($resultset as $row) {
			try {
				if (!isset(self::$_identToWebIdentList[$row[$key]])) {
					$errorMsg = $this->t->_("Unknown specific locale '%s' in available locales");
					throw new Zend_Locale_Exception(sprintf($errorMsg, $row[$key]));
				}
				$identSpec = self::$_identToWebIdentList[$row[$key]];
				$ident = self::webIdentToZendLocale($identSpec);
			} catch (Zend_Locale_Exception $e) {
				$errorMsg = $this->t->_("Unknown specific locale '%s' in available locales");
				throw new Zend_Locale_Exception(sprintf($errorMsg, $row[$key]));
			}
			$identLang = substr($ident, 0, 2);
			if (in_array($identLang, $this->_available)) {
				$result[$identSpec] = ltrim($row[$value], '* ');
			}
		}
			
		return $result;
	}
	
}
