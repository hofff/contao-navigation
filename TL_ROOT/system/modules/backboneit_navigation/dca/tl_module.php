<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_rootSelectionType';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_defineRoots';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_includeStart';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_defineStop';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_defineHard';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'backboneit_navigation_defineDepth';

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_menu']
	= '{title_legend},name,headline,type;'
	. '{backboneit_navigation_root_legend},'
	. 'backboneit_navigation_currentAsRoot,backboneit_navigation_defineRoots,'
	. 'backboneit_navigation_start,backboneit_navigation_respectHidden,'
	. 'backboneit_navigation_respectPublish,backboneit_navigation_respectGuests;'
	. '{backboneit_navigation_legend},'
	. 'backboneit_navigation_includeStart,'
	. 'backboneit_navigation_defineStop,'
	. 'backboneit_navigation_defineHard,'
	. 'backboneit_navigation_showHidden,backboneit_navigation_showProtected,'
	. 'backboneit_navigation_showGuests,backboneit_navigation_isSitemap;'
	. '{template_legend:hide},navigationTpl,backboneit_navigation_addLegacyCss,'
	. 'backboneit_navigation_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},backboneit_navigation_noForwardResolution,backboneit_navigation_showErrorPages,backboneit_navigation_disableHooks,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_chain']
	= '{title_legend},name,type,backboneit_navigation_rootSelectionType';

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_chainsingle']
	= '{title_legend},name,headline,type;'
	. '{backboneit_navigation_root_legend},'
	. 'backboneit_navigation_rootSelectionType,'
	. 'backboneit_navigation_roots,'
	. 'backboneit_navigation_respectPublish;'
	. '{backboneit_navigation_legend},'
	. 'backboneit_navigation_chainDirections,'
	. 'backboneit_navigation_showHidden,backboneit_navigation_showProtected,'
	. 'backboneit_navigation_showGuests;'
	. '{template_legend:hide},navigationTpl,'
	. 'backboneit_navigation_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},backboneit_navigation_noForwardResolution,backboneit_navigation_showErrorPages,backboneit_navigation_disableHooks,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_chainsubtrees']
	= '{title_legend},name,headline,type;'
	. '{backboneit_navigation_root_legend},'
	. 'backboneit_navigation_rootSelectionType,'
	. 'backboneit_navigation_roots,'
	. 'backboneit_navigation_start,backboneit_navigation_includeStart,'
	. 'backboneit_navigation_respectHidden,backboneit_navigation_respectPublish,'
	. 'backboneit_navigation_respectGuests;'
	. '{backboneit_navigation_legend},'
	. 'backboneit_navigation_chainDirections,'
	. 'backboneit_navigation_defineDepth,'
	. 'backboneit_navigation_showHidden,backboneit_navigation_showProtected;'
	. 'backboneit_navigation_showGuests;'
	. '{template_legend:hide},navigationTpl,'
	. 'backboneit_navigation_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},backboneit_navigation_noForwardResolution,backboneit_navigation_showErrorPages,backboneit_navigation_disableHooks,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_chaincurrent']
	= '{title_legend},name,headline,type;'
	. '{backboneit_navigation_root_legend},'
	. 'backboneit_navigation_rootSelectionType,'
	. '{backboneit_navigation_legend},'
	. 'backboneit_navigation_chainDirections,'
	. 'backboneit_navigation_showHidden,backboneit_navigation_showProtected;'
	. 'backboneit_navigation_showGuests;'
	. '{template_legend:hide},navigationTpl,'
	. 'backboneit_navigation_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},backboneit_navigation_noForwardResolution,backboneit_navigation_showErrorPages,backboneit_navigation_disableHooks,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['backboneit_navigation_defineRoots']
	= 'backboneit_navigation_roots';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['backboneit_navigation_includeStart']
	= 'backboneit_navigation_showHiddenStart';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['backboneit_navigation_defineStop']
	= 'backboneit_navigation_stop';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['backboneit_navigation_defineHard']
	= 'backboneit_navigation_hard';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['backboneit_navigation_defineDepth']
	= 'backboneit_navigation_depth';



/***** GENERAL FIELDS *********************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_roots'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_roots'],
	'exclude'	=> true,
	'inputType'	=> 'pageTree',
	'eval'		=> array(
		'multiple'		=> true,
		'fieldType'		=> 'checkbox',
		'tl_class'		=> 'clr'
	)
);

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

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectHidden'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectHidden'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
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

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectGuests'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectGuests'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_includeStart'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_includeStart'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange'=> true,
		'tl_class'		=> 'clr w50 cbx',
	),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showHiddenStart'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showHiddenStart'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx',
	),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showHidden'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showHidden'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showProtected'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showProtected'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showGuests'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showGuests'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
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
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showErrorPages'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showErrorPages'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_disableHooks'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_disableHooks'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
	)
);



/***** NAVIGATION MENU ********************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_currentAsRoot'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_currentAsRoot'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
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
		'maxlength'		=> 255,
		'tl_class'		=> 'w50'
	),
	'save_callback' => array(
		array('NavigationDCA', 'saveStop'),
	),
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

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_isSitemap'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_isSitemap'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
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



/***** NAVIGATION CHAIN *******************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_rootSelectionType'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_rootSelectionType'],
	'exclude'	=> true,
	'inputType'	=> 'select',
	'default'	=> 'current',
	'options'	=> array('subtrees', 'single', 'current'),
	'reference'	=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_rootSelectionType_options'],
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_chainDirections'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_chainDirections'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'default'	=> array('forward', 'backward'),
	'options'	=> array('forward', /*'top', */'backward'),
	'reference'	=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_chainDirections_options'],
	'eval'		=> array(
		'multiple'		=> true,
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineDepth'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineDepth'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_depth'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_depth'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'w50'
	)
);
