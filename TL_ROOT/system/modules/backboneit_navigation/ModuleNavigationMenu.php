<?php


class ModuleNavigationMenu extends AbstractModuleNavigation {
	
	protected $strTemplate = 'mod_backboneit_navigation_menu';
	
	protected $strNavigation;
	
	public function generate() {
		if(TL_MODE == 'BE')
			return $this->generateBE('NAVIGATION MENU');
			
		global $objPage;
		$arrRoots = $this->backboneit_navigation_defineRoots
			? deserialize($this->backboneit_navigation_roots, true)
			: array($objPage->rootId);
		$this->backboneit_navigation_currentAsRoot && array_unshift($arrRoots, $objPage->id);
		
		$strConditions = $this->getQueryPartHidden(!$this->backboneit_navigation_respectHidden);
		$this->backboneit_navigation_respectGuests && $strConditions .= $this->getQueryPartGuests();
		$this->backboneit_navigation_respectPublish && $strConditions .= $this->getQueryPartPublish();
		
		$strStartConditions = $this->backboneit_navigation_includeStart ? '' : $strConditions;
		
		if($this->backboneit_navigation_start > 0) {
			$arrRoots = $this->filterPages($arrRoots, $strConditions);
			for($i = 1, $n = $this->backboneit_navigation_start; $i < $n; $i++)
				$arrRoots = $this->getNextLevel($arrRoots, $strConditions);
			$arrRoots = $this->getNextLevel($arrRoots, $strStartConditions);
			
		} elseif($this->backboneit_navigation_start < 0) {
			for($i = 0, $n = -$this->backboneit_navigation_start; $i < $n; $i++)
				$arrRoots = $this->getPrevLevel($arrRoots);
			$arrRoots = $this->filterPages($arrRoots, $strStartConditions);
			
		} else {
			$arrRoots = $this->filterPages($arrRoots, $strStartConditions);
		}
		
		if($this->backboneit_navigation_includeStart) {
			$objRoots = $this->Database->execute(
				'SELECT	' . implode(',', $this->arrFields) . '
				FROM	tl_page
				WHERE	id IN (' . implode(',', $arrRoots) . ')
				AND		type != \'error_403\'
				AND		type != \'error_404\'
				' . $this->getQueryPartHidden($this->backboneit_navigation_showHidden)
				. $this->getQueryPartGuests()
				. $this->getQueryPartPublish());

			while($objRoots->next())
				$this->arrItems[$objRoots->id] = $this->compileNavigationItem($objRoots->row());
			
			$this->fetchItems($arrRoots, 2);
			
		} else {
			$arrItems = array();
			$this->fetchItems($arrRoots);
			foreach($arrRoots as $intRootID)
				if(isset($this->arrSubpages[$intRootID]))
					$arrItems = array_merge($arrItems, $this->arrSubpages[$intRootID]);
			$arrRoots = $arrItems;
		}
		
		foreach($this->arrItems as &$arrPage)
			$arrPage = $this->compileNavigationItem($arrPage);
			
		$this->strNavigation = trim($this->renderNaviTree($this->executeHook($arrRoots)));
		
		return $this->strNavigation ? parent::generate() : '';
	}
	
	protected function compile() {
		$this->Template->request = $this->getIndexFreeRequest(true);
		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->items = $this->strNavigation;
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
		
		$strQueryStart =
			'SELECT	' . implode(',', $this->arrFields) . '
			FROM	tl_page
			WHERE	pid IN (';
		$strQueryEnd = ')
			AND		type != \'root\'
			AND		type != \'error_403\'
			AND		type != \'error_404\'
			' . $this->getQueryPartHidden($this->backboneit_navigation_showHidden)
			. $this->getQueryPartGuests()
			. $this->getQueryPartPublish() . '
			ORDER BY sorting';
		
		while($arrPIDs && $intLevel <= $intHard) {
			$objSubpages = $this->Database->execute($strQueryStart . implode(',', $arrPIDs) . $strQueryEnd);
			
			if(!$objSubpages->numRows)
				break;
			
			$arrNextPIDs = array();
			while($objSubpages->next()) {
				if(isset($this->arrItems[$objSubpages->id]))
					continue;
					
				if(!$this->checkProtected($objSubpages))
					continue;
					
				$this->arrSubpages[$objSubpages->pid][] = $objSubpages->id; // for order of items
				$this->arrItems[$objSubpages->id] = $objSubpages->row(); // item datasets
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
	
	/**
	 * Executes the navigation hook.
	 * The callback receives the following parameters:
	 * $this - This navigation module instance
	 * $arrRoots - The IDs of the first navigation level
	 * 
	 * And should return a new root array or null
	 * 
	 * @param array $arrRoots The root pages before hook execution
	 * @return array $arrRoots The root pages after hook execution
	 */
	protected function executeHook(array $arrRoots) {
		if(!is_array($GLOBALS['TL_HOOKS']['backboneit_navigation_menu']))
			return $arrRoots;
			
		foreach($GLOBALS['TL_HOOKS']['backboneit_navigation_menu'] as $arrCallback) {
			$this->import($arrCallback[0]);
			$arrNewRoots = $this->{$arrCallback[0]}->{$arrCallback[1]}($this, $arrRoots);
			
			if($arrNewRoots !== null)
				$arrRoots = $arrNewRoots;
		}
		
		return $arrRoots;
	}
	
}
