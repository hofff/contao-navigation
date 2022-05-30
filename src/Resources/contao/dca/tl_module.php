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
    . 'backboneit_navigation_showGuests,backboneit_navigation_isSitemap,'
    . 'backboneit_navigation_hideSingleLevel;'
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

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_roots'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_roots'],
    'exclude'   => true,
    'inputType' => 'pageTree',
    'eval'      => [
        'multiple'   => true,
        'fieldType'  => 'checkbox',
        'tl_class'   => 'clr',
        'orderField' => 'backboneit_navigation_roots_order',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_roots_order'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_roots_order'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_start'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_start'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => [
        'maxlength' => 5,
        'rgxp'      => 'digit',
        'tl_class'  => 'clr w50',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectHidden'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectHidden'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx m12',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectPublish'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectPublish'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectGuests'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectGuests'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_includeStart'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_includeStart'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'clr w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showHiddenStart'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showHiddenStart'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showHidden'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showHidden'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showProtected'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showProtected'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showGuests'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showGuests'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_addFields'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_addFields'],
    'exclude'          => true,
    'inputType'        => 'checkbox',
    'options_callback' => ['NavigationDCA', 'getPageFields'],
    'eval'             => [
        'multiple' => true,
        'tl_class' => 'clr',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_noForwardResolution'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_noForwardResolution'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx m12',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showErrorPages'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showErrorPages'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx m12',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_disableHooks'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_disableHooks'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx m12',
    ],
];


/***** NAVIGATION MENU ********************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_currentAsRoot'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_currentAsRoot'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineRoots'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineRoots'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineStop'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineStop'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'clr w50 cbx m12',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_stop'] = [
    'label'         => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_stop'],
    'exclude'       => true,
    'inputType'     => 'text',
    'eval'          => [
        'maxlength' => 255,
        'tl_class'  => 'w50',
    ],
    'save_callback' => [
        ['NavigationDCA', 'saveStop'],
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineHard'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineHard'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'clr w50 cbx m12',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_hard'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_hard'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => [
        'maxlength' => 5,
        'rgxp'      => 'digit',
        'tl_class'  => 'w50',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_isSitemap'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_isSitemap'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_hideSingleLevel'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_hideSingleLevel'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_addLegacyCss'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_addLegacyCss'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx m12',
    ],
];


/***** NAVIGATION CHAIN *******************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_rootSelectionType'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_rootSelectionType'],
    'exclude'   => true,
    'inputType' => 'select',
    'default'   => 'current',
    'options'   => ['subtrees', 'single', 'current'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_rootSelectionType_options'],
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'clr w50',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_chainDirections'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_chainDirections'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'default'   => ['forward', 'backward'],
    'options'   => [
        'forward', /*'top', */
        'backward',
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_chainDirections_options'],
    'eval'      => [
        'multiple' => true,
        'tl_class' => 'clr w50',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineDepth'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineDepth'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'clr w50 cbx m12',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_depth'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_depth'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => [
        'maxlength' => 5,
        'rgxp'      => 'digit',
        'tl_class'  => 'w50',
    ],
];
