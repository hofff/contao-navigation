<?php

namespace Hofff\Contao\Navigation\FrontendModule;

use Contao\BackendTemplate;
use Contao\Module;

/**
 * Abstract base class for navigation modules
 *
 * Navigation item array layout:
 * Before rendering:
 * id            => the ID of the current item (optional)
 * isInTrail        => whether this item is in the trail path
 * class        => CSS classes
 * title        => page name with Insert-Tags stripped and XML specialchars replaced by their entities
 * pageTitle    => page title with Insert-Tags stripped and XML specialchars replaced by their entities
 * link            => page name (with Insert-Tags and XML specialchars NOT replaced; as stored in the db)
 * href            => URL of target page
 * nofollow        => true, if nofollow should be set on rel attribute
 * target        => either ' onclick="window.open(this.href); return false;"' or empty string
 * description    => page description with line breaks (\r and \n) replaced by whitespaces
 *
 * Calculated while rendering:
 * subitems        => subnavigation as HTML string or empty string (rendered if subpages & items setup correctly)
 * isActive        => whether this item is the current active navigation item
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
abstract class AbstractModuleNavigation extends Module
{
    public static $arrDefaultFields = [
        'id'          => true,
        'pid'         => true,
        'sorting'     => true,
        'tstamp'      => true,
        'type'        => true,
        'alias'       => true,
        'title'       => true,
        'protected'   => true,
        'groups'      => true,
        'jumpTo'      => true,
        'pageTitle'   => true,
        'target'      => true,
        'description' => true,
        'url'         => true,
        'robots'      => true,
        'cssClass'    => true,
        'accesskey'   => true,
        'tabindex'    => true,
    ];

    protected $arrFields = []; // the fields to use for navigation tpl

    protected $objStmt; // a reusable stmt object

    protected $arrGroups; // set of groups of the current user

    public $varActiveID; // the id of the active page

    protected $blnTreeHook = false; // execute tree hook?
    protected $blnItemHook = false; // execute item hook?

    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);
        if (TL_MODE == 'BE') {
            return;
        }


        $this->import('Database');

        $this->varActiveID = $this->hofff_navigation_isSitemap || $this->Input->get('articles')
            ? null
            : (int) $GLOBALS['objPage']->id;

        if (FE_USER_LOGGED_IN) {
            $this->import('FrontendUser', 'User');
            $this->User->groups && $this->arrGroups = $this->User->groups;
        }

        if (! strlen($this->navigationTpl)) {
            $this->navigationTpl = 'nav_default';
        }

        $arrFields = deserialize($this->hofff_navigation_addFields, true);

        if (count($arrFields) > 10) {
            $this->arrFields[] = '*';
        } elseif ($arrFields) {
            $arrFields = array_flip($arrFields);
            foreach ($this->Database->listFields('tl_page') as $arrField) {
                if (isset($arrFields[$arrField['name']])) {
                    $this->arrFields[$arrField['name']] = true;
                }
            }

            $this->arrFields = array_keys(array_merge($this->arrFields, self::$arrDefaultFields));
        } else {
            $this->arrFields = array_keys(self::$arrDefaultFields);
        }

        $this->objStmt = $this->Database->prepare('*');

        if (! $this->hofff_navigation_disableHooks) {
            $this->blnTreeHook = is_array($GLOBALS['TL_HOOKS']['hofff_navigation_tree']);
            $this->blnItemHook = is_array($GLOBALS['TL_HOOKS']['hofff_navigation_item']);
        }
    }

    public function __get($strKey)
    {
        switch ($strKey) {
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
     * For performance reason $arrPages is NOT "intval"ed. Make sure $arrPages
     * contains no hazardous code.
     *
     * @param array $arrPages An array of page IDs to filter
     *
     * @return array Filtered array of page IDs
     */
    public function filterPages(array $arrPages, $strConditions = '')
    {
        if (! $arrPages) {
            return $arrPages;
        }

        $strConditions && $strConditions = 'AND (' . $strConditions . ')';
        $objPage = $this->objStmt->query(
            'SELECT ' . $this->getQuotedFieldsPart(['id', 'pid', 'protected', 'groups']) . '
			FROM	tl_page
			WHERE	id IN (' . implode(',', array_keys(array_flip($arrPages))) . ')
			' . $strConditions
        );

        if (! $this->isPermissionCheckRequired()) {
            return array_intersect($arrPages, $objPage->fetchEach('id'));
        } // restore order

        $arrPIDs  = [];
        $arrValid = [];
        while ($arrPage = $objPage->fetchAssoc()) {
            if ($this->isPermissionDenied($arrPage)) {
                continue;
            }

            $arrValid[] = $arrPage['id'];
            /*
             * do not remove the protected check! permission denied checks for
             * more, but we need to know, if we must recurse to parent pages,
             * for permission check, which must not be done, when this page
             * defines access rights.
             */
            if (! $arrPage['protected'] && $arrPage['pid'] != 0) {
                $arrPIDs[$arrPage['pid']][] = $arrPage['id'];
            }
        }

        // exclude pages which are in a protected path
        while (count($arrPIDs)) {
            $arrIDs  = $arrPIDs;
            $arrPIDs = [];

            $objPage = $this->objStmt->query(
                'SELECT ' . $this->getQuotedFieldsPart(['id', 'pid', 'protected', 'groups']) . '
				FROM	tl_page
				WHERE	id IN (' . implode(',', array_keys($arrIDs)) . ')'
            );

            while ($arrPage = $objPage->fetchAssoc()) {
                if (! $arrPage['protected']) { // do not remove, see above
                    if ($arrPage['pid'] != 0) {
                        $arrPIDs[$arrPage['pid']] = isset($arrPIDs[$arrPage['pid']])
                            ? array_merge($arrPIDs[$arrPage['pid']], $arrIDs[$arrPage['id']])
                            : $arrIDs[$arrPage['id']];
                    }
                } elseif ($this->isPermissionDenied($arrPage)) {
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
     * For performance reason $arrPages is NOT "intval"ed. Make sure $arrPages
     * contains no hazardous code.
     *
     * @param array $arrPages An array of parent IDs
     *
     * @return array The child IDs
     */
    public function getNextLevel(array $arrPages, $strConditions = '')
    {
        if (! $arrPages) {
            return $arrPages;
        }

        $strConditions && $strConditions = 'AND (' . $strConditions . ')';
        $objNext = $this->objStmt->query(
            'SELECT	' . $this->getQuotedFieldsPart(['id', 'pid', 'protected', 'groups']) . '
			FROM	tl_page
			WHERE	pid IN (' . implode(',', array_keys(array_flip($arrPages))) . ')
			' . $strConditions . '
			ORDER BY sorting'
        );

        $arrNext = [];
        if ($this->isPermissionCheckRequired()) {
            while ($arrPage = $objNext->fetchAssoc()) {
                if (! $this->isPermissionDenied($arrPage)) {
                    $arrNext[$arrPage['pid']][] = $arrPage['id'];
                }
            }
        } else {
            while ($arrPage = $objNext->fetchAssoc()) {
                $arrNext[$arrPage['pid']][] = $arrPage['id'];
            }
        }

        $arrNextLevel = [];
        foreach ($arrPages as $intID) {
            if (isset($arrNext[$intID])) {
                $arrNextLevel = array_merge($arrNextLevel, $arrNext[$intID]);
            }
        }

        return $arrNextLevel;
    }

    /**
     * Retrieves the parents of the given array of page IDs in respect of the
     * given conditions, which are added to the WHERE clause of the query.
     * Maintains relative order of the input array and merges subsequent parent
     * IDs.
     *
     * For performance reason $arrPages is NOT "intval"ed. Make sure $arrPages
     * contains no hazardous code.
     *
     * @param array $arrPages An array of child IDs
     *
     * @return array The parent IDs
     */
    public function getPrevLevel(array $arrPages, $strConditions = '')
    {
        if (! $arrPages) {
            return $arrPages;
        }

        $strConditions && $strConditions = 'AND (' . $strConditions . ')';
        $objPrev = $this->objStmt->query(
            'SELECT	id, pid
			FROM	tl_page
			WHERE	id IN (' . implode(',', array_keys(array_flip($arrPages))) . ')
			' . $strConditions
        );

        $arrPrev = [];
        while ($objPrev->next()) {
            $arrPrev[$objPrev->id] = $objPrev->pid;
        }

        $arrPrevLevel = [];
        $intPID       = -1;
        foreach ($arrPages as $intID) {
            if (isset($arrPrev[$intID]) && $arrPrev[$intID] != $intPID) {
                $arrPrevLevel[] = $intPID = $arrPrev[$intID];
            }
        }

        return $arrPrevLevel;
    }


    public function isPermissionCheckRequired()
    {
        return ! BE_USER_LOGGED_IN && ! $this->hofff_navigation_showProtected;
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
     *                       groups and protected attributes set.
     *
     * @return boolean If the permission is denied true, otherwise false.
     */
    public function isPermissionDenied($arrPage)
    {
        if (! $arrPage['protected']) // this page is not protected
        {
            return false;
        }

        if (! $this->arrGroups) // the current user is not in any group
        {
            return true;
        }

        // check if the current user is not in any group, which is allowed to access the current page
        return ! array_intersect($this->arrGroups, deserialize($arrPage['groups'], true));
    }

    /**
     * Returns the part of the where condition, checking for hidden state of a page.
     *
     * @return string The where condition
     */
    public function getQueryPartHidden($blnShowHidden = false, $blnSitemap = false)
    {
        if ($blnShowHidden) {
            return '';
        } elseif ($blnSitemap) {
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
    public function getQueryPartGuests()
    {
        if (FE_USER_LOGGED_IN && ! BE_USER_LOGGED_IN) {
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
    public function getQueryPartPublish()
    {
        if (BE_USER_LOGGED_IN) {
            return '';
        } else {
            static $intTime;
            if (! $intTime) {
                $intTime = time();
            }

            return '(start = \'\' OR start < ' . $intTime . ') AND (stop = \'\' OR stop > ' . $intTime . ') AND published = 1';
        }
    }

    public function getQueryPartErrorPages($blnShowErrorPages = false)
    {
        if ($blnShowErrorPages) {
            return '';
        } else {
            return 'type != \'error_401\' AND type != \'error_403\' AND type != \'error_404\'';
        }
    }

    protected function getQuotedFieldsPart(array $fields): string
    {
        return implode(', ', array_map([$this->Database, 'quoteIdentifier'], $fields));
    }

    /**
     * A helper method to generate BE wildcard.
     *
     * @param string $strBEType (optional, defaults to "NAVIGATION") The type to be displayed in the wildcard
     *
     * @return string The wildcard HTML string
     */
    protected function generateBE($strBEType = 'NAVIGATION')
    {
        $objTemplate = new BackendTemplate('be_wildcard');

        $objTemplate->wildcard = '### ' . $strBEType . ' ###';
        $objTemplate->title    = $this->headline;
        $objTemplate->id       = $this->id;
        $objTemplate->link     = $this->name;
        $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

        return $objTemplate->parse();
    }
}
