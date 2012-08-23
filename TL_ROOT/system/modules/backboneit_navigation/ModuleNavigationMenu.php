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
		$this->compileNavigationTree($arrRootIDs, $intStop, $intHard);
		$this->executeTreeHook();
		$arrRootIDs = $this->executeMenuHook($arrRootIDs);
		$arrFirstIDs = $this->getFirstNavigationLevel($arrRootIDs);
		$this->strNavigation = trim($this->renderNavigationTree($arrFirstIDs, $intStop, $intHard));
		
		return $this->strNavigation ? parent::generate() : '';
	}
	
	protected function compile() {
		$this->Template->request = $this->getIndexFreeRequest(true);
		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->items = $this->strNavigation;
		$this->backboneit_navigation_addLegacyCss && $this->Template->legacyClass = ' mod_navigation';
	}
	
	protected function calculateRootIDs($intStop = PHP_INT_MAX) {
		$arrRootIDs = $this->backboneit_navigation_defineRoots
			? array_map('intval', deserialize($this->backboneit_navigation_roots, true))
			: array($GLOBALS['objPage']->rootId);
		
		$this->backboneit_navigation_currentAsRoot && array_unshift($arrRootIDs, $GLOBALS['objPage']->id);
		
		$arrConditions = array($this->getQueryPartHidden(!$this->backboneit_navigation_respectHidden, $this->backboneit_navigation_isSitemap));
		$this->backboneit_navigation_respectGuests && $arrConditions[] = $this->getQueryPartGuests();
		$this->backboneit_navigation_respectPublish && $arrConditions[] = $this->getQueryPartPublish();
		$strConditions = implode(' AND ', array_filter($arrConditions, 'strlen'));
		
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
		
		if($intStop == 0) { // special case, keep only roots within the current path
			$arrPath = array_keys($this->arrTrail);
			$arrPath[] = $this->varActiveID;
			$arrRootIDs = array_intersect($arrRootIDs, $arrPath);
		}
		
		return $arrRootIDs;
	}
	
	protected function compileNavigationTree(array $arrRootIDs, $intStop = PHP_INT_MAX, $intHard = PHP_INT_MAX) {
		if(!$arrRootIDs)
			return;
		
		$arrRootIDs = array_keys(array_flip($arrRootIDs));
		
		if($this->backboneit_navigation_includeStart) {
			//$arrConditions = array(
			//	$this->getQueryPartHidden($this->backboneit_navigation_showHidden, $this->backboneit_navigation_isSitemap),
			//	$this->getQueryPartPublish()
			//);
			//!$this->backboneit_navigation_showGuests && $arrConditions[] = $this->getQueryPartGuests();
			//$strConditions = implode(' AND ', array_filter($arrConditions, 'strlen'));
			//$strConditions && $strConditions = 'AND (' . $strConditions . ')';
		
			$objRoots = $this->objStmt->query(
				'SELECT	' . implode(',', $this->arrFields) . '
				FROM	tl_page
				WHERE	id IN (' . implode(',', $arrRootIDs) . ')
				AND		type != \'error_403\'
				AND		type != \'error_404\'
				'// . $strConditions
			);

			while($objRoots->next())
				$this->arrItems[$objRoots->id] = $objRoots->row();
			
			$this->fetchItems($arrRootIDs, $intStop, $intHard, 2);
			
		} else {
			$this->fetchItems($arrRootIDs, $intStop, $intHard);
		}
		
		$blnForwardResolution = !$this->backboneit_navigation_noForwardResolution;
		foreach($this->arrItems as &$arrItem)
			$arrItem = $this->compileNavigationItem($arrItem, $blnForwardResolution, self::HOOK_ENABLE);
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
	protected function fetchItems(array $arrPIDs, $intStop = PHP_INT_MAX, $intHard = PHP_INT_MAX, $intLevel = 1) {
		$intLevel = max(1, $intLevel);
		
		 // nothing todo
		 // $intLevel == $intHard + 1 requires subitem detection for css class "submenu" calculation
		if(!$arrPIDs || $intLevel > $intHard + 1)
			return;
		
		$arrConditions = array(
			$this->getQueryPartHidden($this->backboneit_navigation_showHidden, $this->backboneit_navigation_isSitemap),
			$this->getQueryPartPublish()
		);
		!$this->backboneit_navigation_showGuests && $arrConditions[] = $this->getQueryPartGuests();
		$strConditions = implode(' AND ', array_filter($arrConditions, 'strlen'));
		$strConditions && $strConditions = 'AND (' . $strConditions . ')';
		
		$strLevelQueryStart =
			'SELECT	' . implode(',', $this->arrFields) . '
			FROM	tl_page
			WHERE	pid IN (';
		$strLevelQueryEnd = ')
			AND		type != \'root\'
			AND		type != \'error_403\'
			AND		type != \'error_404\'
			' . $strConditions . '
			ORDER BY sorting';
		
		while($arrPIDs) {
			// if $arrEndPIDs == $arrPIDs the next $arrPIDs will be empty -> leave loop
			if($intLevel > $intHard) {
				$arrEndPIDs = $arrPIDs;
				
			} elseif($intLevel > $intStop) {
				$arrEndPIDs = array();
				foreach($arrPIDs as $intPID)
					if(!isset($this->arrTrail[$intPID]))
						$arrEndPIDs[$intPID] = true;
			}
			
			$objSubpages = $this->objStmt->query($strLevelQueryStart . implode(',', $arrPIDs) . $strLevelQueryEnd);
			
			if(!$objSubpages->numRows)
				break;
			
			$arrPIDs = array();
			while($arrPage = $objSubpages->fetchAssoc()) {
				if(isset($this->arrItems[$arrPage['id']]))
					continue;
					
				if(!$this->isPermissionGranted($arrPage))
					continue;
				
				if(!isset($arrEndPIDs[$arrPage['pid']])) {
					$this->arrSubitems[$arrPage['pid']][] = $arrPage['id']; // for order of items
					$this->arrItems[$arrPage['id']] = $arrPage; // item datasets
					$arrPIDs[] = $arrPage['id']; // ids of current layer (for next layer pids)
					
				} elseif(!isset($this->arrSubitems[$arrPage['pid']])) {
					$this->arrSubitems[$arrPage['pid']] = array();
				}
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
	protected function executeMenuHook(array $arrRootIDs, $blnForce = false) {
		if(!$blnForce && $this->backboneit_navigation_disableHooks)
			return $arrRootIDs;
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
	
	protected function getFirstNavigationLevel(array $arrRootIDs) {
		if($this->backboneit_navigation_includeStart)
			return $arrRootIDs;
			
		 // if we do not want to show the root level
		$arrFirstIDs = array();
		foreach($arrRootIDs as $varRootID)
			if(isset($this->arrSubitems[$varRootID]))
				$arrFirstIDs = array_merge($arrFirstIDs, $this->arrSubitems[$varRootID]);
				
		return $arrFirstIDs;
	}
	
}
