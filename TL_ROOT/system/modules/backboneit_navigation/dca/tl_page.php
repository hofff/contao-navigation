<?php

$GLOBALS['TL_DCA']['tl_page']['list']['global_operations'] = array_merge(
	array(
		'backboneit_navigation' => array(
			'label'	=> &$GLOBALS['TL_LANG']['MSC']['bbit_navi'],
			'href'	=> 'table=tl_bbit_navi_hook'
		)
	),
	$GLOBALS['TL_DCA']['tl_page']['list']['global_operations']
);
