<?php

namespace Hofff\Contao\Navigation\FrontendModule;

class ModuleNavigationMenu extends AbstractModuleNavigation
{

    protected $strTemplate = 'mod_backboneit_navigation_menu';

    protected $strNavigation;

    public $strFirstLevelTemplate;

    public function generate()
    {
        if (TL_MODE == 'BE') {
            return $this->generateBE('NAVIGATION MENU');
        }

        $arrStop = $this->getStop();
        $intHard = $this->getHard();

        $arrRootIDs = $this->calculateRootIDs($arrStop);
        $this->compileNavigationTree($arrRootIDs, $arrStop, $intHard);
        $this->executeTreeHook();
        $arrRootIDs  = $this->executeMenuHook($arrRootIDs);
        $arrFirstIDs = $this->getFirstNavigationLevel($arrRootIDs);

        if ($this->hofff_navigation_hideSingleLevel) {
            foreach ($arrFirstIDs as $id) {
                if ($this->arrSubitems[$id]) {
                    $hasMultipleLevels = true;
                    break;
                }
            }
            if (! $hasMultipleLevels) {
                return '';
            }
        }

        $arrStop[0] == 0 && array_shift($arrStop); // special case renderNavigationTree cannot handle
        $this->strNavigation = trim(
            $this->renderNavigationTree($arrFirstIDs, $this->strFirstLevelTemplate, $arrStop, $intHard)
        );

        return $this->strNavigation ? parent::generate() : '';
    }

    public function getStop()
    {
        if (! $this->hofff_navigation_defineStop) {
            return [PHP_INT_MAX];
        }
        $intMin = -1;
        foreach (array_map('intval', explode(',', $this->hofff_navigation_stop)) as $intLevel) {
            if ($intLevel > $intMin) {
                $arrStop[] = $intMin = $intLevel;
            }
        }

        return $arrStop ? $arrStop : [PHP_INT_MAX];
    }

    public function getHard()
    {
        return $this->hofff_navigation_defineHard ? $this->hofff_navigation_hard : PHP_INT_MAX;
    }

    protected function compile()
    {
        $this->Template->request = $this->getIndexFreeRequest(true);
        $this->Template->skipId  = 'skipNavigation' . $this->id;
        $this->Template->items   = $this->strNavigation;
        $this->hofff_navigation_addLegacyCss && $this->Template->legacyClass = ' mod_navigation';
    }

    protected function calculateRootIDs($arrStop = PHP_INT_MAX)
    {
        $arrRootIDs = $this->hofff_navigation_defineRoots
            ? $this->getRootIds()
            : [$GLOBALS['objPage']->rootId];

        $this->hofff_navigation_currentAsRoot && array_unshift($arrRootIDs, $GLOBALS['objPage']->id);

        $arrConditions = [
            $this->getQueryPartHidden(
                ! $this->hofff_navigation_respectHidden,
                $this->hofff_navigation_isSitemap
            ),
        ];
        $this->hofff_navigation_respectGuests && $arrConditions[] = $this->getQueryPartGuests();
        $this->hofff_navigation_respectPublish && $arrConditions[] = $this->getQueryPartPublish();
        $strConditions = implode(' AND ', array_filter($arrConditions, 'strlen'));

        if ($this->hofff_navigation_includeStart) {
            $arrStartConditions = [
                $this->getQueryPartHidden(
                    $this->hofff_navigation_showHiddenStart,
                    $this->hofff_navigation_isSitemap
                ),
                $this->getQueryPartPublish(),
                $this->getQueryPartErrorPages($this->hofff_navigation_showErrorPages),
            ];
            ! $this->hofff_navigation_showGuests && $arrStartConditions[] = $this->getQueryPartGuests();
            $strStartConditions = implode(' AND ', array_filter($arrStartConditions, 'strlen'));
        } else {
            $strStartConditions = $strConditions;
        }

        if ($this->hofff_navigation_start > 0) {
            $arrRootIDs = $this->filterPages($arrRootIDs, $strConditions);
            for ($i = 1, $n = $this->hofff_navigation_start; $i < $n; $i++) {
                $arrRootIDs = $this->getNextLevel($arrRootIDs, $strConditions);
            }
            $arrRootIDs = $this->getNextLevel($arrRootIDs, $strStartConditions);
        } elseif ($this->hofff_navigation_start < 0) {
            for ($i = 0, $n = -$this->hofff_navigation_start; $i < $n; $i++) {
                $arrRootIDs = $this->getPrevLevel($arrRootIDs);
            }
            $arrRootIDs = $this->filterPages($arrRootIDs, $strStartConditions);
        } else {
            $arrRootIDs = $this->filterPages($arrRootIDs, $strStartConditions);
        }

        $arrStop = (array) $arrStop;
        if ($arrStop[0] == 0) { // special case, keep only roots within the current path
            $arrPath    = array_keys($this->arrTrail);
            $arrPath[]  = $this->varActiveID;
            $arrRootIDs = array_intersect($arrRootIDs, $arrPath);
        }

        return $arrRootIDs;
    }

