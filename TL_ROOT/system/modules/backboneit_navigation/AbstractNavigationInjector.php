<?php

abstract class AbstractNavigationInjector implements NavigationInjector {

	protected $arrConfig;
	
	protected $arrInjectionPoints;
	
	protected $blnCalculated;
	
	protected $arrMounts;
	
	protected $arrTree;
	
	protected $arrItems;
	
	
	public function __construct(array $arrConfig = null) {
		$this->arrConfig = $arrConfig;
	}
	
	public function getConfig() {
		return $this->arrConfig;
	}
	
	public function setConfig(array $arrConfig = null) {
		$this->arrConfig = $arrConfig;
	}
	
	public function hasInjectsFor(array $arrTree, array $arrItems) {
		$blnHasInjects = false;
		foreach($this->getInjectionPoints() as $arrInjectionPoint) {
			switch($arrInjectionPoint['where']) {
				case 'top':
				case 'bottom':
					if(isset($arrTree[$arrInjectionPoint['id']])) {
						$blnHasInjects = true;
						break 2;
					}
					break;
					
				case 'before':
				case 'after':
					if($arrItems[$arrInjectionPoint['id']]['ancestors']) {
						$blnHasInjects = true;
						break 2;
					}
					break;
			}
		}
		return $blnHasInjects;
	}
	
	public function setInjects(array $arrMounts = null, array $arrTree = null, array $arrItems = null) {
		$this->arrMounts = $arrMounts;
		$this->arrTree = $arrTree;
		$this->arrItems = $arrItems;
	}
	
	public function injectInto(array &$arrTree, array &$arrItems) {
		if(!$this->arrMounts) {
			return;
		}
		
		foreach($this->getInjectionPoints() as $arrInjectionPoint) {
			switch($arrInjectionPoint['where']) {
				case 'top':
					if(isset($arrTree[$arrInjectionPoint['id']])) {
						$arrTree[$arrInjectionPoint['id']] = array_merge($this->arrMounts, $arrTree[$arrInjectionPoint['id']]);
						$arrAncestors[$arrInjectionPoint['id']] = true;
					}
					break;
					
				case 'bottom':
					if(isset($arrTree[$arrInjectionPoint['id']])) {
						$arrTree[$arrInjectionPoint['id']] = array_merge($arrTree[$arrInjectionPoint['id']], $this->arrMounts);
						$arrAncestors[$arrInjectionPoint['id']] = true;
					}
					break;
					
				case 'before':
					if($arrItems[$arrInjectionPoint['id']]['ancestors']) foreach($arrItems['ancestors'] as $varAncestorID => $_) {
						if(isset($arrTree[$varAncestorID])) {
							array_key_insert($arrTree[$varAncestorID], $arrInjectionPoint['id'], $this->arrMounts, true);
							$arrAncestors[$varAncestorID] = true;
						}
					}
					break;
					
				case 'after':
					if($arrItems[$arrInjectionPoint['id']]['ancestors']) foreach($arrItems['ancestors'] as $varAncestorID) {
						if(isset($arrTree[$varAncestorID])) {
							array_key_insert($arrTree[$varAncestorID], $arrInjectionPoint['id'], $this->arrMounts, false);
							$arrAncestors[$varAncestorID] = true;
						}
					}
					break;
			}
		}
		
		if($this->arrItems) foreach($this->arrItems as $varID => $arrItem) {
			$arrItems[$varID] = $arrItem;
		}
		
		foreach($this->arrMounts as $varID) {
			if($arrItems[$varID]['ancestors']) {
				$arrItems[$varID]['ancestors'] = array_merge($arrItems[$varID]['ancestors'], $arrAncestors);
			} elseif(isset($arrItems[$varID])) {
				$arrItems[$varID]['ancestors'] = $arrAncestors;
			}
		}
		
		if($this->arrTree) foreach($this->arrTree as $varParentID => $arrChildrenIDs) {
			foreach($arrChildrenIDs as $varID) {
				if(isset($arrItems[$varID])) {
					$arrItems[$varID]['ancestors'][$varParentID] = true;
				}
			}
			$arrTree[$varParentID] = $arrChildrenIDs;
		}
	}
	
	public function getInjectionPoints($blnRecalculate = false) {
		if($blnRecalculate || !$this->blnCalculated) {
			$this->blnCalculated = false;
			$this->arrInjectionPoints = array();
			$this->calculateInjectionPoints();
			$this->blnCalculated = true;
		}
		
		return $this->arrInjectionPoints;
	}
	
	protected abstract function calculateInjectionPoints();
	
}

function array_key_insert(array &$arrOriginal, $varKey, array $arrInsert, $blnBefore = false) {
	if(!isset($arrOriginal[$varKey])) {
		return;
	}
	$arrNew = array();
	foreach($arrOriginal as $varOriginalKey => $varOriginalValue) {
		if($k !== $varKey) {
			$arrNew[$varOriginalKey] = $varOriginalValue;
		} elseif($blnBefore) {
			foreach($arrInsert as $varInsertKey => $varInsertValue) {
				$arrNew[$varInsertKey] = $varInsertValue;
			}
			$arrNew[$varOriginalKey] = $varOriginalValue;
		} else {
			$arrNew[$varOriginalKey] = $varOriginalValue;
			foreach($arrInsert as $varInsertKey => $varInsertValue) {
				$arrNew[$varInsertKey] = $varInsertValue;
			}
		}
	}
	$arrOriginal = $arrNew;
}
