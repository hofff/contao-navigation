<?php

/**
 * Abstract base class for navigation modules
 * 
 * Navigation item array layout:
 * subitems		=> subnavigation as HTML string or empty string
 * class		=> CSS classes
 * title		=> page name with Insert-Tags stripped and XML specialchars replaced by their entities
 * pageTitle	=> page title with Insert-Tags stripped and XML specialchars replaced by their entities
 * link			=> page name (with Insert-Tags and XML specialchars NOT replaced; as stored in the db)
 * href			=> URL of target page
 * nofollow		=> true, if nofollow should be set on rel attribute
 * target		=> either ' onclick="window.open(this.href); return false;"' or empty string
 * description	=> page description with line breaks (\r and \n) replaced by whitespaces
 * 
 * Additionally all page dataset values from the database are available unter their field name,
 * if the field name does not collide with the listed keys.
 * 
 * For the collisions of the Contao core page dataset fields the following keys are available:
 * 
 * @author Oliver Hoff
 */
abstract class AbstractModuleNavigation extends Module {
	
	public static $arrDefaultFields = array(
		'id'		=> true,
		'pid'		=> true,
		'sorting'	=> true,
		'tstamp'	=> true,
		'type'		=> true,
		'alias'		=> true,
		'title'		=> true,
		'protected'	=> true,
		'groups'	=> true,
		'jumpTo'	=> true,
		'pageTitle'	=> true,
		'target'	=> true,
		'description' => true,
		'url'		=> true,
		'robots'	=> true,
		'cssClass'	=> true,
		'accesskey'	=> true,
		'tabindex'	=> true
	);
	
	protected $strJumpToFallbackQuery;
	protected $strJumpToQuery;
	
	public $arrItems = array(); // compiled page datasets
	public $arrSubpages = array(); // ordered IDs of subnavigations
	
	protected $arrFields = array(); // the fields to use for navigation tpl
	
	protected $arrGroups; // set of groups of the current user
	
	protected $intActive; // the id of the active page
	protected $arrPath; // same as trail but with current page included
	protected $arrTrail; // set of parent pages of the current page
	
	public function __construct(Database_Result $objModule, $strColumn = 'main') {
		parent::__construct($objModule, $strColumn);
		if(TL_MODE == 'BE')
			return;
		
		$this->import('Database');
		
		global $objPage;
		$this->intActive = $this->backboneit_navigation_isSitemap || $this->Input->get('articles') ? false : $objPage->id;
		$this->arrPath = array_flip($objPage->trail);
		$this->arrTrail = $this->arrPath;
		unset($this->arrTrail[$objPage->id]); // trail has a slightly different meaning here (current page excluded, same as in the templates)
		
		if(FE_USER_LOGGED_IN) {
			$this->import('FrontendUser', 'User');
			if($this->User->groups)
				$this->arrGroups = array_flip($this->User->groups);
		}
		
		if(!strlen($this->navigationTpl))
			$this->navigationTpl = 'nav_default';
		
		$arrAddFields = deserialize($this->backboneit_navigation_addFields, true);
		
		if(count($arrFields) > 10) {
			$this->arrFields[] = '*';
			
		} else {
			$arrAddFields = array_merge(array_flip($arrAddFields), self::$arrDefaultFields);
			
			foreach($this->Database->listFields('tl_page') as $arrField)
				if(isset($arrAddFields[$arrField['name']]))
					$this->arrFields[] = $arrField['name'];
		}
			
		$strGuests = $this->getQueryPartGuests();
		$strPublish = $this->getQueryPartPublish();
		
		$this->strJumpToQuery =
			'SELECT	id, alias, type
			FROM	tl_page
			WHERE	id = ?
			' . $strGuests . $strPublish . '
			LIMIT	0, 1';
		
		$this->strJumpToFallbackQuery =
			'SELECT	id, alias
			FROM	tl_page
			WHERE	pid = ?
			AND		type = \'regular\'
			' . $strGuests . $strPublish . '
			ORDER BY sorting
			LIMIT	0, 1';
	}
	
