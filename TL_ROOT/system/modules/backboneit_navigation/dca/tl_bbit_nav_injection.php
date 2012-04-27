<?php

$GLOBALS['TL_DCA']['tl_bbit_nav_injection'] = array(

	'config' => array(
		'dataContainer'		=> 'Table',
		'ptable'			=> 'tl_bbit_nav_hook',
		'enableVersioning'	=> true,
		'onload_callback'	=> array(
		),
		'onsubmit_callback'	=> array(
		),
	),
	
	'list' => array(
		'sorting' => array(
			'mode'			=> 4,
			'fields'		=> array('id'),
			'panelLayout'	=> 'filter,limit;',
			'headerFields'	=> array('title', 'publish', 'hookType'),
			'child_record_callback'   => array('NavigationDCA', 'callbackInjectionRecord'),
		),
		'global_operations' => array(
			'all' => array(
				'label'	=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'	=> 'act=select',
				'class'	=> 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations' => array(
			'edit' => array(
				'label'	=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['edit'],
				'href'	=> 'act=edit',
				'icon'	=> 'edit.gif'
			),
			'copy' => array(
				'label'	=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['copy'],
				'href'	=> 'act=paste&amp;mode=copy',
				'icon'	=> 'copy.gif'
			),
			'delete' => array(
				'label'	=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['delete'],
				'href'	=> 'act=delete',
				'icon'	=> 'delete.gif',
				'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array(
				'label'	=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['show'],
				'href'	=> 'act=show',
				'icon'	=> 'show.gif'
			)
		),
	),
	
	'palettes'=> array(
		'__selector__'	=> array('injectionType'),
		'default'		=> '{general_legend},injectionType;',
		'page'			=> '{general_legend},injectionType,publish;'
			. '{injection_legend},pages,injection;'
	),
	
	'subpalettes'=> array(
	),
	
	'fields' => array(
		'injectionType' => array(
			'label'			=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['injectionType'],
			'inputType'		=> 'select',
			'exclude'		=> true,
			'default'		=> 'page',
			'options'		=> array('page'),
			'reference'		=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['injectionTypeOptions'],
			'eval'			=> array(
				'mandatory'		=> true,
				'tl_class'		=> 'clr w50',
			)
		),
		'publish' => array(
			'label'			=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['publish'],
			'inputType'		=> 'checkbox',
			'exclude'		=> true,
			'filter'		=> true,
			'eval'			=> array(
				'tl_class'		=> 'w50 cbx m12',
			)
		),
		'pages' => array(
			'label'			=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['pages'],
			'inputType'		=> 'pageTree',
			'exclude'		=> true,
			'eval'			=> array(
				'fieldType'		=> 'checkbox',
				'mandatory'		=> true,
				'tl_class'		=> 'clr'
			)
		),
		'injection' => array(
			'label'			=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['injection'],
			'inputType'		=> 'select',
			'exclude'		=> true,
			'default'		=> 'bottom',
			'options'		=> array('before', 'top', 'bottom', 'after'),
			'reference'		=> &$GLOBALS['TL_LANG']['tl_bbit_nav_injection']['injectionOptions'],
			'eval'			=> array(
				'mandatory'		=> true,
				'tl_class'		=> 'w50',
			)
		),
	)
		
);
