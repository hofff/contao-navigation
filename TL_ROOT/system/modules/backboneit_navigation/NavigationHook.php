<?php

interface NavigationHook {
	
	public function getConfig();
	
	public function setConfig(array $arrConfig = null);
	
	public function build();
	
	public function getTree();
	
	public function getItems();
	
	public function getMounts();
	
	public function hasActive();
	
	public function getActive();
	
}
