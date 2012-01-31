<?php

class EntriesHook extends AbstractNavigationHook {
	
	protected function doBuild() {
		$arrEntries = deserialize($this->arrConfig['entries'], true);
		
		if(!$arrEntries) {
			return;
		}
		
		foreach($arrEntries as $i => $arrEntry) {
			$arrEntry['class'] = $arrEntry['css'] . ' ' . $this->arrConfig['css'];
			$arrEntry['title'] = specialchars($arrEntry['link'], true);
			$arrEntry['pageTitle'] = $arrEntry['title'];
			
			$strID = __CLASS__ . '_' . $this->arrConfig['id'] . '_' . $i;
			$this->arrMounts[] = $strID;
			$this->arrItems[$strID] = $arrEntry;
		}
	}
	
}
