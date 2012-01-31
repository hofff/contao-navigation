<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_navi_rootSelectionType';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_navi_defineRoots';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_navi_defineStop';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_navi_defineHard';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'bbit_navi_defineDepth';

$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_navi_menu']
	= '{title_legend},name,headline,type;'
	. '{bbit_navi_root_legend},'
	. 'bbit_navi_currentAsRoot,bbit_navi_defineRoots,'
	. 'bbit_navi_start,bbit_navi_includeStart,'
	. 'bbit_navi_respectHidden,bbit_navi_respectPublish,'
	. 'bbit_navi_respectGuests;'
	. '{bbit_navi_legend},'
	. 'bbit_navi_defineStop,'
	. 'bbit_navi_defineHard,'
	. 'bbit_navi_showHidden,bbit_navi_showProtected,'
	. 'bbit_navi_showGuests,bbit_navi_isSitemap;'
	. '{template_legend:hide},navigationTpl,bbit_navi_addLegacyCss,'
	. 'bbit_navi_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},bbit_navi_noForwardResolution,bbit_navi_disableHooks,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_navi_chain']
	= '{title_legend},name,type,bbit_navi_rootSelectionType';
	
$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_navi_chainsingle']
	= '{title_legend},name,headline,type;'
	. '{bbit_navi_root_legend},'
	. 'bbit_navi_rootSelectionType,'
	. 'bbit_navi_roots,'
	. 'bbit_navi_respectPublish;'
	. '{bbit_navi_legend},'
	. 'bbit_navi_chainDirections,'
	. 'bbit_navi_showHidden,bbit_navi_showProtected,'
	. 'bbit_navi_showGuests;'
	. '{template_legend:hide},navigationTpl,'
	. 'bbit_navi_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},bbit_navi_noForwardResolution,bbit_navi_disableHooks,guests,cssID,space';
	
$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_navi_chainsubtrees']
	= '{title_legend},name,headline,type;'
	. '{bbit_navi_root_legend},'
	. 'bbit_navi_rootSelectionType,'
	. 'bbit_navi_roots,'
	. 'bbit_navi_start,bbit_navi_includeStart,'
	. 'bbit_navi_respectHidden,bbit_navi_respectPublish,'
	. 'bbit_navi_respectGuests;'
	. '{bbit_navi_legend},'
	. 'bbit_navi_chainDirections,'
	. 'bbit_navi_defineDepth,'
	. 'bbit_navi_showHidden,bbit_navi_showProtected;'
	. 'bbit_navi_showGuests;'
	. '{template_legend:hide},navigationTpl,'
	. 'bbit_navi_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},bbit_navi_noForwardResolution,bbit_navi_disableHooks,guests,cssID,space';
	
$GLOBALS['TL_DCA']['tl_module']['palettes']['bbit_navi_chaincurrent']
	= '{title_legend},name,headline,type;'
	. '{bbit_navi_root_legend},'
	. 'bbit_navi_rootSelectionType,'
	. '{bbit_navi_legend},'
	. 'bbit_navi_chainDirections,'
	. 'bbit_navi_showHidden,bbit_navi_showProtected;'
	. 'bbit_navi_showGuests;'
	. '{template_legend:hide},navigationTpl,'
	. 'bbit_navi_addFields;'
	. '{protected_legend:hide},protected;'
	. '{expert_legend:hide},bbit_navi_noForwardResolution,bbit_navi_disableHooks,guests,cssID,space';
	
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['bbit_navi_defineRoots']
	= 'bbit_navi_roots';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['bbit_navi_defineStop']
	= 'bbit_navi_stop';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['bbit_navi_defineHard']
	= 'bbit_navi_hard';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['bbit_navi_defineDepth']
	= 'bbit_navi_depth';

	

/***** GENERAL FIELDS *********************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_roots'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_roots'],
	'exclude'	=> true,
	'inputType'	=> 'pageTree',
	'eval'		=> array(
		'fieldType'		=> 'checkbox',
		'tl_class'		=> 'clr'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_start'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_start'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_includeStart'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_includeStart'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_respectHidden'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_respectHidden'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_respectPublish'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_respectPublish'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_respectGuests'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_respectGuests'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_showHidden'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_showHidden'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_showProtected'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_showProtected'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_showGuests'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_showGuests'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_addFields'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_addFields'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'options_callback' => array('NavigationDCA', 'callbackAddFieldsOptions'),
	'eval'		=> array(
		'multiple'		=> true,
		'tl_class'		=> 'clr'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_noForwardResolution'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_noForwardResolution'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_disableHooks'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_disableHooks'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
	)
);



/***** NAVIGATION MENU ********************************************************/
	
$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_currentAsRoot'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_currentAsRoot'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_defineRoots'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_defineRoots'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_defineStop'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_defineStop'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_stop'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_stop'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_defineHard'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_defineHard'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_hard'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_hard'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_isSitemap'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_isSitemap'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_addLegacyCss'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_addLegacyCss'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'w50 cbx m12'
	)
);



/***** NAVIGATION CHAIN *******************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_rootSelectionType'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_rootSelectionType'],
	'exclude'	=> true,
	'inputType'	=> 'select',
	'default'	=> 'current',
	'options'	=> array('subtrees', 'single', 'current'),
	'reference'	=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_rootSelectionType_options'],
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_chainDirections'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_chainDirections'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'default'	=> array('forward', 'backward'),
	'options'	=> array('forward', /*'top', */'backward'),
	'reference'	=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_chainDirections_options'],
	'eval'		=> array(
		'multiple'		=> true,
		'tl_class'		=> 'clr w50'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_defineDepth'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_defineDepth'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'submitOnChange' => true,
		'tl_class'		=> 'clr w50 cbx m12'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_depth'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_depth'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array(
		'maxlength'		=> 5,
		'rgxp'			=> 'digit',
		'tl_class'		=> 'w50'
	)
);
