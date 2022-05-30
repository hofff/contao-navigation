<?php

use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'hofff_navigation_rootSelectionType';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'hofff_navigation_defineRoots';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'hofff_navigation_includeStart';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'hofff_navigation_defineStop';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'hofff_navigation_defineHard';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'hofff_navigation_defineDepth';

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_menu']
    = '{title_legend},name,headline,type;'
    . '{hofff_navigation_root_legend},'
    . 'hofff_navigation_currentAsRoot,hofff_navigation_defineRoots,'
    . 'hofff_navigation_start,hofff_navigation_respectHidden,'
    . 'hofff_navigation_respectPublish,hofff_navigation_respectGuests;'
    . '{hofff_navigation_legend},'
    . 'hofff_navigation_includeStart,'
    . 'hofff_navigation_defineStop,'
    . 'hofff_navigation_defineHard,'
    . 'hofff_navigation_showHidden,hofff_navigation_showProtected,'
    . 'hofff_navigation_showGuests,hofff_navigation_isSitemap,'
    . 'hofff_navigation_hideSingleLevel;'
    . '{template_legend:hide},navigationTpl,hofff_navigation_addLegacyCss,'
    . 'hofff_navigation_addFields;'
    . '{protected_legend:hide},protected;'
    . '{expert_legend:hide},hofff_navigation_noForwardResolution,hofff_navigation_showErrorPages,hofff_navigation_disableHooks,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_navigation_chain']
    = '{title_legend},name,type,hofff_navigation_rootSelectionType';

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


/***** GENERAL FIELDS *********************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_roots'] = [
    'exclude'   => true,
    'inputType' => 'pageTree',
    'eval'      => [
        'multiple'   => true,
        'fieldType'  => 'checkbox',
        'tl_class'   => 'clr',
        'orderField' => 'backboneit_navigation_roots_order',
    ],
    'sql'       => [
        'type'    => Types::BLOB,
        'default' => null,
        'notnull' => false,
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_roots_order'] = [
    'sql' => [
        'type'    => Types::BLOB,
        'default' => null,
        'notnull' => false,
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_start'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => [
        'maxlength' => 5,
        'rgxp'      => 'digit',
        'tl_class'  => 'clr w50',
    ],
    'sql'       => [
        'type'    => Types::SMALLINT,
        'default' => 0,
        'length'  => 5,
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectHidden'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx m12',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectPublish'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_respectGuests'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_includeStart'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'clr w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showHiddenStart'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showHidden'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showProtected'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showGuests'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_addFields'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'multiple' => true,
        'tl_class' => 'clr',
    ],
    'sql'       => [
        'type'    => Types::BLOB,
        'default' => null,
        'notnull' => false,
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_noForwardResolution'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx m12',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_showErrorPages'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx m12',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_disableHooks'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx m12',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];


/***** NAVIGATION MENU ********************************************************/

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_currentAsRoot'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineRoots'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineStop'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'clr w50 cbx m12',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_stop'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => [
        'maxlength' => 255,
        'tl_class'  => 'w50',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 255,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_defineHard'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'clr w50 cbx m12',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_hard'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => [
        'maxlength' => 5,
        'rgxp'      => 'digit',
        'tl_class'  => 'w50',
    ],
    'sql'       => [
        'type'    => Types::SMALLINT,
        'default' => 0,
        'length'  => 5,
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_isSitemap'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_hideSingleLevel'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'clr w50 cbx',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['backboneit_navigation_addLegacyCss'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'w50 cbx m12',
    ],
    'sql'       => [
        'type'    => Types::STRING,
        'length'  => 1,
        'default' => '',
    ],
];
