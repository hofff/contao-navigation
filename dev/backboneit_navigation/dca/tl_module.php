<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_static']
	= '{title_legend},name,headline,type;'
	. '{nav_legend},backboneit_navigation_skip,'
	. 'backboneit_navigation_ignoreGuests,backboneit_navigation_ignoreHidden,'
	. 'backboneit_navigation_stop,backboneit_navigation_hard,'
	. 'backboneit_navigation_showProtected,backboneit_navigation_showHidden;'
	. '{reference_legend:hide},backboneit_navigation_currentAsRoot,defineRoot;'
	. '{template_legend:hide},navigationTpl;'
	. '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_skip'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_skip'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_ignoreGuests'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_ignoreGuests'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_ignoreHidden'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_ignoreHidden'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_stop'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_stop'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_hard'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_hard'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showProtected'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showProtected'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showHidden'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showHidden'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_currentAsRoot'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_currentAsRoot'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);
