<?php


class ModuleStaticNavigation extends AbstractModuleNavigation {
	
	protected $strTemplate = 'mod_backboneit_navigation_static';
	
	protected $strNavigation;
	
	public function generate() {
		if(TL_MODE == 'BE')
			return $this->generateBE('STATIC NAVIGATION MENU');
	
		$this->prepare();
		
		if($this->backboneit_navigation_defineRoots) {
			$arrRoots = deserialize($this->backboneit_navigation_roots, true);
		} else {
			$arrRoots = array($this->objPage->rootId);
		}
		
		if($this->backboneit_navigation_currentAsRoot) {
			$arrRoots[] = $this->objPage->id;
		}
		
//		echo '<pre>';
//		echo 'roots 2:';
//		print_r($arrRoots);
		
		if($this->backboneit_navigation_start > 0) {
			$arrRoots = $this->filterPages($arrRoots);
			for($i = 0, $n = $this->backboneit_navigation_start; $i < $n; $i++)
				$arrRoots = $this->getNextLevel($arrRoots);
			
		} elseif($this->backboneit_navigation_start < 0) {
			for($i = 0, $n = -$this->backboneit_navigation_start; $i < $n; $i++)
				$arrRoots = $this->getPrevLevel($arrRoots);
			$arrRoots = $this->filterPages($arrRoots);
			
		} else {
			$arrRoots = $this->filterPages($arrRoots);
		}
		
//		echo 'roots 3:';
//		print_r($arrRoots);
		
		$this->fetchItems($arrRoots);
		
//		echo 'items 1:';
//		print_r($this->arrItems);
//		echo '</pre>';
		
		$arrItems = array();
		foreach($arrRoots as $intRootID)
			if(isset($this->arrSubpages[$intRootID]))
				$arrItems = array_merge($arrItems, $this->arrSubpages[$intRootID]);
				
		$this->strNavigation = trim($this->renderNaviTree($arrItems));
		
		return $this->strNavigation ? parent::generate() : '';
	}
	
	protected function compile() {
		$this->Template->request = $this->getIndexFreeRequest(true);
		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->items = $this->strNavigation;
	}
	
}
