<?php

$objDB = Database::getInstance();

echo "NAVIGATION_RUNONCE";

// renaming backboneit_navigation_* columns into bbit_nav_*
foreach(array('tl_module') as $strTable) {
	if(!$objDB->tableExists($strTable)) {
		continue;
	}
	$arrCreate = $objDB->query('SHOW CREATE TABLE `' . $strTable . '`')->row(true);
	$arrCreate = explode("\n", $arrCreate[1]);
	foreach($arrCreate as $strLine) {
		if(!preg_match('@^[^A-Za-z]*\`backboneit_navigation_@', $strLine)) {
			continue;
		}
		$strLine = preg_replace('@(`backboneit_navigation_([A-Za-z]+)`)@', '$1 `bbit_nav_$2`', $strLine, 1);
		$strLine = 'ALTER TABLE `' . $strTable . '` CHANGE COLUMN ' . rtrim($strLine, ',');
		echo $strLine;
//		$objDB->query($strLine);
	}
}

if($objDB->tableExists('tl_module')) {
	$objDB->query('
		UPDATE	`tl_module`
		SET		`type` = \'bbit_nav_menu\'
		WHERE	`type` = \'backboneit_navigation_menu\'
	');
}

exit;
