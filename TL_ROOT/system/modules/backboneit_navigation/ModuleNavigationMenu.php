<?php


class ModuleNavigationMenu extends AbstractModuleNavigation {
	
	protected $strTemplate = 'mod_backboneit_navigation_menu';
	
	protected $strNavigation;
	
	public function generate() {
		if(TL_MODE == 'BE')
			return $this->generateBE('NAVIGATION MENU');
			
		$intStop = $this->backboneit_navigation_defineStop ? $this->backboneit_navigation_stop : PHP_INT_MAX;
		$intHard = $this->backboneit_navigation_defineHard ? $this->backboneit_navigation_hard : PHP_INT_MAX;
		
		$arrRootIDs = $this->calculateRootIDs($intStop);
	
		if($this->backboneit_navigation_includeStart) {
			$objRoots = $this->Database->execute(
				'SELECT	' . implode(',', $this->arrFields) . '
				FROM	tl_page
				WHERE	id IN (' . implode(',', array_keys(array_flip($arrRootIDs))) . ')
				AND		type != \'error_403\'
				AND		type != \'error_404\'
				' . $this->getQueryPartHidden(!$this->backboneit_navigation_respectHidden)
				. $this->getQueryPartGuests()
				. $this->getQueryPartPublish());

			while($objRoots->next())
				$this->arrItems[$objRoots->id] = $objRoots->row();
			
			$this->fetchItems($arrRootIDs, $intStop, $intHard, 2);
			
		} else {
			$this->fetchItems($arrRootIDs, $intStop, $intHard);
		}
		
		foreach($this->arrItems as &$arrItem)
			$arrItem = $this->compileNavigationItem($arrItem);
		
		$arrRootIDs = $this->executeHook($arrRootIDs);
		
		if($this->backboneit_navigation_includeStart) { // get first navigation level, if we do not want to show the root level
			$arrFirstIDs = $arrRootIDs;
		} else {
			$arrFirstIDs = array();
			foreach($arrRootIDs as $intRootID)
				if(isset($this->arrSubpages[$intRootID]))
					$arrFirstIDs = array_merge($arrFirstIDs, $this->arrSubpages[$intRootID]);
		}
		
		$this->strNavigation = trim($this->renderNaviTree($arrFirstIDs, $intStop, $intHard));
		
		return $this->strNavigation ? parent::generate() : '';
	}
	
	protected function compile() {
		$this->Template->request = $this->getIndexFreeRequest(true);
		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->items = $this->strNavigation;
	}
	
	protected function calculateRootIDs($intStop = PHP_INT_MAX) {
		$arrRootIDs = $this->backboneit_navigation_defineRoots
			? deserialize($this->backboneit_navigation_roots, true)
			: array($GLOBALS['objPage']->rootId);
		
		$this->backboneit_navigation_currentAsRoot && array_unshift($arrRootIDs, $GLOBALS['objPage']->id);
		
		$strConditions = $this->getQueryPartHidden(!$this->backboneit_navigation_respectHidden);
		$this->backboneit_navigation_respectGuests && $strConditions .= $this->getQueryPartGuests();
		$this->backboneit_navigation_respectPublish && $strConditions .= $this->getQueryPartPublish();
		
		$strStartConditions = $this->backboneit_navigation_includeStart ? '' : $strConditions;
		
		if($this->backboneit_navigation_start > 0) {
			$arrRootIDs = $this->filterPages($arrRootIDs, $strConditions);
			for($i = 1, $n = $this->backboneit_navigation_start; $i < $n; $i++)
				$arrRootIDs = $this->getNextLevel($arrRootIDs, $strConditions);
			$arrRootIDs = $this->getNextLevel($arrRootIDs, $strStartConditions);
			
		} elseif($this->backboneit_navigation_start < 0) {
			for($i = 0, $n = -$this->backboneit_navigation_start; $i < $n; $i++)
				$arrRootIDs = $this->getPrevLevel($arrRootIDs);
			$arrRootIDs = $this->filterPages($arrRootIDs, $strStartConditions);
			
		} else {
			$arrRootIDs = $this->filterPages($arrRootIDs, $strStartConditions);
		}
		
		if($intStop == 0) { // special case, kick all roots outside of current path
			$arrFilteredIDs = array();
			foreach($arrRootIDs as $intRootID)
				if(isset($this->arrPath[$intRootID]))
					$arrFilteredIDs[] = $intRootID;
			$arrRootIDs = $arrFilteredIDs;
		}
		
		return $arrRootIDs;
	}
	
	/**
	 * Fetches page data for all navigation items below the given roots.
	 * 
	 * @param integer $arrRootIDs The root pages of the navigation.
	 * @param integer $intStop (optional, defaults to PHP_INT_MAX) The soft limit of depth.
	 * @param integer $intHard (optional, defaults to PHP_INT_MAX) The hard limit of depth.
	 * @param integer $intLevel (optional, defaults to 1) The level of the roots.
	 * @return null
	 */
	protected function fetchItems($arrRootIDs, $intStop = PHP_INT_MAX, $intHard = PHP_INT_MAX, $intLevel = 1) {
		$arrNextPIDs = array_keys(array_flip($arrRootIDs));
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
		
		while($intLevel <= $intHard) {
			
			if($intLevel <= $intStop) {
				$arrPIDs = $arrNextPIDs;
			} else {
				$arrPIDs = array();
				foreach($arrNextPIDs as $intPID)
					if(isset($this->arrPath[$intPID]))
						$arrPIDs[] = $intPID;
			}
			
			if(!$arrPIDs)
				break;
			
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
		}
	}
	
	/**
	 * Executes the navigation hook.
	 * The callback receives the following parameters:
	 * $this - This navigation module instance
	 * $arrRootIDs - The IDs of the first navigation level
	 * 
	 * And should return a new root array or null
	 * 
	 * @param array $arrRootIDs The root pages before hook execution
	 * @return array $arrRootIDs The root pages after hook execution
	 */
	protected function executeHook(array $arrRootIDs) {
		if(!is_array($GLOBALS['TL_HOOKS']['backboneit_navigation_menu']))
			return $arrRootIDs;
			
		foreach($GLOBALS['TL_HOOKS']['backboneit_navigation_menu'] as $arrCallback) {
			$this->import($arrCallback[0]);
			$arrNewRoots = $this->{$arrCallback[0]}->{$arrCallback[1]}($this, $arrRootIDs);
			
			if($arrNewRoots !== null)
				$arrRootIDs = $arrNewRoots;
		}
		
		return $arrRootIDs;
	}
	
}