	public function __get($strKey) {
		switch($strKey) {
			case 'strJumpToFallbackQuery':
			case 'strJumpToQuery':
			case 'arrFields':
			case 'arrGroups':
			case 'intActive':
			case 'arrPath':
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
	 * @param array $arrPages An array of page IDs to filter
	 * @return array Filtered array of page IDs
	 */
	public function filterPages(array $arrPages, $strConditions = '') {
		if(!$arrPages)
			return $arrPages;
			
		$objPages = $this->Database->execute(
			'SELECT	id, pid, protected, groups
			FROM	tl_page
			WHERE	id IN (' . implode(',', array_keys(array_flip($arrPages))) . ')
			' . $strConditions);
		
		$arrPIDs = array();
		$arrValid = array();
		while($objPages->next()) {
			if(!$this->checkProtected($objPages))
				continue;
			
			$arrValid[$objPages->id] = true;
			if(!$objPages->protected && $objPages->pid != 0)
				$arrPIDs[$objPages->pid][] = $objPages->id;
		}
		
		// exclude pages which are in a protected path
		while(count($arrPIDs)) {
			$arrIDs = $arrPIDs;
			$arrPIDs = array();
			
			$objPages = $this->Database->execute(
				'SELECT id, pid, protected, groups
				FROM	tl_page
				WHERE	id IN (' . implode(',', array_keys($arrIDs)) . ')');
		
			while($objPages->next()) {
				if(!$objPages->protected) {
					if($objPages->pid != 0) {
						$arrPIDs[$objPages->pid] = isset($arrPIDs[$objPages->pid])
							? array_merge($arrPIDs[$objPages->pid], $arrIDs[$objPages->id])
							: $arrIDs[$objPages->id];
					}
				} elseif(!$this->checkProtected($objPages)) {
					$arrValid = array_diff_key($arrValid, array_flip($arrIDs[$objPages->id]));
				}
			}
		}
						
		$arrFiltered = array();
		foreach($arrPages as $intID)
			if($arrValid[$intID])
				$arrFiltered[] = $intID;
		
		return $arrFiltered;
	}
	
	/**
	 * Retrieves the subpages of the given array of page IDs in respect of the
	 * given conditions, which are added to the WHERE clause of the query.
	 * Maintains relative order of the input array.
	 * 
	 * @param array $arrPages An array of parent IDs
	 * @return array The child IDs
	 */
	public function getNextLevel(array $arrPages, $strConditions = '') {
		if(!$arrPages)
			return $arrPages;
			
		$objNext = $this->Database->execute(
			'SELECT	id, pid, protected, groups
			FROM	tl_page
			WHERE	pid IN (' . implode(',', array_keys(array_flip($arrPages))) . ')
			' . $strConditions . '
			ORDER BY sorting');
		
		$arrNext = array();
		while($objNext->next())
			if($this->checkProtected($objNext))
				$arrNext[$objNext->pid][] = $objNext->id;
		
		$arrNextLevel = array();
		foreach($arrPages as $intID)
			if(isset($arrNext[$intID]))
				$arrNextLevel = array_merge($arrNextLevel, $arrNext[$intID]);
		
		return $arrNextLevel;
	}
	
	/**
	 * Retrieves the parents of the given array of page IDs in respect of the
	 * given conditions, which are added to the WHERE clause of the query.
	 * Maintains relative order of the input array.
	 * 
	 * @param array $arrPages An array of child IDs
	 * @return array The parent IDs
	 */
	public function getPrevLevel(array $arrPages, $strConditions = '') {
		if(!$arrPages)
			return $arrPages;
			
		$objPrev = $this->Database->execute(
			'SELECT	id, pid
			FROM	tl_page
			WHERE	id IN (' . implode(',', array_keys(array_flip($arrPages))) . ')
			' . $strConditions);
		
		$arrPrev = array();
		while($objPrev->next())
			$arrPrev[$objPrev->id] = $objPrev->pid;
		
		$arrPrevLevel = array();
		foreach($arrPages as $intID)
			if(isset($arrPrev[$intID]))
				$arrPrevLevel[] = $arrPrev[$intID];
		
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
	protected function renderNaviTree(array $arrIDs, $intStop = PHP_INT_MAX, $intHard = PHP_INT_MAX, $intLevel = 1) {
		if(!$arrIDs)
			return '';
			
		$arrItems = array();
		
		foreach($arrIDs as $intID) {
			if(!isset($this->arrItems[$intID]))
				continue;
				
			$arrItem = $this->arrItems[$intID];
			
			if($arrItem['pid'] == $objPage->pid) {
				$arrItem['class'] .= ' sibling';
			} elseif(isset($this->arrTrail[$arrItem['id']])) {
				$arrItem['class'] .= ' trail';
			}
		
			if(($intLevel <= $intStop || isset($this->arrPath[$arrItem['pid']]))
			&& $intLevel <= $intHard
			&& isset($this->arrSubpages[$intID])) {
				$arrItem['class'] .= ' submenu';
				$arrItem['subitems'] = $this->renderNaviTree($this->arrSubpages[$intID], $intStop, $intHard, $intLevel + 1);
			}
			
			$arrItem['class'] = trim($arrItem['class']);
			
			$arrItems[] = $arrItem;
		}
		
		
		$intLast = count($arrItems) - 1;
		$arrItems[0]['class'] = trim($arrItems[0]['class'] . ' first');
		$arrItems[$intLast]['class'] = trim($arrItems[$intLast]['class'] . ' last');
		
		$objTemplate = new FrontendTemplate($this->navigationTpl);
		$objTemplate->setData(array(
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
	public function compileNavigationItem(array $arrPage) {
		// fallback for dataset field collisions
		$arrPage['_pageTitle']		= $arrPage['pageTitle'];
		$arrPage['_target']			= $arrPage['target'];
		$arrPage['_description']	= $arrPage['description'];
		
		switch($arrPage['type']) {
			case 'forward':
				if($arrPage['jumpTo']) {
					$intFallbackSearchID = $arrPage['id'];
					$intJumpToID = $arrPage['jumpTo'];
					do {
						$objNext = $this->Database->prepare(
							$this->strJumpToQuery
						)->execute($intJumpToID);
						
						if(!$objNext->numRows) {
							$objNext = $this->Database->prepare(
								$this->strJumpToFallbackQuery
							)->execute($intFallbackSearchID);
							break;
						}
						
						$intFallbackSearchID = $intJumpToID;
						$intJumpToID = $objNext->jumpTo;
						
					} while($objNext->type == 'forward');
				} else {
					$objNext = $this->Database->prepare(
						$this->strJumpToFallbackQuery
					)->execute($arrPage['id']);
				}
				
				if(!$objNext->numRows) {
					$arrPage['href'] = $this->generateFrontendUrl($arrPage);
				} elseif($objNext->type == 'redirect') {
					$arrPage['href'] = $this->encodeEmailURL($objNext->url);
				} else {
					$intForwardID = $objNext->id;
					$arrPage['href'] = $this->generateFrontendUrl($objNext->row());
				}
				break;
				
			case 'redirect':
				$arrPage['href'] = $this->encodeEmailURL($arrPage['url']);
				break;
				
			case 'root':
				if(!$arrPage['dns']
				|| preg_replace('/^www\./', '', $arrPage['dns']) ==  preg_replace('/^www\./', '', $this->Environment->httpHost)) {
					$arrPage['href'] = $this->Environment->base;
					break;
				}
				
			default:
				$arrPage['href'] = $this->generateFrontendUrl($arrPage);
				break;
		}
		
		$arrPage['link']			= $arrPage['title'];
		$arrPage['class']			= $arrPage['cssClass'];
		$arrPage['title']			= specialchars($arrPage['title'], true);
		$arrPage['pageTitle']		= specialchars($arrPage['_pageTitle'], true);
		$arrPage['nofollow']		= strncmp($arrPage['robots'], 'noindex', 7) === 0;
		$arrPage['target']			= $arrPage['type'] == 'redirect' && $arrPage['_target'] ? LINK_NEW_WINDOW : '';
		$arrPage['description']		= str_replace(array("\n", "\r"), array(' ' , ''), $arrPage['_description']);
		
		$arrPage['isActive'] = $this->intActive === $arrPage['id'] || $this->intActive === $intForwardID;
		
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

		$this->import('String');
		return $this->String->encodeEmail($strHref);
	}
	
	/**
	 * Utility method.
	 * Checks if the page of the given page dataset is visible to the current user,
	 * in regards to the current navigation settings and the permission requirements of the page.
	 * 
	 * @param $objPage
	 * @return unknown_type
	 */
	public function checkProtected($objPage) {
		if(BE_USER_LOGGED_IN)
			return true;
			
		if(!$objPage->protected)
			return true;
			
		if($this->backboneit_navigation_showProtected)
			return true;
			
		if(!$this->arrGroups)
			return false;
			
		if(array_intersect_key($this->arrGroups, array_flip(deserialize($objPage->groups, true))))
			return true;
			
		return false;
	}
	
	/**
	 * Returns the part of the where condition, checking for hidden state of a page.
	 * The condition is preceded by " AND ".
	 * 
	 * @return string The where condition
	 */
	public function getQueryPartHidden($blnShowHidden) {
		if($blnShowHidden) {
			return '';
		} elseif($this->backboneit_navigation_isSitemap) {
			return ' AND (sitemap = \'map_always\' OR (hide != 1 AND sitemap != \'map_never\'))';
		} else {
			return ' AND hide != 1';
		}	
	}
	
	/**
	 * Returns the part of the where condition, checking for guest visibility state of a page.
	 * The condition is preceded by " AND ".
	 * 
	 * @return string The where condition
	 */
	public function getQueryPartGuests() {
		if(FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN) {
			return ' AND guests != 1';
		} else {
			return '';
		}
	}
	
	/**
	 * Returns the part of the where condition checking for publication state of a page.
	 * The condition is preceded by " AND ".
	 * 
	 * @return string The where condition
	 */
	public function getQueryPartPublish() {
		if(BE_USER_LOGGED_IN) {
			return '';
		} else {
			static $intTime; if(!$intTime) $intTime = time();
			return ' AND (start = \'\' OR start < ' . $intTime . ') AND (stop = \'\' OR stop > ' . $intTime . ') AND published = 1';
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
	
}