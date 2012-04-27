<?php

$GLOBALS['TL_DCA']['tl_bbit_nav_hook'] = array(

	'config' => array(
		'dataContainer'		=> 'Table',
		'ctable'			=> array('tl_bbit_nav_injection'),
		'enableVersioning'	=> true,
		'onload_callback'	=> array(
		),
		'onsubmit_callback'	=> array(
		),
	),
	
	'list' => array(
		'sorting' => array(
			'mode'			=> 2,
			'fields'		=> array('title'),
			'panelLayout'	=> 'filter,limit;search',
			'disableGrouping' => true,
		),
		'label' => array(
			'fields'		=> array('title'),
			'format'		=> '%s',
//			'label_callback' => array('NavigationNewsDCA', 'renderNewsNavigationLabel'),
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
			'injections' => array(
				'label'	=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['injections'],
				'href'	=> 'table=tl_bbit_nav_injection',
				'icon'	=> 'edit.gif'
			),
			'edit' => array(
				'label'	=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['edit'],
				'href'	=> 'act=edit',
				'icon'	=> 'header.gif'
			),
			'copy' => array(
				'label'	=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['copy'],
				'href'	=> 'act=paste&amp;mode=copy',
				'icon'	=> 'copy.gif'
			),
			'delete' => array(
				'label'	=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['delete'],
				'href'	=> 'act=delete',
				'icon'	=> 'delete.gif',
				'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array(
				'label'	=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['show'],
				'href'	=> 'act=show',
				'icon'	=> 'show.gif'
			)
		),
	),
	
	'palettes'=> array(
		'__selector__'	=> array('hookType'),
		'default'		=> '{general_legend},title,hookType;',
		'entries'		=> '{general_legend},title,publish,hookType;'
			. '{navigation_legend},entries',
		'tree'			=> '{general_legend},title,publish,hookType;'
			. '{navigation_legend},tree',
	),
	
	'subpalettes'=> array(
	),
	
	'fields' => array(
		'title' => array(
			'label'			=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['title'],
			'inputType' 	=> 'text',
			'exclude'		=> true,
			'eval' => array(
				'mandatory'		=> true,
				'maxlength'		=> 255,
				'tl_class'		=> 'clr w50',
			)
		),
		'publish' => array(
			'label'			=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['publish'],
			'inputType'		=> 'checkbox',
			'exclude'		=> true,
			'filter'		=> true,
			'eval'			=> array(
				'tl_class'		=> 'w50 cbx m12',
			)
		),
		'hookType' => array(
			'label'			=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['hookType'],
			'inputType'		=> 'select',
			'exclude'		=> true,
			'default'		=> 'tree',
			'options'		=> array('tree', 'entries'),
			'reference'		=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['hookTypeOptions'],
			'eval'			=> array(
				'mandatory'		=> true,
				'submitOnChange'=> true,
				'tl_class'		=> 'clr w50',
			)
		),
		
		'tree' => array(
			'label'			=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['tree'],
			'inputType'		=> 'pageTree',
			'exclude'		=> true,
			'eval'			=> array(
				'mandatory'		=> true,
				'fieldType'		=> 'checkbox',
				'tl_class'		=> 'clr'
			)
		),
		
		'entries' => array(
			'label'			=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['entries'],
			'inputType' 	=> 'multiColumnWizard',
			'exclude' 		=> true,
			'eval' 			=> array(
				'mandatory'		=> true,
				'columnFields'	=> array(
					'link' => array(
						'label'		=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['entries_link'],
						'inputType'	=> 'text',
						'eval'		=> array('mandatory' => true, 'maxlength' => 255, 'style' => 'width:200px')
					),
					'href' => array(
						'label'		=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['entries_href'],
						'inputType'	=> 'text',
						'eval'		=> array('mandatory' => true, 'maxlength' => 255, 'rgxp' => 'url', 'style' => 'width:350px')
					),
					'target' => array(
						'label'		=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['entries_target'],
						'inputType'	=> 'checkbox',
						'eval'		=> array('style' => 'width:50px')
					),
					'css' => array(
						'label'		=> &$GLOBALS['TL_LANG']['tl_bbit_nav_hook']['entries_css'],
						'inputType'	=> 'text',
						'eval'		=> array('maxlength' => 255, 'style' => 'width:100px')
					),
				)
			),
			'load_callback'	=> array(),
			'save_callback'	=> array(),
		),
	)
		
);
