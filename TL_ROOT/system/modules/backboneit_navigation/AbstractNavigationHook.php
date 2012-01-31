<?php

abstract class AbstractNavigationHook implements NavigationHook {

	protected $arrConfig;
	
	protected $arrTree;
	
	protected $arrItems;
	
	protected $arrMounts;
	
	protected $blnBuilt = false;
	
	public function __construct(array $arrConfig = null) {
		$this->arrConfig = $arrConfig;
	}
	
	public function getConfig() {
		return $this->arrConfig;
	}
	
	public function setConfig(array $arrConfig = null) {
		$this->arrConfig = $arrConfig;
	}
	
	public function build($blnRebuild = false) {
		if(!$blnRebuild && $this->blnBuilt) {
			return;
		}
		$this->blnBuilt = false;
		$this->arrTree = array();
		$this->arrItems = array();
		$this->arrMounts = array();
		$this->doBuild();
		$this->blnBuilt = true;
	}
	
	public function getTree() {
		$this->build();
		return $this->arrTree;
	}
	
	public function getItems() {
		$this->build();
		return $this->arrItems;
	}
	
	public function getMounts() {
		$this->build();
		return $this->arrMounts;
	}
	
	protected abstract function doBuild();
	
}
