<?php

class StaticNavigation extends Controller {

	protected $arrImports = array();
	
	protected $strTemplate = 'nav_default';
	
	protected $intRoot;

	protected $intSkip = 0;
	
	protected $intStop = 0;
	
	protected $intHard = 0;
	
	protected $strSubpageQuery;
	
	protected $strJumpToFallbackQuery;
	
	protected $strJumpToQuery;
	
	protected $strStartQuery;
	
	protected $arrGroups;
	
	protected $blnSitemap;
	
	protected $blnShowHidden;
	
	protected $blnShowProtected;
	
	protected $blnIgnoreHidden;
	
	protected $blnIgnoreGuests;
	
	protected $arrTrail;
	
	public function __construct() {
		$this->import('Database');
		
		global $objPage;
		$this->intRoot = $objPage->rootId;
		$this->arrTrail = array_flip($objPage->trail);
		unset($this->arrTrail[$objPage->id]);
		
		if(FE_USER_LOGGED_IN) {
			$this->import('FrontendUser', 'User');
			$this->arrGroups = array_flip($this->User->groups);
		} else {
			$this->arrGroups = array();
		}
	}
	
	public function __get($strKey) {
		return $this->arrImports[$strKey];
	}
	
	public function __set($strKey, $varValue) {
		switch($strKey) {
			case 'root':
				
				global $objPage;
				$varValue = intval($varValue);
				$this->intRoot =
					$varValue > 0
					&& $this->Database->prepare('
						SELECT id FROM tl_page WHERE id = ?
					')->execute($varValue)->numRows
					? $varValue
					: $objPage->rootId;
					
				break;
				
			case 'skip':
				$this->intSkip = intval($varValue);
				break;
				
			case 'stop':
				$varValue = intval($varValue);
				$this->intStop = $varValue > 0 ? $varValue : 0;
				break;
				
			case 'hard':
				$varValue = intval($varValue);
				$this->intHard = $varValue > 0 ? $varValue : 0; 
				break;
				
			case 'template':
				$this->strTemplate = $varValue;
				break;
				
			case 'sitemap':
				$this->blnSitemap = $varValue ? true : false;
				break;
				
			case 'showHidden':
				$this->blnShowHidden = $varValue ? true : false;
				break;
				
			case 'showProtected':
				$this->blnShowProtected = $varValue ? true : false;
				break;
				
			case 'ignoreHidden':
				$this->blnIgnoreHidden = $varValue ? true : false;
				break;
				
			case 'ignoreGuests':
				$this->blnIgnoreGuests = $varValue ? true : false;
				break;
			
			default:
				$this->arrImports[$strKey] = $varValue;
				break;
		}
	}
	
	public function generate() {
		$this->buildQueries();
		
		$intStop = $this->intStop;
		$intHard = $this->intHard;
		$this->intStop = $intStop > 0 ? $intStop : 0;
		$this->intHard = $intHard > 0 ? $intHard : 0; 
		
		if($this->intSkip > 0) {
			$arrRoots = array($this->intRoot);
			for($i = 0, $n = $this->intSkip; $i < $n; $i++) {
				$arrChilds = array();
				foreach($arrRoots as $intRoot) {
					$arrChilds = array_merge($arrChilds, $this->Database->prepare(
						$this->strStartQuery
					)->execute($intRoot)->fetchEach('id'));
				}
				$arrRoots = $arrChilds;
			}
			
			$arrItems = array();
			foreach($arrRoots as $intRoot) {
				$arrItems = array_merge($arrItems, $this->compileItems($intRoot));
			}
			
			$strReturn = $this->renderNavigation($arrItems);
		} elseif($this->intSkip < 0) {
			$intRoot = $this->intRoot;
			for($i = 0, $n = -$this->intSkip; $i < $n; $i++) {
				$objParent = $this->Database->prepare('
					SELECT
						p.pid AS id
					FROM
						tl_page AS p
					WHERE
						p.id = ?
					AND
						p.type != \'root\'
					AND
						p.type != \'error403\'
					AND
						p.type != \'error404\'
				')->execute($intRoot);
				
				if($objParent->numRows) {
					$intRoot = $objParent->id;
				} else {
					break;
				}
			}
			
			$strReturn = $this->renderNavigation($this->compileItems($intRoot));
		} else {
			$strReturn = $this->renderNavigation($this->compileItems($this->intRoot));
		}
		
		$this->intStop = $intStop;
		$this->intHard = $intHard;
		
		return $strReturn;
	}
	
	protected function compileItems($intPID, $intLevel = 1) {
		$objSubpages = $this->Database->prepare($this->strSubpageQuery)->execute($intPID);
		
		if(!$objSubpages->numRows)
			return array();
		
		$arrItems = array();
		
		while($objSubpages->next()) {
			// Do not show protected pages unless a back end or front end user is logged in
			if($objSubpages->protected
			&& !BE_USER_LOGGED_IN
			&& !$this->blnShowProtected
			&& !array_intersect_key($this->arrGroups, array_flip(deserialize($objSubpages->groups, true)))
			&& !($this->blnSitemap && $objSubpages->sitemap == 'map_always'))
				continue;
			
			$arrItems[] = $this->compileNavigationItem(
				$objSubpages,
				$this->renderSubmenu($objSubpages, $intLevel + 1)
			);
		}
		
		return $arrItems;
	}
	
	private function renderNavigation($arrItems, $intLevel = 1) {
		if(!$arrItems)
			return '';
			
		$intLast = count($arrItems) - 1;
		$arrItems[0]['class'] = trim($arrItems[0]['class'] . ' first');
		$arrItems[$intLast]['class'] = trim($arrItems[$intLast]['class'] . ' last');
		
		$objTemplate = new FrontendTemplate($this->strTemplate);
		$objTemplate->setData(array(
			'level' => 'level_' . $intLevel,
			'items' => $arrItems,
//			'type' => get_class($this)
		));
		
		return $objTemplate->parse();
	}
	
	private function compileNavigationItem($objSubpages, $strSubnavi) {
		switch($objSubpages->type) {
			case 'redirect':
				$strHref = $objSubpages->url;

				if(strncasecmp($strHref, 'mailto:', 7) === 0) {
					$this->import('String');
					$strHref = $this->String->encodeEmail($strHref);
				}
				break;

			case 'forward':
				if($objSubpages->jumpTo) {
					$objNext = $this->Database->prepare(
						$this->strJumpToQuery
					)->execute($objSubpages->jumpTo);
				} else {
					$objNext = $this->Database->prepare(
						$this->strJumpToFallbackQuery
					)->execute($objSubpages->id);
				}

				if($objNext->numRows) {
					$strHref = $this->generateFrontendUrl($objNext->fetchAssoc());
				} else {
					$strHref = $this->generateFrontendUrl($objSubpages->row());
				}
				break;

			default:
				$strHref = $this->generateFrontendUrl($objSubpages->row());
				break;
		}

		$arrItem = $objSubpages->row();
		
		$arrItem['isActive'] =
			($objPage->id == $objSubpages->id || ($objSubpages->type == 'forward' && $objPage->id == $objSubpages->jumpTo))
			&& !$this->blnSitemap
			&& !$this->Input->get('articles');
		
		$strClass = '';
		if(strlen($strSubnavi))
			$strClass .= 'submenu';
		if(strlen($objSubpages->cssClass))
			$strClass .= ' ' . $objSubpages->cssClass;
		if($objSubpages->pid == $objPage->pid) {
			$strClass .= ' sibling';
		} elseif(isset($this->arrTrail[$objSubpages->id])) {
			$strClass .= ' trail';
		}
		
		$arrItem['subitems'] = $strSubnavi;
		$arrItem['class'] = trim($strClass);
		$arrItem['title'] = specialchars($objSubpages->title, true);
		$arrItem['pageTitle'] = specialchars($objSubpages->pageTitle, true);
		$arrItem['link'] = $objSubpages->title;
		$arrItem['href'] = $strHref;
		$arrItem['nofollow'] = strncmp($objSubpages->robots, 'noindex', 7) === 0;
		$arrItem['target'] = $objSubpages->type == 'redirect' && $objSubpages->target ? LINK_NEW_WINDOW : '';
		$arrItem['description'] = str_replace(array("\n", "\r"), array(' ' , ''), $objSubpages->description);

		return $arrItem;
	}
	
	private function renderSubmenu($objSubpages, $intLevel) {
		if($objSubpages->subpages < 1)
			return;
		
		if($this->intHard && $intLevel > $this->intHard)
			return;
			
		if($this->intStop && $intLevel > $this->intStop && !isset($this->arrTrail[$objSubpages->id]))
			return;
		
		return $this->renderNavigation($this->compileItems($objSubpages->id, $intLevel), $intLevel);
	}
	
	private function buildQueries() {
		
		if($this->blnShowHidden) {
			$strHidden = '';
		} elseif($this->blnSitemap) {
			$strHidden = '
				AND (
					%1$s.sitemap = \'map_always\'
					OR
					(%1$s.hide != 1 AND %1$s.sitemap != \'map_never\')
				)';
		} else {
			$strHidden = 'AND %1$s.hide != 1';
		}
		
		if(FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN) {
			$strGuests = 'AND %1$s.guests != 1';
		}
		
		if(BE_USER_LOGGED_IN) {
			$strPublish = '';
		} else {
			$intTime = time();
			$strPublish = '
				AND (%1$s.start=\'\' OR %1$s.start < ' . $intTime . ')
				AND (%1$s.stop=\'\' OR %1$s.stop > ' . $intTime . ')
				AND %1$s.published = 1';
		}
		
		$strQuery = '
			SELECT
				p.*,
				(
					SELECT
						COUNT(*)
					FROM
						tl_page AS c
					WHERE
						c.pid = p.id
					AND
						c.type != \'root\'
					AND
						c.type != \'error_403\'
					AND
						c.type != \'error_404\'
					' . sprintf($strHidden, 'c') . '
					' . sprintf($strGuests, 'c') . '
					' . sprintf($strPublish, 'c') . '
				) AS subpages
			FROM
				tl_page AS p
			WHERE
				p.pid = ?
			AND
				p.type != \'root\'
			AND
				p.type != \'error_403\'
			AND
				p.type != \'error_404\'
			' . sprintf($strHidden, 'p') . '
			' . sprintf($strGuests, 'p') . '
			' . sprintf($strPublish, 'p') . '
			ORDER BY
				p.sorting';
		
		$this->strSubpageQuery = preg_replace('/\s+/', ' ', trim($strQuery));
		
		$strQuery = '
			SELECT
				p.id AS id
			FROM
				tl_page AS p
			WHERE
				p.pid = ?
			' . ($this->blnIgnoreHidden ? '' : sprintf($strHidden, 'p')) . '
			' . ($this->blnIgnoreGuests ? '' : sprintf($strGuests, 'p')) . '
			' . sprintf($strPublish, 'p') . '
			ORDER BY
				p.sorting';
		
		$this->strStartQuery = preg_replace('/\s+/', ' ', trim($strQuery));
		
		$this->strJumpToQuery = 'SELECT id, alias FROM tl_page WHERE id = ? LIMIT 0, 1';
		
		$strQuery = '
			SELECT
				p.id,
				p.alias
			FROM
				tl_page AS p
			WHERE
				p.pid = ?
			AND
				p.type=\'regular\'
			' . sprintf($strPublish, 'p') . '
			ORDER BY
				p.sorting
			LIMIT
				0, 1';
		
		$this->strJumpToFallbackQuery = preg_replace('/\s+/', ' ', trim($strQuery));
		
	}
	
}