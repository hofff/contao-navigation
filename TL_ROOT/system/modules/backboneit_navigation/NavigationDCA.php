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
