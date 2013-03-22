<?php

class NavigationDCA extends Backend {
	
	public function getPageFields() {
		$this->loadLanguageFile('tl_page');
		$this->loadDataContainer('tl_page');
		
		$arrFields = array();
		
		foreach($GLOBALS['TL_DCA']['tl_page']['fields'] as $strField => $arrConfig)
			if(!isset(AbstractModuleNavigation::$arrDefaultFields[$strField]))
				$arrFields[$strField] = &$arrConfig['label'][0];
		
		return $arrFields;
	}
	
	public function saveStop($varValue) {
		$intMin = -1;
		foreach(array_map('intval', explode(',', strval($varValue))) as $intLevel) if($intLevel > $intMin) {
			$arrStop[] = $intMin = $intLevel;
		}
		return implode(',', $arrStop);
	}
	
	protected function __construct() {
		parent::__construct();
	}
	
	private static $objInstance;
	
	public static function getInstance() {
		if(!isset(self::$objInstance))
			self::$objInstance = new self();
			
		return self::$objInstance;
	}
	
}