    public function compileNavigationTree(array $arrRootIDs, $arrStop = PHP_INT_MAX, $intHard = PHP_INT_MAX)
    {
        if (! $arrRootIDs) {
            return;
        }

        $arrRootIDs = array_keys(array_flip($arrRootIDs));
        $arrStop    = (array) $arrStop;

        foreach ($arrRootIDs as $intRootID) {
            $this->arrItems[$intRootID] = (array) $this->arrItems[$intRootID];
        }

        if ($this->hofff_navigation_includeStart) {
            $arrFetched = $this->fetchItems($arrRootIDs, $arrStop, $intHard, 2);

            $objRoots = $this->objStmt->query(
                'SELECT	' . $this->getQuotedFieldsPart($this->arrFields) . '
				FROM	tl_page
				WHERE	id IN (' . implode(',', $arrRootIDs) . ')'
            );

            while ($objRoots->next()) {
                $this->arrItems[$objRoots->id] = $objRoots->row();
                $arrFetched[$objRoots->id]     = true;
            }
        } else {
            $arrFetched = $this->fetchItems($arrRootIDs, $arrStop, $intHard);
        }

        $blnForwardResolution = ! $this->hofff_navigation_noForwardResolution;
        foreach ($arrFetched as $intID => $_) {
            $this->arrItems[$intID] = $this->compileNavigationItem(
                $this->arrItems[$intID],
                $blnForwardResolution,
                self::HOOK_ENABLE
            );
        }
    }

