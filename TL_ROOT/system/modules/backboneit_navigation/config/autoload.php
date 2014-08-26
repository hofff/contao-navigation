<?php

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'AbstractModuleNavigation'         => 'system/modules/backboneit_navigation/AbstractModuleNavigation.php',
	'ModuleNavigationChainPreorder'    => 'system/modules/backboneit_navigation/ModuleNavigationChainPreorder.php',
	'ModuleNavigationMenu'             => 'system/modules/backboneit_navigation/ModuleNavigationMenu.php',
	'NavigationDCA'                    => 'system/modules/backboneit_navigation/NavigationDCA.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_backboneit_navigation_menu' => 'system/modules/backboneit_navigation/templates',
	'nav_bbit'                       => 'system/modules/backboneit_navigation/templates',
));
