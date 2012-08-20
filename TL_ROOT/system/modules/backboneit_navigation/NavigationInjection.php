<?php

interface NavigationInjection {
	
	public function getConfig();
	
	public function setConfig(array $arrConfig = null);
	
	public function hasInjectsFor(array $arrTree, array $arrItems);
	
	public function setInjects(array $arrMounts = null, array $arrTree = null, array $arrItems = null);
	
	public function injectInto(array &$arrTree, array &$arrItems);
	
	public function getInjectionPoints($blnRecalculate = false);
	
}
