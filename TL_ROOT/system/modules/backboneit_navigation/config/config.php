<?php

$GLOBALS['BE_MOD']['design']['page']['tables'][] = 'tl_bbit_nav_hook';
$GLOBALS['BE_MOD']['design']['page']['tables'][] = 'tl_bbit_nav_injection';

$GLOBALS['FE_MOD']['navigationMenu']['bbit_nav_menu'] = 'ModuleNavigationMenu';
//$GLOBALS['FE_MOD']['navigationMenu']['bbit_nav_chain'] = 'ModuleNavigationChain';

$GLOBALS['TL_HOOKS']['bbit_nav_tree'] = array_merge(
	(array) $GLOBALS['TL_HOOKS']['bbit_nav_tree'],
	(array) $GLOBALS['TL_HOOKS']['backboneit_navigation_tree']
);
$GLOBALS['TL_HOOKS']['backboneit_navigation_tree'] = &$GLOBALS['TL_HOOKS']['bbit_nav_tree'];

$GLOBALS['TL_HOOKS']['bbit_nav_menu'] = array_merge(
	(array) $GLOBALS['TL_HOOKS']['bbit_nav_menu'],
	(array) $GLOBALS['TL_HOOKS']['backboneit_navigation_menu']
);
$GLOBALS['TL_HOOKS']['backboneit_navigation_menu'] = &$GLOBALS['TL_HOOKS']['bbit_nav_menu'];


//$GLOBALS['TL_HOOKS']['bbit_nav_tree'][] = array('NavigationNews', 'hookNavigationTree');
