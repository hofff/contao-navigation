<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_nav_rootSelectionType';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_nav_defineRoots';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_nav_defineStop';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_nav_defineHard';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_nav_defineDepth';

$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_nav_menu']
	= '{title_legend},name,headline,type;'
	. '{bbit_nav_root_legend},'
	. 'bbit_nav_currentAsRoot,bbit_nav_defineRoots,'
	. 'bbit_nav_start,bbit_nav_includeStart,'
	. 'bbit_nav_respectHidden,bbit_nav_respectPublish,'
	. 'bbit_nav_respectGuests;'
	. '{bbit_nav_legend},'
	. 'bbit_nav_defineStop,'
	. 'bbit_nav_defineHard,'
	. 'bbit_nav_showHidden,bbit_nav_showProtected,'
	. 'bbit_nav_showGuests,bbit_nav_isSitemap;'
	. '{template_legend:hide},navigationTpl,bbit_nav_addLegacyCss,'
	. 'bbit_nav_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},bbit_nav_noForwardResolution,bbit_nav_disableHooks,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_nav_chain']
	= '{title_legend},name,type,bbit_nav_rootSelectionType';
	
$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_nav_chainsingle']
	= '{title_legend},name,headline,type;'
	. '{bbit_nav_root_legend},'
	. 'bbit_nav_rootSelectionType,'
	. 'bbit_nav_roots,'
	. 'bbit_nav_respectPublish;'
	. '{bbit_nav_legend},'
	. 'bbit_nav_chainDirections,'
	. 'bbit_nav_showHidden,bbit_nav_showProtected,'
	. 'bbit_nav_showGuests;'
	. '{template_legend:hide},navigationTpl,'
	. 'bbit_nav_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},bbit_nav_noForwardResolution,bbit_nav_disableHooks,guests,cssID,space';
	
$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_nav_chainsubtrees']
	= '{title_legend},name,headline,type;'
	. '{bbit_nav_root_legend},'
	. 'bbit_nav_rootSelectionType,'
	. 'bbit_nav_roots,'
	. 'bbit_nav_start,bbit_nav_includeStart,'
	. 'bbit_nav_respectHidden,bbit_nav_respectPublish,'
	. 'bbit_nav_respectGuests;'
	. '{bbit_nav_legend},'
	. 'bbit_nav_chainDirections,'
	. 'bbit_nav_defineDepth,'
	. 'bbit_nav_showHidden,bbit_nav_showProtected;'
	. 'bbit_nav_showGuests;'
	. '{template_legend:hide},navigationTpl,'
	. 'bbit_nav_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},bbit_nav_noForwardResolution,bbit_nav_disableHooks,guests,cssID,space';
	
$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_nav_chaincurrent']
	= '{title_legend},name,headline,type;'
	. '{bbit_nav_root_legend},'
	. 'bbit_nav_rootSelectionType,'
	. '{bbit_nav_legend},'
	. 'bbit_nav_chainDirections,'
	. 'bbit_nav_showHidden,bbit_nav_showProtected;'
	. 'bbit_nav_showGuests;'
	. '{template_legend:hide},navigationTpl,'
	. 'bbit_nav_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},bbit_nav_noForwardResolution,bbit_nav_disableHooks,guests,cssID,space';
	
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['bbit_nav_defineRoots']
	= 'bbit_nav_roots';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['bbit_nav_defineStop']
	= 'bbit_nav_stop';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['bbit_nav_defineHard']
	= 'bbit_nav_hard';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['bbit_nav_defineDepth']
	= 'bbit_nav_depth';

	

/***** GENERAL FIELDS *********************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_roots'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_roots'],
	'exclude'	=> true,
	'inputType'	=> 'pageTree',
	'eval'		=> array(
		'fieldType'		=> 'checkbox',
		'tl_class'		=> 'clr'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_start'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_start'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_includeStart'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_includeStart'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_respectHidden'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_respectHidden'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_respectPublish'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_respectPublish'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_respectGuests'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_respectGuests'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_showHidden'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_showHidden'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_showProtected'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_showProtected'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_showGuests'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_showGuests'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_addFields'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_addFields'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'options_callback' => array('NavigationDCA', 'callbackAddFieldsOptions'),
	'eval'		=> array(
		'multiple'		=> true,
		'tl_class'		=> 'clr'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_noForwardResolution'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_noForwardResolution'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_disableHooks'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_disableHooks'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
	)
);



/***** NAVIGATION MENU ********************************************************/
	
$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_currentAsRoot'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_currentAsRoot'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_defineRoots'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_defineRoots'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_defineStop'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_defineStop'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_stop'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_stop'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_defineHard'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_defineHard'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_hard'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_hard'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_isSitemap'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_isSitemap'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_addLegacyCss'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_addLegacyCss'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
	)
);



/***** NAVIGATION CHAIN *******************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_rootSelectionType'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_rootSelectionType'],
	'exclude'	=> true,
	'inputType'	=> 'select',
	'default'	=> 'current',
	'options'	=> array('subtrees', 'single', 'current'),
	'reference'	=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_rootSelectionType_options'],
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_chainDirections'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_chainDirections'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'default'	=> array('forward', 'backward'),
	'options'	=> array('forward', /*'top', */'backward'),
	'reference'	=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_chainDirections_options'],
	'eval'		=> array(
		'multiple'		=> true,
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_defineDepth'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_defineDepth'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_nav_depth'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_nav_depth'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'w50'
	)
);
