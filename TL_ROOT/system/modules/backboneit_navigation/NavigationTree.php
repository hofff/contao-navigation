<?php

class PageTree extends DBAdjacencyListTree {

	private static $arrCache = array();
	
	public static function createCached(
			Database $objConn,
			$strTable,
			$strPrimaryKey = 'id',
			$strParentKey = 'pid',
			$strSortingColumn = 'sorting'
	) {
		$objTree = new self($objConn, $strTable, $strPrimaryKey, $strParentKey, $strSortingColumn);
		
		$strCacheKey = serialize(array($objTree->strTable, $objTree->strPrimaryKey, $objTree->strParentKey, $objTree->strSortingColumn));
		
		if(isset(self::$arrCache[$strCacheKey])) {
			$objTree = self::$arrCache[$strCacheKey];
		} else {
			self::$arrCache[$strCacheKey] = $objTree;
		}
		
		return $objTree;
	}
	
	
	
	private $objConn;
	
	private $strTable;
	
	private $strPrimaryKey;

	private $strParentKey;
	
	private $strSortingColumn;
	
	private $varRootValue;
	
	
	private $blnDBSchemaValid;
	
	private $strSelectClause;
	
	
	private $arrTree = array();
	
	private $arrParents = array();
	
	private $arrChildrenFetched = array();
	
	
	
	public function __construct(
			Database $objConn,
			$strTable,
			$strPrimaryKey = 'id',
			$strParentKey = 'pid',
			$strSortingColumn = 'sorting',
			$varRootValue = 0
	) {
		foreach(array('strTable', 'strPrimaryKey', 'strParentKey', 'strSortingColumn') as $i => $strArg) {
			if(!strlen($$strArg)) {
				throw new InvalidArgumentException(
					sprintf('[%s::%s] Argument %s "%s" must be a non-empty string, given: %s',
						__CLASS__, __METHOD__, $i, $strArg, $$strArg
					)
				);
			}
		}
		
		$this->objConn = $objConn;
		$this->strTable = $strTable;
		$this->strParentKey = $strParentKey;
		$this->strPrimaryKey = $strPrimaryKey;
		$this->strSortingColumn = $strSortingColumn;
		$this->varRootValue = $varRootValue;
		
		$this->strSelectClause = $this->buildSelectClause();
		$this->arrParents[$varRootValue] = $this;
	}
	
	protected function validateDBSchema() {
		$this->blnDBSchemaValid = true; // TODO actually check the schema
	}
	
	public function isDBSchemaValid() {
		if(!isset($this->blnDBSchemaValid)) {
			 $this->validateDBSchema();
		}
		return $this->blnDBSchemaValid;
	}

	public static function generateWildcards(array $arrValues) {
		$arrArgs = func_get_args();
		return implode(',', array_fill(0, array_sum(array_map('count', array_filter($arrArgs, 'is_array'))), '?'));
	}
	
	protected function buildSelectClause() {
		$arrFields = array_unique(array($this->strPrimaryKey, $this->strParentKey, $this->strSortingColumn));
		
		return sprintf('SELECT `%s` FROM `%s`',
			implode('`,`', $arrFields),
			$this->strTable
		);
	}
	
	protected function fetchDescendants(array $arrIDs) {
		
	}
	
	protected function fetchChildren(array $arrIDs) {
		
	}
	