    /**
     * Fetches page data for all navigation items below the given roots.
     *
     * @param integer $arrPIDs  The root pages of the navigation.
     * @param integer $intStop  (optional, defaults to PHP_INT_MAX) The soft limit of depth.
     * @param integer $intHard  (optional, defaults to PHP_INT_MAX) The hard limit of depth.
     * @param integer $intLevel (optional, defaults to 1) The level of the PIDs.
     *
     * @return null
     */
    protected function fetchItems(array $arrPIDs, $arrStop = PHP_INT_MAX, $intHard = PHP_INT_MAX, $intLevel = 1)
    {
        $intLevel = max(1, $intLevel);
        $arrStop  = (array) $arrStop;

        // nothing todo
        // $intLevel == $intHard + 1 requires subitem detection for css class "submenu" calculation
        if (! $arrPIDs || $intLevel - 1 > $intHard) {
            return;
        }

        $arrConditions = [
            $this->getQueryPartHidden($this->hofff_navigation_showHidden, $this->hofff_navigation_isSitemap),
            $this->getQueryPartPublish(),
            $this->getQueryPartErrorPages($this->hofff_navigation_showErrorPages),
        ];
        ! $this->hofff_navigation_showGuests && $arrConditions[] = $this->getQueryPartGuests();
        $strConditions = implode(' AND ', array_filter($arrConditions, 'strlen'));
        $strConditions && $strConditions = 'AND (' . $strConditions . ')';

        $strLevelQueryStart =
            'SELECT	' . $this->getQuotedFieldsPart($this->arrFields) . '
			FROM	tl_page
			WHERE	pid IN (';
        $strLevelQueryEnd   = ')
			AND		type != \'root\'
			' . $strConditions . '
			ORDER BY sorting';

        $arrFetched = [];

        while ($arrPIDs) {
            // if $arrEndPIDs == $arrPIDs the next $arrPIDs will be empty -> leave loop
            if ($intLevel > $intHard) {
                $arrEndPIDs = $arrPIDs;
            } elseif ($intLevel > $arrStop[0]) {
                count($arrStop) > 1 && array_shift($arrStop);
                $arrEndPIDs = [];
                foreach ($arrPIDs as $intPID) {
                    if (! isset($this->arrTrail[$intPID])) {
                        $arrEndPIDs[$intPID] = true;
                    }
                }
            } else {
                $arrEndPIDs = [];
            }

            $objSubpages = $this->objStmt->query($strLevelQueryStart . implode(',', $arrPIDs) . $strLevelQueryEnd);

            if (! $objSubpages->numRows) {
                break;
            }

            $arrPIDs = [];
            while ($arrPage = $objSubpages->fetchAssoc()) {
                if (isset($this->arrItems[$arrPage['id']])) {
                    continue;
                }

                if (! $this->isPermissionGranted($arrPage)) {
                    continue;
                }

                if (! isset($arrEndPIDs[$arrPage['pid']])) {
                    $this->arrSubitems[$arrPage['pid']][] = $arrPage['id']; // for order of items
                    $this->arrItems[$arrPage['id']]       = $arrPage; // item datasets
                    $arrPIDs[]                            = $arrPage['id']; // ids of current layer (for next layer pids)
                    $arrFetched[$arrPage['id']]           = true; // fetched in this method

                } elseif (! isset($this->arrSubitems[$arrPage['pid']])) {
                    $this->arrSubitems[$arrPage['pid']] = [];
                }
            }

            $intLevel++;
        }

        return $arrFetched;
    }

    /**
     * Executes the navigation hook.
     * The callback receives the following parameters:
     * $this - This navigation module instance
     * $arrRootIDs - The IDs of the first navigation level
     *
     * And should return a new root array or null
     *
     * @param array $arrRootIDs The root pages before hook execution
     *
     * @return array $arrRootIDs The root pages after hook execution
     */
    protected function executeMenuHook(array $arrRootIDs, $blnForce = false)
    {
        if (! $blnForce && $this->hofff_navigation_disableHooks) {
            return $arrRootIDs;
        }
        if (! is_array($GLOBALS['TL_HOOKS']['backboneit_navigation_menu'])) {
            return $arrRootIDs;
        }

        foreach ($GLOBALS['TL_HOOKS']['backboneit_navigation_menu'] as $arrCallback) {
            $this->import($arrCallback[0]);
            $arrNewRoots = $this->{$arrCallback[0]}->{$arrCallback[1]}($this, $arrRootIDs);

            if ($arrNewRoots !== null) {
                $arrRootIDs = $arrNewRoots;
            }
        }

        return $arrRootIDs;
    }

    protected function getFirstNavigationLevel(array $arrRootIDs)
    {
        if ($this->hofff_navigation_includeStart) {
            return $arrRootIDs;
        }

        // if we do not want to show the root level
        $arrFirstIDs = [];
        foreach ($arrRootIDs as $varRootID) {
            if (isset($this->arrSubitems[$varRootID])) {
                $arrFirstIDs = array_merge($arrFirstIDs, $this->arrSubitems[$varRootID]);
            }
        }

        return $arrFirstIDs;
    }

    protected function getRootIds()
    {
        return array_map(
            'intval',
            array_values(
                array_unique(
                    array_merge(
                        \Contao\StringUtil::deserialize($this->hofff_navigation_roots_order, true),
                        \Contao\StringUtil::deserialize($this->hofff_navigation_roots, true)
                    )
                )
            )
        );
    }

}
