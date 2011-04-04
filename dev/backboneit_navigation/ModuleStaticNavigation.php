<?php

class ModuleStaticNavigation extends Module {
	
	protected $strTemplate = 'mod_backboneit_navigation_static';
	
	protected $strNavigation;
	
	public function generate() {
		
		if(TL_MODE == 'BE') {
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### STATIC NAVIGATION MENU ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
	
		$objNavi = new StaticNavigation();
		$objNavi->skip = $this->backboneit_navigation_skip;
		$objNavi->ignoreGuests = $this->backboneit_navigation_ignoreGuests;
		$objNavi->ignoreHidden = $this->backboneit_navigation_ignoreHidden;
		$objNavi->stop = $this->backboneit_navigation_stop;
		$objNavi->hard = $this->backboneit_navigation_hard;
		$objNavi->showProtected = $this->backboneit_navigation_showProtected;
		$objNavi->showHidden = $this->backboneit_navigation_showHidden;
		$objNavi->template = $this->navigationTpl;
		if($this->backboneit_navigation_currentAsRoot) {
			global $objPage;
			$objNavi->root = $objPage->id;	
		} elseif($this->defineRoot) {
			$objNavi->root = $this->rootPage;
		}
		$this->strNavigation = trim($objNavi->generate());
		
		return $this->strNavigation ? parent::generate() : '';
	}
	
	protected function compile() {
		$this->Template->request = $this->getIndexFreeRequest(true);
		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->items = $this->strNavigation;
	}
	
}