	protected function fetchParents(array $arrIDs) {
		if(!$arrIDs) {
			return;
		}
		
		$arrParents = &$this->arrParents;
		$arrQueryIDs = array_diff_key($arrIDs, $arrParents);
	
		if($arrQueryIDs) {
			$strPrimaryKey = $this->strPrimaryKey;
			
			$objNodes = $this->objConn->prepare($this->strSelectClause
				. ' WHERE `' . $strPrimaryKey . '` IN (' . self::generateWildcards($arrIDs) . ')
			')->execute($arrIDs);
			
			if($objNodes->numRows) {
				$arrTree = &$this->arrTree;
				$strParentKey = $this->strParentKey;
				$strSortingColumn = $this->strSortingColumn;
				
				while($objNodes->next()) {
					$arrParents[$objNodes->$strPrimaryKey] = $objNodes->$strParentKey;
					$arrTree[$objNodes->$strParentKey][$objNodes->$strSortingColumn] = $objNodes->$strPrimaryKey;
				}
			}
		}
		
		foreach($arrIDs as $intID => &$intPID) {
			$intPID = $arrParents[$intID];
		}
		
		return $arrIDs;
	}
	
	protected function fetchAncestors(array $arrIDs, $intDepth = PHP_INT_MAX) {
		if(!$arrIDs) {
			return;
		}
		
		$intDepth = max(intval($intDepth), 1);
		$arrOriginalIDs = $arrIDs;
	
		while($intDepth-- && $arrIDs) {
			$this->fetchParents($arrIDs);
			foreach($arrIDs as )
			$arrIDs = array_intersect_key()
		}
		
		foreach($arrTree as &$arrLevel) {
			ksort($arrLevel, SORT_NUMERIC);
		}
		
		return $arrTree;
	}
	
	protected function getAncestorOrSelfTree($arrIDs) {
		if(!$arrIDs)
			return array();
			
		$arrQueriedIDs = array_flip(array_map('intval', (array) $arrIDs));
		$arrQueryIDs = array_keys($arrQueriedIDs);
			
		$objDB = Database::getInstance();
		$arrTree = array();
		$arrQueriedIDs[0] = true;
		
		while($arrQueryIDs) {
			$objNodes = $objDB->query('
				SELECT	id, pid, sorting
				FROM	' . $strTable . '
				WHERE	id IN (' . implode(',', $arrQueryIDs) . ')
			');
			
			$arrQueryIDs = array();
			while($objNodes->next()) {
				$arrTree[$objNodes->pid][$objNodes->sorting] = $objNodes->id;
				if(!isset($arrQueriedIDs[$objNodes->pid])) {
					$arrQueryIDs[] = $objNodes->pid;
					$arrQueriedIDs[$objNodes->pid] = true;
				}
			}
		}
		
		foreach($arrTree as &$arrLevel) {
			ksort($arrLevel, SORT_NUMERIC);
		}
		
		return $arrTree;
	}
	
	public static function getAncestorOrSelfTree($arrIDs) {
		if(!$arrIDs)
			return array();
			
		$arrQueriedIDs = array_flip(array_map('intval', (array) $arrIDs));
		$arrQueryIDs = array_keys($arrQueriedIDs);
			
		$objDB = Database::getInstance();
		$arrTree = array();
		$arrQueriedIDs[0] = true;
		
		while($arrQueryIDs) {
			$objNodes = $objDB->query('
				SELECT	id, pid, sorting
				FROM	' . $strTable . '
				WHERE	id IN (' . implode(',', $arrQueryIDs) . ')
			');
			
			$arrQueryIDs = array();
			while($objNodes->next()) {
				$arrTree[$objNodes->pid][$objNodes->sorting] = $objNodes->id;
				if(!isset($arrQueriedIDs[$objNodes->pid])) {
					$arrQueryIDs[] = $objNodes->pid;
					$arrQueriedIDs[$objNodes->pid] = true;
				}
			}
		}
		
		foreach($arrTree as &$arrLevel) {
			ksort($arrLevel, SORT_NUMERIC);
		}
		
		return $arrTree;
	}
	
	/**
	 * Returns the given nodes of the given table in preorder,
	 * optionally removing nested IDs.
	 * 
	 * Removes duplicates.
	 *  
	 * @param array $arrIDs
	 * @param boolean $blnUnnest
	 * @param string $strTable
	 * @return array
	 */
	public static function getPreorder($arrIDs, $blnUnnest = false, $strTable = 'tl_page', array $arrTree = null) {
		if(!$arrIDs)
			return array();
		
		$arrAltIDs = array_unique(array_map('intval', (array) $arrIDs));
		if(count($arrAltIDs) < 2) {
			return $arrAltIDs;
		}
		
		$arrAltIDs = array_flip($arrAltIDs);
		$arrReturn = array();
		
		if(isset($arrAltIDs[0])) {
			if($blnUnnest) {
				return array(0);
				
			} elseif(count($arrAltIDs) < 3) {
				ksort($arrAltIDs);
				return array_keys($arrAltIDs);
			}
			$arrReturn[] = 0;
		}
			
		$arrTree !== null || $arrTree = self::getAncestorTree($arrIDs, $strTable);
		
		if($blnUnnest) {
			self::getPreorderHelperUnnest($arrAltIDs, $arrReturn, $arrTree);
		} else {
			self::getPreorderHelper($arrAltIDs, $arrReturn, $arrTree);
		}
		
		return $arrReturn;
	}
	
	/**
	 * Inserts the nodes of the subtree of $arrTree starting at $intCurrentNode
	 * into $arrReturn, if they are in $arrNodeIDs, too.
	 * 
	 * @param array $arrNodeIDs
	 * @param array $arrReturn 
	 * @param array $arrTree
	 * @param integer $intCurrentNode
	 */
	private static function getPreorderHelper(array $arrNodeIDs, array &$arrReturn, array $arrTree, $intCurrentNode = 0) {
		foreach($arrTree[$intCurrentNode] as $intID) {
			isset($arrNodeIDs[$intID]) && $arrReturn[] = $intID;
			isset($arrTree[$intID]) && self::getPreorderHelperFilter($arrNodeIDs, $arrReturn, $arrTree, $intID);
		}
	}
	
	/**
	 * Inserts the nodes of the subtree of $arrTree starting at $intCurrentNode
	 * into $arrReturn, if they are in $arrNodeIDs, too.
	 * 
	 * @param array $arrNodeIDs
	 * @param array $arrReturn 
	 * @param array $arrTree
	 * @param integer $intCurrentNode
	 */
	private static function getPreorderHelperUnnest(array $arrNodeIDs, array &$arrReturn, array $arrTree, $intCurrentNode = 0) {
		foreach($arrTree[$intCurrentNode] as $intID) {
			if(isset($arrNodeIDs[$intID])) {
				$arrReturn[] = $intID;
			} elseif(isset($arrTree[$intID])) {
				self::getPreorderHelperFilterUnnest($arrNodeIDs, $arrReturn, $arrTree, $intID);
			}
		}
	}

	/**
	 * Get all descendants of the given nodes of the given table, optionally
	 * including the given nodes itself (the ones, which are not already a
	 * descendant of another given node). There are no duplicates in the
	 * returned result.
	 * 
	 * @param array $arrIDs
	 * @param boolean $blnIncludeSelf
	 * @param string $strTable
	 * @return array
	 */
	public static function getDescendants($arrIDs, $blnIncludeSelf = false, $strTable = 'tl_page') {
		if(!$arrIDs)
			return array();
			
		$arrQueriedIDs = array_flip(array_map('intval', (array) $arrIDs));
		$arrQueryIDs = array_keys($arrQueriedIDs);
		$arrDescendants = $blnIncludeSelf ? $arrQueryIDs : array();
		$objDB = Database::getInstance();
		
		while($arrQueryIDs) {
			$objNodes = $objDB->query('
				SELECT	id
				FROM	' . $strTable . '
				WHERE	pid IN (' . implode(',', $arrQueryIDs) . ')
			');
			
			$arrQueryIDs = array();
			while($objNodes->next()) {
				if(!isset($arrQueriedIDs[$objNodes->id])) {
					$arrDescendants[] = $objNodes->id;
					$arrQueryIDs[] = $objNodes->id;
					$arrQueriedIDs[$objNodes->id] = true;
				}
			}
		}
		
		return $arrDescendants;
	}
	
	public static function getDescendantsOrSelfTree($arrIDs, $strTable = 'tl_page') {
		if(!$arrIDs)
			return array();
		
		$arrQueriedIDs = array_flip(array_unique(array_map('intval', (array) $arrIDs)));
		$arrQueryIDs = array_keys($arrQueriedIDs);
		$objDB = Database::getInstance();
		$arrTree = array();
		
		while($arrQueryIDs) {
			$objNodes = $objDB->query('
				SELECT	id, pid
				FROM	' . $strTable . '
				WHERE	pid IN (' . implode(',', $arrQueryIDs) . ')
				ORDER BY sorting'
			);
			
			$arrQueryIDs = array();
			while($objNodes->next()) {
				$arrTree[$objNodes->pid][] = $objNodes->id;
				if(!isset($arrQueriedIDs[$objNodes->id])) {
					$arrQueryIDs[] = $objNodes->id;
					$arrQueriedIDs[$objNodes->id] = true;
				}
			}
		}
		
		return $arrTree;
	}

	/**
	 * Returns the descendants of the each of the given nodes of the given table
	 * in preorder, optionally adding the given node themselves.
	 * Duplicates are not removed, invalid and nested nodes are not removed. Use
	 * Controller::getPreorder(..) with $blnUnnest set to true before calling
	 * this function, if this is the desired behavior.
	 * 
	 * @param array $arrIDs
	 * @param boolean $blnIncludeSelf
	 * @param string $strTable
	 * 
	 * @return array
	 */
	public static function getDescendantsPreorder($arrIDs, $blnIncludeSelf = false, $strTable = 'tl_page', array $arrTree = null) {
		if(!$arrIDs)
			return array();
			
		$arrTree !== null || $arrTree = self::getDescendantsOrSelfTree($arrIDs, $strTable);
		$arrIDs = array_map('intval', (array) $arrIDs);
		
		$arrReturn = array();
		if($blnIncludeSelf) {
			foreach($arrIDs as $intID) {
				$arrReturn[] = $intID;
				isset($arrTree[$intID]) && self::getDescendantsPreorderHelper($arrReturn, $arrTree, $intID);
			}
		} else {
			foreach($arrIDs as $intID) {
				isset($arrTree[$intID]) && self::getDescendantsPreorderHelper($arrReturn, $arrTree, $intID);
			}
		}
		
		return $arrReturn;
	}
	
	/**
	 * Inserts the nodes of the subtree of $arrTree starting at $intCurrentNode
	 * into $arrReturn.
	 * 
	 * @param array $arrReturn 
	 * @param array $arrTree
	 * @param integer|mixed $intCurrentNode
	 */
	private static function getDescendantsPreorderHelper(array &$arrReturn, array $arrTree, $intCurrentNode = 0) {
		foreach($arrTree[$intCurrentNode] as $intID) {
			$arrReturn[] = $intID;
			isset($arrTree[$intID]) && self::getDescendantsPreorderHelper($arrReturn, $arrTree, $intID);
		}
	}

}
