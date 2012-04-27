<?php

class ModuleNavigationChainPreorder extends AbstractModuleNavigation {
	
	protected $strTemplate = 'mod_bbit_nav_chain_preorder';
	
	protected $strNavigation;
	
	public function generate() {
		if(TL_MODE == 'BE')
			return $this->generateBE('NAVIGATION CHAIN PREORDER');
		
		return $this->strNavigation ? parent::generate() : '';
	}
	
	protected function compile() {
		$this->Template->request = $this->getIndexFreeRequest(true);
		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->items = $this->strNavigation;
	}
	
	/**
	 * Executes the navigation chain hook.
	 * 
	 * The callback receives the following parameters:
	 * $this - This navigation module instance
	 * $arrChainIDs - The (ordered) IDs of the navigation chain to be rendered.
	 * 
	 * And should return a new chain array or null
	 * 
	 * @param array $arrRootIDs The root pages before hook execution
	 * @return array $arrRootIDs The root pages after hook execution
	 */
	protected function executeChainHook(array $arrChainIDs, $blnForce = false) {
		if(!$blnForce && $this->bbit_nav_disableHooks)
			return $arrChainIDs;
		if(!is_array($GLOBALS['TL_HOOKS']['backboneit_navigation_chain']))
			return $arrChainIDs;
			
		foreach($GLOBALS['TL_HOOKS']['backboneit_navigation_chain'] as $arrCallback) {
			$this->import($arrCallback[0]);
			$arrNewChain = $this->{$arrCallback[0]}->{$arrCallback[1]}($this, $arrChainIDs);
			
			if($arrNewChain !== null)
				$arrChainIDs = $arrNewChain;
		}
		
		return $arrChainIDs;
	}
	
}
