<?php

$GLOBALS['BE_MOD']['design']['page']['tables'][] = 'tl_bbit_navi_hook';
$GLOBALS['BE_MOD']['design']['page']['tables'][] = 'tl_bbit_navi_injection';

$GLOBALS['FE_MOD']['navigationMenu']['bbit_navi_menu'] = 'ModuleNavigationMenu';
//$GLOBALS['FE_MOD']['navigationMenu']['bbit_navi_chain'] = 'ModuleNavigationChain';

$GLOBALS['TL_HOOKS']['bbit_navi_tree'] = array_merge(
	(array) $GLOBALS['TL_HOOKS']['bbit_navi_tree'],
	(array) $GLOBALS['TL_HOOKS']['backboneit_navigation_tree']
);
$GLOBALS['TL_HOOKS']['backboneit_navigation_tree'] = &$GLOBALS['TL_HOOKS']['bbit_navi_tree'];

$GLOBALS['TL_HOOKS']['bbit_navi_menu'] = array_merge(
	(array) $GLOBALS['TL_HOOKS']['bbit_navi_menu'],
	(array) $GLOBALS['TL_HOOKS']['backboneit_navigation_menu']
);
$GLOBALS['TL_HOOKS']['backboneit_navigation_menu'] = &$GLOBALS['TL_HOOKS']['bbit_navi_menu'];


//$GLOBALS['TL_HOOKS']['bbit_navi_tree'][] = array('NavigationNews', 'hookNavigationTree');
