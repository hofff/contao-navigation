<?php

/**
 * Abstract base class for navigation modules
 *
 * Navigation item array layout:
 * Before rendering:
 * id			=> the ID of the current item (optional)
 * isTrail		=> whether this item is in the trail path
 * class		=> CSS classes
 * title		=> page name with Insert-Tags stripped and XML specialchars replaced by their entities
 * pageTitle	=> page title with Insert-Tags stripped and XML specialchars replaced by their entities
 * link			=> page name (with Insert-Tags and XML specialchars NOT replaced; as stored in the db)
 * href			=> URL of target page
 * nofollow		=> true, if nofollow should be set on rel attribute
 * target		=> either ' onclick="window.open(this.href); return false;"' or empty string
 * description	=> page description with line breaks (\r and \n) replaced by whitespaces
 *
 * Calculated while rendering:
 * subitems		=> subnavigation as HTML string or empty string (rendered if subpages & items setup correctly)
 * isActive		=> whether this item is the current active navigation item
 *
 * Following CSS classes are calculated while rendering: level_x, trail, sibling, submenu, first, last
 *
 * Additionally all page dataset values from the database are available unter their field name,
 * if the field name does not collide with the listed keys.
 *
 * For the collisions of the Contao core page dataset fields the following keys are available:
 * _type
 * _title
 * _pageTitle
 * _target
 * _description
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractModuleNavigation extends Module {

	const HOOK_DISABLE = 0;
	const HOOK_ENABLE = 1;
	const HOOK_FORCE = 2;

	public static $arrDefaultFields = array(
		'id'			=> true,
		'pid'			=> true,
		'sorting'		=> true,
		'tstamp'		=> true,
		'type'			=> true,
		'alias'			=> true,
		'title'			=> true,
		'protected'		=> true,
		'groups'		=> true,
		'jumpTo'		=> true,
		'pageTitle'		=> true,
		'target'		=> true,
		'description'	=> true,
		'url'			=> true,
		'robots'		=> true,
		'cssClass'		=> true,
		'accesskey'		=> true,
		'tabindex'		=> true
	);

	protected $arrFields = array(); // the fields to use for navigation tpl
	protected $strJumpToQuery;
	protected $strJumpToFallbackQuery;

	protected $objStmt; // a reusable stmt object

	protected $arrGroups; // set of groups of the current user
	protected $arrTrail; // same as trail but with current page included

	public $varActiveID; // the id of the active page

	public $arrItems = array(); // compiled page datasets
	public $arrSubitems = array(); // ordered IDs of subnavigations
	/** @Deprecated */ public $arrSubpages;

	protected $blnTreeHook = false; // execute tree hook?
	protected $blnItemHook = false; // execute item hook?

	public function __construct($objModule, $strColumn = 'main') {
		parent::__construct($objModule, $strColumn);
		if(TL_MODE == 'BE')
			return;

		$this->arrSubpages = &$this->arrSubitems; // for deprecated compat

		$this->import('Database');

		$this->varActiveID = $this->backboneit_navigation_isSitemap || $this->Input->get('articles') ? false : $GLOBALS['objPage']->id;
		$this->arrTrail = array_flip($GLOBALS['objPage']->trail);

		if(FE_USER_LOGGED_IN) {
			$this->import('FrontendUser', 'User');
			$this->User->groups && $this->arrGroups = $this->User->groups;
		}

		if(!strlen($this->navigationTpl))
			$this->navigationTpl = 'nav_default';

		$arrFields = deserialize($this->backboneit_navigation_addFields, true);

		if(count($arrFields) > 10) {
			$this->arrFields[] = '*';

		} elseif($arrFields) {
			$arrFields = array_flip($arrFields);
			foreach($this->Database->listFields('tl_page') as $arrField)
				if(isset($arrFields[$arrField['name']]))
					$this->arrFields[$arrField['name']] = true;

			$this->arrFields = array_keys(array_merge($this->arrFields, self::$arrDefaultFields));

		} else {
			$this->arrFields = array_keys(self::$arrDefaultFields);
		}

		$this->objStmt = $this->Database->prepare('*');

		$arrConditions = array(
			$this->getQueryPartGuests(),
			$this->getQueryPartPublish()
		);
		$strConditions = implode(' AND ', array_filter($arrConditions, 'strlen'));
		$strConditions && $strConditions = ' AND (' . $strConditions . ')';

		$this->strJumpToQuery =
			'SELECT	id, alias, type, jumpTo, url, target
			FROM	tl_page
			WHERE	id = ?
			' . $strConditions . '
			LIMIT	0, 1';

		$this->strJumpToFallbackQuery =
			'SELECT	id, alias
			FROM	tl_page
			WHERE	pid = ?
			AND		type = \'regular\'
			' . $strConditions . '
			ORDER BY sorting
			LIMIT	0, 1';

		if(!$this->backboneit_navigation_disableHooks) {
			$this->blnTreeHook = is_array($GLOBALS['TL_HOOKS']['backboneit_navigation_tree']);
			$this->blnItemHook = is_array($GLOBALS['TL_HOOKS']['bbit_navi_item']);
		}
	}

	public function __get($strKey) {
		switch($strKey) {
			case 'strJumpToFallbackQuery':
			case 'strJumpToQuery':
			case 'arrFields':
			case 'arrGroups':
			case 'arrTrail':
				return $this->$strKey;
		}
		return parent::__get($strKey);
	}

	/**
	 * Filters the given array of page IDs in regard of publish state,
	 * required permissions (protected and guests only) and hidden state, according to
	 * this navigations settings.
	 * Maintains relative order of the input array.
	 *
	 * For permance reason $arrPages is NOT "intval"ed. Make sure $arrPages
	 * contains no hazardous code.
	 *
	 * @param array $arrPages An array of page IDs to filter
	 * @return array Filtered array of page IDs
	 */
	public function filterPages(array $arrPages, $strConditions = '') {
		if(!$arrPages)
			return $arrPages;

		$strConditions && $strConditions = 'AND (' . $strConditions . ')';
		$objPage = $this->objStmt->query(
			'SELECT	id, pid, protected, groups
			FROM	tl_page
			WHERE	id IN (' . implode(',', array_keys(array_flip($arrPages))) . ')
			' . $strConditions
		);

		if(!$this->isPermissionCheckRequired())
			return array_intersect($arrPages, $objPage->fetchEach('id')); // restore order

		$arrPIDs = array();
		$arrValid = array();
		while($arrPage = $objPage->fetchAssoc()) {
			if($this->isPermissionDenied($arrPage))
				continue;

			$arrValid[] = $arrPage['id'];
			/*
			 * do not remove the protected check! permission denied checks for
			 * more, but we need to know, if we must recurse to parent pages,
			 * for permission check, which must not be done, when this page
			 * defines access rights.
			 */
			if(!$arrPage['protected'] && $arrPage['pid'] != 0)
				$arrPIDs[$arrPage['pid']][] = $arrPage['id'];
		}

		// exclude pages which are in a protected path
		while(count($arrPIDs)) {
			$arrIDs = $arrPIDs;
			$arrPIDs = array();

			$objPage = $this->objStmt->query(
				'SELECT id, pid, protected, groups
				FROM	tl_page
				WHERE	id IN (' . implode(',', array_keys($arrIDs)) . ')'
			);

			while($arrPage = $objPage->fetchAssoc()) {
				if(!$arrPage['protected']) { // do not remove, see above
					if($arrPage['pid'] != 0) {
						$arrPIDs[$arrPage['pid']] = isset($arrPIDs[$arrPage['pid']])
							? array_merge($arrPIDs[$arrPage['pid']], $arrIDs[$arrPage['id']])
							: $arrIDs[$arrPage['id']];
					}
				} elseif($this->isPermissionDenied($arrPage)) {
					$arrValid = array_diff($arrValid, $arrIDs[$arrPage['id']]);
				}
			}
		}

		return array_intersect($arrPages, $arrValid);
	}

	/**
	 * Retrieves the subpages of the given array of page IDs in respect of the
	 * given conditions, which are added to the WHERE clause of the query.
	 * Maintains relative order of the input array.
	 *
	 * For permance reason $arrPages is NOT "intval"ed. Make sure $arrPages
	 * contains no hazardous code.
	 *
	 * @param array $arrPages An array of parent IDs
	 * @return array The child IDs
	 */
	public function getNextLevel(array $arrPages, $strConditions = '') {
		if(!$arrPages)
			return $arrPages;

		$strConditions && $strConditions = 'AND (' . $strConditions . ')';
		$objNext = $this->objStmt->query(
			'SELECT	id, pid, protected, groups
			FROM	tl_page
			WHERE	pid IN (' . implode(',', array_keys(array_flip($arrPages))) . ')
			' . $strConditions . '
			ORDER BY sorting'
		);

		$arrNext = array();
		if($this->isPermissionCheckRequired()) {
			while($arrPage = $objNext->fetchAssoc())
				if(!$this->isPermissionDenied($arrPage))
					$arrNext[$arrPage['pid']][] = $arrPage['id'];
		} else {
			while($arrPage = $objNext->fetchAssoc())
				$arrNext[$arrPage['pid']][] = $arrPage['id'];
		}

		$arrNextLevel = array();
		foreach($arrPages as $intID)
			if(isset($arrNext[$intID]))
				$arrNextLevel = array_merge($arrNextLevel, $arrNext[$intID]);

		return $arrNextLevel;
	}

	/**
	 * Retrieves the parents of the given array of page IDs in respect of the
	 * given conditions, which are added to the WHERE clause of the query.
	 * Maintains relative order of the input array and merges subsequent parent
	 * IDs.
	 *
	 * For permance reason $arrPages is NOT "intval"ed. Make sure $arrPages
	 * contains no hazardous code.
	 *
	 * @param array $arrPages An array of child IDs
	 * @return array The parent IDs
	 */
	public function getPrevLevel(array $arrPages, $strConditions = '') {
		if(!$arrPages)
			return $arrPages;

		$strConditions && $strConditions = 'AND (' . $strConditions . ')';
		$objPrev = $this->objStmt->query(
			'SELECT	id, pid
			FROM	tl_page
			WHERE	id IN (' . implode(',', array_keys(array_flip($arrPages))) . ')
			' . $strConditions
		);

		$arrPrev = array();
		while($objPrev->next())
			$arrPrev[$objPrev->id] = $objPrev->pid;

		$arrPrevLevel = array();
		$intPID = -1;
		foreach($arrPages as $intID)
			if(isset($arrPrev[$intID]) && $arrPrev[$intID] != $intPID)
				$arrPrevLevel[] = $intPID = $arrPrev[$intID];

		return $arrPrevLevel;
	}


	/**
	 * Renders the navigation of the given IDs into the navigation template.
	 * Adds CSS classes "first" and "last" to the appropriate navigation item arrays.
	 * If the given array is empty, the empty string is returned.
	 *
	 * @param array $arrRoots The navigation items arrays
	 * @param integer $intStop (optional, defaults to PHP_INT_MAX) The soft limit of depth.
	 * @param integer $intHard (optional, defaults to PHP_INT_MAX) The hard limit of depth.
	 * @param integer $intLevel (optional, defaults to 1) The current level of this navigation layer
	 * @return string The parsed navigation template, could be empty string.
	 */
	protected function renderNavigationTree(array $arrIDs, $strTemplate, $arrStop = PHP_INT_MAX, $intHard = PHP_INT_MAX, $intLevel = 1) {
		if(!$arrIDs)
			return '';

		$arrStop = (array) $arrStop;
		$arrStop || $arrStop = array(PHP_INT_MAX);
		$intStop = $intLevel >= $arrStop[0] ? array_shift($arrStop) : $arrStop[0];
		$arrItems = array();

		foreach($arrIDs as $varID) {
			if(!$this->arrItems[$varID]) {
				continue;
			}

			$arrItem = $this->arrItems[$varID];

			if($varID == $this->varActiveID) {
				$blnContainsActive = true;

				if ($arrItem['href'] === Environment::get('request')) {
					$arrItem['isActive'] = true; // nothing else (active class is set in template)
					$arrItem['isTrail']  = false;
				} else {
					$arrItem['isActive'] = false; // nothing else (active class is set in template)
					$arrItem['isTrail']  = true;
				}

			} else { // do not flatten if/else
				if($arrItem['tid'] == $this->varActiveID) {
					if ($arrItem['href'] === Environment::get('request')) {
						$arrItem['isActive'] = true; // nothing else (active class is set in template)
						$arrItem['isTrail']  = false;
					} else {
						$arrItem['isActive'] = false; // nothing else (active class is set in template)
						$arrItem['isTrail']  = true;
					}
				}
			}

			if($arrItem['isTrail']) {
				$arrItem['class'] .= ' trail';
			}

			if(!isset($this->arrSubitems[$varID])) {
				$arrItem['class'] .= ' leaf';

			} elseif($intLevel >= $intHard) {
				// we are at hard level, never draw submenu
				$arrItem['class'] .= ' submenu leaf';

			} elseif($intLevel >= $intStop && !$arrItem['isTrail'] && $varID !== $this->varActiveID) {
				// we are at stop level and not trail and not active, never draw submenu
				$arrItem['class'] .= ' submenu leaf';

			} elseif($this->arrSubitems[$varID]) {
				$arrItem['class'] .= ' submenu inner';
				$arrItem['subitems'] = $this->renderNavigationTree(
					$this->arrSubitems[$varID], $arrItem['template'], $arrStop, $intHard, $intLevel + 1
				);

			} else { // should never be reached, if no hooks are used
				$arrItem['class'] .= ' leaf';
			}

			$arrItems[] = $arrItem;
		}

		if($blnContainsActive)
			foreach($arrItems as &$arrItem)
				if(!$arrItem['isActive'])
					$arrItem['class'] .= ' sibling';

		$arrItems[0]['class'] .= ' first';
		$arrItems[count($arrItems) - 1]['class'] .= ' last';

		foreach($arrItems as &$arrItem)
			$arrItem['class'] = ltrim($arrItem['class']);

		strlen($strTemplate) || $strTemplate = $this->navigationTpl;
		$objTemplate = new FrontendTemplate($strTemplate);
		$objTemplate->setData(array(
			'module' => $this->arrData,
			'level' => 'level_' . $intLevel,
			'items' => $arrItems,
			'type' => get_class($this)
		));

		return $objTemplate->parse();
	}

	/**
	 * Compiles a navigation item array from a page dataset with the given subnavi
	 *
	 * @param array $arrPage The page dataset as an array
	 * @return array The compiled navigation item array
	 */
	public function compileNavigationItem(array $arrPage, $blnForwardResolution = true, $intItemHook = self::HOOK_DISABLE) {
		// fallback for dataset field collisions
		$arrPage['_title']			= $arrPage['title'];
		$arrPage['_pageTitle']		= $arrPage['pageTitle'];
		$arrPage['_target']			= $arrPage['target'];
		$arrPage['_description']	= $arrPage['description'];

		$arrPage['link']			= $arrPage['_title'];
		$arrPage['class']			= $arrPage['cssClass'] . ' ' . $arrPage['type'];
		$arrPage['title']			= specialchars($arrPage['_title'], true);
		$arrPage['pageTitle']		= specialchars($arrPage['_pageTitle'], true);
		$arrPage['target']			= ''; // overwrite DB value
		$arrPage['nofollow']		= strncmp($arrPage['robots'], 'noindex', 7) === 0;
		$arrPage['description']		= str_replace(array("\n", "\r"), array(' ' , ''), $arrPage['_description']);
		$arrPage['isTrail']			= isset($this->arrTrail[$arrPage['id']]);

		switch($arrPage['type']) {
			case 'forward':
				if($blnForwardResolution) {
					if($arrPage['jumpTo']) {
						$intFallbackSearchID = $arrPage['id'];
						$intJumpToID = $arrPage['jumpTo'];
						do {
							$objNext = $this->objStmt->prepare($this->strJumpToQuery)->execute($intJumpToID);

							if(!$objNext->numRows) {
								$objNext = $this->objStmt->prepare($this->strJumpToFallbackQuery)->execute($intFallbackSearchID);
								break;
							}

							$intFallbackSearchID = $intJumpToID;
							$intJumpToID = $objNext->jumpTo;

						} while($objNext->type == 'forward');

					} else {
						$objNext = $this->objStmt->prepare($this->strJumpToFallbackQuery)->execute($arrPage['id']);
					}

					if(!$objNext->numRows) {
						$arrPage['href'] = $this->generateFrontendUrl($arrPage, null, null, true);

					} elseif($objNext->type == 'redirect') {
						$arrPage['href'] = $this->encodeEmailURL($objNext->url);
						$arrPage['target'] = $objNext->target ? LINK_NEW_WINDOW : '';

					} else {
						$arrPage['tid'] = $objNext->id;
						$arrPage['href'] = $this->generateFrontendUrl($objNext->row(), null, null, true);
					}
				} else {
					$arrPage['tid'] = $arrPage['jumpTo'];
					$arrPage['href'] = $this->generateFrontendUrl($arrPage, null, null, true);
				}
				break;

			case 'redirect':
				$arrPage['href'] = $this->encodeEmailURL($arrPage['url']);
				$arrPage['target'] = $arrPage['_target'] ? LINK_NEW_WINDOW : '';
				break;

			case 'root':
				if(!$arrPage['dns']
				|| preg_replace('/^www\./', '', $arrPage['dns']) == preg_replace('/^www\./', '', $this->Environment->httpHost)) {
					$arrPage['href'] = $this->Environment->base;
					break; // we only break on root pages; pages in different roots should be handled by DomainLink extension
				}
				// do not break

			default:
			case 'regular':
			case 'error_401':
			case 'error_403':
			case 'error_404':
				$arrPage['href'] = $this->generateFrontendUrl($arrPage, null, null, true);
				break;
		}

		if($intItemHook != self::HOOK_DISABLE) {
			$this->executeItemHook($arrPage, $intItemHook == self::HOOK_FORCE);
		}

		return $arrPage;
	}

	/**
	 * Utility method of compileNavigationItem.
	 *
	 * If the given URL starts with "mailto:", the E-Mail is encoded,
	 * otherwise nothing is done.
	 *
	 * @param string $strHref The URL to check and possibly encode
	 * @return string The modified URL
	 */
	public function encodeEmailURL($strHref) {
		if(strncasecmp($strHref, 'mailto:', 7) !== 0)
			return $strHref;

		if (version_compare(VERSION . '.' . BUILD, '3.5.5', '>=')) {
			return \StringUtil::encodeEmail($strHref);
		} else {
			return \String::encodeEmail($strHref);
		}
	}

	public function isPermissionCheckRequired() {
		return !BE_USER_LOGGED_IN && !$this->backboneit_navigation_showProtected;
	}

	/**
	 * Utility method.
	 *
	 * THIS IS NOT THE OPPOSITE OF ::isPermissionGranted()!
	 *
	 * Checks if the current user has no permission to view the page of the
	 * given page dataset, in regards to the permission requirements of the
	 * page.
	 *
	 * @param array $arrPage The page dataset of the current page, with at least
	 * 		groups and protected attributes set.
	 *
	 * @return boolean If the permission is denied true, otherwise false.
	 */
	public function isPermissionDenied($arrPage) {
		if(!$arrPage['protected']) // this page is not protected
			return false;

		if(!$this->arrGroups) // the current user is not in any group
			return true;

		// check if the current user is not in any group, which is allowed to access the current page
		return !array_intersect($this->arrGroups, deserialize($arrPage['groups'], true));
	}

	/**
	 * Utility method.
	 *
	 * THIS IS NOT THE OPPOSITE OF ::isPermissionDenied()!
	 *
	 * Checks if the current user has permission to view the page of the given
	 * page dataset, in regards to the current navigation settings and the
	 * permission requirements of the page.
	 *
	 * Context property: backboneit_navigation_showProtected
	 *
	 * @param array $arrPage The page dataset of the current page, with at least
	 * 		groups and protected attributes set.
	 *
	 * @return boolean If the permission is granted true, otherwise false.
	 */
	public function isPermissionGranted($arrPage) {
		if(BE_USER_LOGGED_IN) // be users have access everywhere
			return true;

		if($this->backboneit_navigation_showProtected) // protection is ignored
			return true;

		return !$this->isPermissionDenied($arrPage);
	}

	/**
	 * Returns the part of the where condition, checking for hidden state of a page.
	 *
	 * @return string The where condition
	 */
	public function getQueryPartHidden($blnShowHidden = false, $blnSitemap = false) {
		if($blnShowHidden) {
			return '';
		} elseif($blnSitemap) {
			return '(sitemap = \'map_always\' OR (hide != 1 AND sitemap != \'map_never\'))';
		} else {
			return 'hide != 1';
		}
	}

	/**
	 * Returns the part of the where condition, checking for guest visibility state of a page.
	 *
	 * @return string The where condition
	 */
	public function getQueryPartGuests() {
		if(FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN) {
			return 'guests != 1';
		} else {
			return '';
		}
	}

	/**
	 * Returns the part of the where condition checking for publication state of a page.
	 *
	 * @return string The where condition
	 */
	public function getQueryPartPublish() {
		if(BE_USER_LOGGED_IN) {
			return '';
		} else {
			static $intTime; if(!$intTime) $intTime = time();
			return '(start = \'\' OR start < ' . $intTime . ') AND (stop = \'\' OR stop > ' . $intTime . ') AND published = 1';
		}
	}

	public function getQueryPartErrorPages($blnShowErrorPages = false) {
		if($blnShowErrorPages) {
			return '';
		} else {
			return 'type != \'error_401\' AND type != \'error_403\' AND type != \'error_404\'';
		}
	}

	/**
	 * A helper method to generate BE wildcard.
	 *
	 * @param string $strBEType (optional, defaults to "NAVIGATION") The type to be displayed in the wildcard
	 * @return string The wildcard HTML string
	 */
	protected function generateBE($strBEType = 'NAVIGATION') {
		$objTemplate = new BackendTemplate('be_wildcard');

		$objTemplate->wildcard = '### ' . $strBEType . ' ###';
		$objTemplate->title = $this->headline;
		$objTemplate->id = $this->id;
		$objTemplate->link = $this->name;
		$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

		return $objTemplate->parse();
	}

	public function addForwardItem($varID, $varTargetID) {
		if(is_array($this->arrItems[$varID])) {
			$this->arrItems[$varID]['href'] = $this->arrItems[$varTargetID]['href'];
		} else {
			$this->arrItems[$varID] = $this->arrItems[$varTargetID];
			$this->arrItems[$varID]['id'] = $varID;
			unset($this->arrItems[$varID]['pid']);
		}
		$this->arrItems[$varID]['tid'] = $varTargetID;
	}

	/**
	 * Executes the tree hook, to dynamically add navigations items to the tree
	 * the navigation is rendered from.
	 *
	 * The callback receives the following parameters:
	 * $this - This navigation module instance
	 *
	 * @param array $arrRootIDs The root pages before hook execution
	 * @return array $arrRootIDs The root pages after hook execution
	 */
	protected function executeTreeHook($blnForce = false) {
		if(!$blnForce && !$this->blnTreeHook) {
			return;
		}

		foreach((array) $GLOBALS['TL_HOOKS']['backboneit_navigation_tree'] as $arrCallback) {
			$this->import($arrCallback[0]);
			$this->{$arrCallback[0]}->{$arrCallback[1]}($this);
		}
	}

	protected function executeItemHook(array &$arrPage, $blnForce = false) {
		if(!$blnForce && !$this->blnItemHook) {
			return;
		}

		foreach((array) $GLOBALS['TL_HOOKS']['bbit_navi_item'] as $arrCallback) {
			$this->import($arrCallback[0]);
			$this->{$arrCallback[0]}->{$arrCallback[1]}($this, $arrPage);
		}
	}

}
