<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_defineRoots';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_defineStop';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_defineHard';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_rootSelectionType';

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_menu']
	= '{title_legend},name,headline,type;'
	. '{backboneit_navigation_root_legend},'
	. 'backboneit_navigation_currentAsRoot,backboneit_navigation_defineRoots,'
	. 'backboneit_navigation_start,backboneit_navigation_includeStart,'
	. 'backboneit_navigation_respectHidden,backboneit_navigation_respectGuests,'
	. 'backboneit_navigation_respectPublish;'
	. '{backboneit_navigation_legend},'
	. 'backboneit_navigation_defineStop,'
	. 'backboneit_navigation_defineHard,'
	. 'backboneit_navigation_showHidden,backboneit_navigation_showProtected,'
	. 'backboneit_navigation_isSitemap;'
	. '{template_legend:hide},navigationTpl,backboneit_navigation_addLegacyCss,'
	. 'backboneit_navigation_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},backboneit_navigation_noForwardResolution,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_preorder_single']
	= '{title_legend},name,headline,type;'
	. '{backboneit_navigation_root_legend},'
	. 'backboneit_navigation_rootSelectionType,'
	. 'backboneit_navigation_roots,'
	. '{backboneit_navigation_legend},'
	. 'backboneit_navigation_buttons,backboneit_navigation_showProtected;'
	. '{template_legend:hide},navigationTpl,'
	. 'backboneit_navigation_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},backboneit_navigation_noForwardResolution,guests,cssID,space';
	
$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_preorder_subtree']
	= '{title_legend},name,headline,type;'
	. '{backboneit_navigation_root_legend},'
	. 'backboneit_navigation_rootSelectionType,'
	. 'backboneit_navigation_roots,'
	. 'backboneit_navigation_start,backboneit_navigation_includeStart,'
	. 'backboneit_navigation_respectGuests,backboneit_navigation_respectHidden,'
	. 'backboneit_navigation_respectPublish;'
	. '{backboneit_navigation_legend},'
	. 'backboneit_navigation_buttons,'
	. 'backboneit_navigation_defineDepth,'
	. 'backboneit_navigation_showHidden,backboneit_navigation_showProtected;'
	. '{template_legend:hide},navigationTpl,'
	. 'backboneit_navigation_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},backboneit_navigation_noForwardResolution,guests,cssID,space';
	
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['backboneit_navigation_defineRoots']
	= 'backboneit_navigation_roots';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['backboneit_navigation_defineStop']
	= 'backboneit_navigation_stop';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['backboneit_navigation_defineHard']
	= 'backboneit_navigation_hard';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['backboneit_navigation_defineDepth']
	= 'backboneit_navigation_depth';

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_start'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_start'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_includeStart'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_includeStart'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
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

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineRoots'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineRoots'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_roots'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_roots'],
	'exclude'	=> true,
	'inputType'	=> 'pageTree',
	'eval'		=> array(
		'fieldType'		=> 'checkbox',
		'tl_class'		=> 'clr'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectGuests'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectGuests'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectHidden'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectHidden'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectPublish'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectPublish'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineStop'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineStop'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_stop'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_stop'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineHard'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineHard'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50 cbx m12'
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

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_isSitemap'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_isSitemap'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_addLegacyCss'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_addLegacyCss'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_addFields'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_addFields'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'options_callback' => array('NavigationDCA', 'getPageFields'),
	'eval'		=> array(
		'multiple'		=> true,
		'tl_class'		=> 'clr'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_noForwardResolution'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_noForwardResolution'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr cbx'
	)
);
