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
	
	/**
	 * Fetches page data for all navigation items below the given roots.
	 * 
	 * @param integer $arrRoots The root pages of the navigation.
	 * @param integer $intLevel (optional, defaults to 1) The level of the roots.
	 * @return null
	 */
	protected function fetchItems($arrRoots, $intLevel = 1) {
		$intHard = $this->backboneit_navigation_hard ? $this->backboneit_navigation_hard : PHP_INT_MAX;
		$intStop = $this->backboneit_navigation_stop ? $this->backboneit_navigation_stop : PHP_INT_MAX;
		$arrPIDs = array_keys(array_flip($arrRoots));
		$intLevel = max(1, $intLevel);
		
		while($arrPIDs && $intLevel <= $intHard) {
			$objSubpages = $this->Database->execute($this->strLevelQueryStart . implode(',', $arrPIDs) . $this->strLevelQueryEnd);
			
			if(!$objSubpages->numRows)
				break;
			
			$arrNextPIDs = array();
			while($objSubpages->next()) {
				if(isset($arrItems[$objSubpages->id]))
					continue;
					
				if(!$this->checkProtected($objSubpages))
					continue;
					
				$this->arrSubpages[$objSubpages->pid][] = $objSubpages->id; // for order of items
				$this->arrItems[$objSubpages->id] = $this->compileNavigationItem($objSubpages->row()); // item datasets
				$arrNextPIDs[] = $objSubpages->id; // ids of current layer (for next layer pids)
			}
			
			$intLevel++;
			
			if($intLevel <= $intStop) {
				$arrPIDs = $arrNextPIDs;
			} else {
				$arrPIDs = array();
				foreach($arrNextPIDs as $intPID)
					if(isset($this->arrPath[$intPID]))
						$arrPIDs[] = $intPID;
			}
		}
	}
	
	protected function compile() {
		$this->Template->request = $this->getIndexFreeRequest(true);
		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->items = $this->strNavigation;
	}
	
}
