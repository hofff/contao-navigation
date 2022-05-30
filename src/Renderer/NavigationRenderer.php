<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Renderer;

use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Navigation\FrontendModule\AbstractModuleNavigation;
use Hofff\Contao\Navigation\Items\PageItems;
use Hofff\Contao\Navigation\QueryBuilder\PageQueryBuilder;

use function array_merge;
use function array_shift;
use function get_class;
use function is_array;
use function ltrim;
use function preg_replace;
use function str_replace;
use function strncasecmp;
use function strncmp;
use function trim;

use const LINK_NEW_WINDOW;
use const PHP_INT_MAX;

final class NavigationRenderer
{
    private Connection $connection;

    private PageQueryBuilder $pageQueryBuilder;

    public function __construct(Connection $connection, PageQueryBuilder $pageQueryBuilder)
    {
        $this->connection       = $connection;
        $this->pageQueryBuilder = $pageQueryBuilder;
    }

    /**
     * Renders the navigation of the given IDs into the navigation template.
     * Adds CSS classes "first" and "last" to the appropriate navigation item arrays.
     * If the given array is empty, the empty string is returned.
     *
     * @param array   $arrIDs       The navigation items arrays
     * @param array   $stopLimit    (optional, defaults to PHP_INT_MAX) The soft limit of depth.
     * @param integer $intHard      (optional, defaults to PHP_INT_MAX) The hard limit of depth.
     * @param integer $currentLevel (optional, defaults to 1) The current level of this navigation layer
     *
     * @return string The parsed navigation template, could be empty string.
     */
    public function render(
        ModuleModel $moduleModel,
        PageItems $items,
        array $itemIds,
        array $stopLimit = [PHP_INT_MAX],
        int $intHard = PHP_INT_MAX,
        int $currentLevel = 1,
        ?int $activeId = null
    ): string {
        if ($stopLimit === []) {
            $stopLimit = [PHP_INT_MAX];
        }

        $this->compileTree($moduleModel, $items);
        $this->executeTreeHook($moduleModel, $items);

        $arrRootIDs = $this->executeMenuHook($moduleModel, $itemIds);
        $arrFirstIDs = $this->getFirstNavigationLevel($moduleModel, $items, $arrRootIDs);

        if ($moduleModel->hofff_navigation_hideSingleLevel) {
            foreach ($arrFirstIDs as $id) {
                if ($items->subItems[$id]) {
                    $hasMultipleLevels = true;
                    break;
                }
            }

            if (! $hasMultipleLevels) {
                return '';
            }
        }

        $stopLimit[0] == 0 && array_shift($stopLimit); // special case renderNavigationTree cannot handle

        return trim($this->renderTree($moduleModel, $items, $arrFirstIDs, $stopLimit, $intHard, $currentLevel, $activeId));
    }

    private function renderTree(
        ModuleModel $moduleModel,
        PageItems $items,
        array $itemIds,
        array $stopLimit = [PHP_INT_MAX],
        int $intHard = PHP_INT_MAX,
        int $currentLevel = 1,
        ?int $activeId = null
    ): string {
        if (! $itemIds) {
            return '';
        }

        $intStop  = $currentLevel >= $stopLimit[0] ? array_shift($stopLimit) : $stopLimit[0];
        $arrItems = [];

        foreach ($itemIds as $varID) {
            if (! isset($items->items[$varID])) {
                continue;
            }

            $arrItem = $items->items[$varID];

            if ($varID == $activeId) {
                $blnContainsActive = true;

                if ($arrItem['href'] === Environment::get('request')) {
                    $arrItem['isActive'] = true; // nothing else (active class is set in template)
                    $arrItem['isInTrail']  = false;
                } else {
                    $arrItem['isActive'] = false; // nothing else (active class is set in template)
                    $arrItem['isInTrail']  = true;
                }
            } else { // do not flatten if/else
                if ($arrItem['tid'] == $activeId) {
                    if ($arrItem['href'] === Environment::get('request')) {
                        $arrItem['isActive'] = true; // nothing else (active class is set in template)
                        $arrItem['isInTrail']  = false;
                    } else {
                        $arrItem['isActive'] = false; // nothing else (active class is set in template)
                        $arrItem['isInTrail']  = true;
                    }
                }
            }

            if ($arrItem['isInTrail']) {
                $arrItem['class'] .= ' trail';
            }

            if (! isset($items->subItems[$varID])) {
                $arrItem['class'] .= ' leaf';
            } elseif ($currentLevel >= $intHard) {
                // we are at hard level, never draw submenu
                $arrItem['class'] .= ' submenu leaf';
            } elseif ($currentLevel >= $intStop && ! $arrItem['isInTrail'] && $varID !== $activeId && $arrItem['tid'] != $activeId) {
                // we are at stop level and not trail and not active, never draw submenu
                $arrItem['class'] .= ' submenu leaf';
            } elseif ($items->subItems[$varID]) {
                $arrItem['class']    .= ' submenu inner';
                $arrItem['subitems'] = $this->renderTree(
                    $moduleModel,
                    $items,
                    $items->subItems[$varID] ?? [],
                    $stopLimit,
                    $intHard,
                    $currentLevel + 1
                );
            } else { // should never be reached, if no hooks are used
                $arrItem['class'] .= ' leaf';
            }

            $arrItems[] = $arrItem;
        }

        if ($blnContainsActive) {
            foreach ($arrItems as &$arrItem) {
                if (! $arrItem['isActive']) {
                    $arrItem['class'] .= ' sibling';
                }
            }
            unset($arrItem);
        }

        $arrItems[0]['class']                    .= ' first';
        $arrItems[count($arrItems) - 1]['class'] .= ' last';

        foreach ($arrItems as &$arrItem) {
            $arrItem['class'] = ltrim($arrItem['class']);
        }
        unset($arrItem);

        $objTemplate = new FrontendTemplate($moduleModel->navigationTpl ?: 'nav_hofff');
        $objTemplate->setData([
            'module' => $moduleModel->row(),
            'level'  => 'level_' . $currentLevel,
            'items'  => $arrItems,
            'type'   => get_class($this),
        ]);

        return $objTemplate->parse();
    }

    public function compileTree(ModuleModel $moduleModel, PageItems $items): void
    {
        $blnForwardResolution = ! $moduleModel->hofff_navigation_noForwardResolution;
        foreach ($items->roots as $intID => $_) {
            if (!isset($items->items[$intID])) {
                continue;
            }

            $items->items[$intID] = $this->compileNavigationItem(
                $moduleModel,
                $items,
                $items->items[$intID],
                $blnForwardResolution
            );
        }
    }

    /**
     * Compiles a navigation item array from a page dataset with the given subnavi
     *
     * @param array $arrPage The page dataset as an array
     *
     * @return array The compiled navigation item array
     */
    public function compileNavigationItem(
        ModuleModel $moduleModel,
        PageItems $items,
        array $arrPage,
        bool $forwardResolution = true
    ) {
        // fallback for dataset field collisions
        $arrPage['_title']       = $arrPage['title'];
        $arrPage['_pageTitle']   = $arrPage['pageTitle'];
        $arrPage['_target']      = $arrPage['target'];
        $arrPage['_description'] = $arrPage['description'];

        $arrPage['link']        = $arrPage['_title'];
        $arrPage['class']       = $arrPage['cssClass'] . ' ' . $arrPage['type'];
        $arrPage['title']       = StringUtil::specialchars($arrPage['_title'], true);
        $arrPage['pageTitle']   = StringUtil::specialchars($arrPage['_pageTitle'], true);
        $arrPage['target']      = ''; // overwrite DB value
        $arrPage['nofollow']    = strncmp($arrPage['robots'], 'noindex', 7) === 0;
        $arrPage['description'] = str_replace(["\n", "\r"], [' ', ''], $arrPage['_description']);
        $arrPage['isInTrail']     = $items->isInTrail((int) $arrPage['id']);

        switch ($arrPage['type']) {
            case 'forward':
                if ($forwardResolution) {
                    $redirectPage = $this->getRedirectPage($arrPage);
                    if (! $redirectPage) {
                        $arrPage['href'] = $this->generatePageUrl($arrPage);
                    } elseif ($redirectPage['type'] === 'redirect') {
                        $arrPage['href']   = $this->encodeEmailURL($redirectPage['url']);
                        $arrPage['target'] = $redirectPage['target'] ? LINK_NEW_WINDOW : '';
                    } else {
                        $arrPage['tid']  = $redirectPage['id'];
                        $arrPage['href'] = $this->generatePageUrl($redirectPage);
                    }
                } else {
                    $arrPage['tid']  = $arrPage['jumpTo'];
                    $arrPage['href'] = $this->generatePageUrl($arrPage);
                }
                break;

            case 'redirect':
                $arrPage['href']   = $this->encodeEmailURL($arrPage['url']);
                $arrPage['target'] = $arrPage['_target'] ? LINK_NEW_WINDOW : '';
                break;

            case 'root':
                if (! $arrPage['dns']
                    || preg_replace('/^www\./', '', $arrPage['dns']) == preg_replace(
                        '/^www\./',
                        '',
                        Environment::get('httpHost')
                    )) {
                    $arrPage['href'] = Environment::get('base');
                    break; // we only break on root pages; pages in different roots should be handled by DomainLink extension
                }
            // do not break

            default:
            case 'regular':
            case 'error_401':
            case 'error_403':
            case 'error_404':
                $arrPage['href'] = $this->generatePageUrl($arrPage);
                break;
        }

        $this->executeItemHook($moduleModel, $arrPage);

        return $arrPage;
    }

    private function generatePageUrl(array $arrPage): ?string
    {
        $pageModel = PageModel::findByPk($arrPage['id']);
        if ($pageModel) {
            return $pageModel->getFrontendUrl();
        }

        return null;
    }

    /**
     * Utility method of compileNavigationItem.
     *
     * If the given URL starts with "mailto:", the E-Mail is encoded,
     * otherwise nothing is done.
     *
     * @param string $strHref The URL to check and possibly encode
     *
     * @return string The modified URL
     */
    public function encodeEmailURL(string $strHref): string
    {
        if (strncasecmp($strHref, 'mailto:', 7) !== 0) {
            return $strHref;
        }

        return StringUtil::encodeEmail($strHref);
    }

    protected function executeItemHook(ModuleModel $moduleModel, array &$arrPage): void
    {
        if (! $moduleModel->hofff_navigation_disableHooks) {
            return;
        }

        foreach ((array) ($GLOBALS['TL_HOOKS']['hofff_navigation_item'] ?? []) as $arrCallback) {
            $arrCallback[0] = System::importStatic($arrCallback[0]);
            $arrCallback[0]->{$arrCallback[1]}($moduleModel, $arrPage);
        }
    }

    /**
     * Executes the tree hook, to dynamically add navigations items to the tree
     * the navigation is rendered from.
     *
     * The callback receives the following parameters:
     * $this - This navigation module instance
     *
     * @param array $arrRootIDs The root pages before hook execution
     *
     * @return void
     */
    protected function executeTreeHook(ModuleModel $moduleModel, PageItems $items, $blnForce = false): void
    {
        if (! $blnForce && ! $moduleModel->hofff_navigation_disableHooks) {
            return;
        }

        foreach ((array) $GLOBALS['TL_HOOKS']['hofff_navigation_tree'] as $arrCallback) {
            $arrCallback[0] = System::importStatic($arrCallback[0]);
            $arrCallback[0]->{$arrCallback[1]}($moduleModel, $items);
        }
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
    protected function executeMenuHook(ModuleModel $moduleModel, array $arrRootIDs, $blnForce = false)
    {
        if (! $blnForce && $moduleModel->hofff_navigation_disableHooks) {
            return $arrRootIDs;
        }

        if (! is_array($GLOBALS['TL_HOOKS']['hofff_navigation_menu'])) {
            return $arrRootIDs;
        }

        foreach ($GLOBALS['TL_HOOKS']['hofff_navigation_menu'] as $arrCallback) {
            $arrCallback[0] = System::importStatic($arrCallback[0]);
            $arrNewRoots = $this->{$arrCallback[0]}->{$arrCallback[1]}($moduleModel, $arrRootIDs);

            if ($arrNewRoots !== null) {
                $arrRootIDs = $arrNewRoots;
            }
        }

        return $arrRootIDs;
    }

    protected function getFirstNavigationLevel(ModuleModel $moduleModel, PageItems $items, array $arrRootIDs): array
    {
        if ($moduleModel->hofff_navigation_includeStart) {
            return $arrRootIDs;
        }

        // if we do not want to show the root level
        $arrFirstIDs = [];
        foreach ($arrRootIDs as $varRootID) {
            if (isset($items->subItems[$varRootID])) {
                $arrFirstIDs = array_merge($arrFirstIDs, $items->subItems[$varRootID]);
            }
        }

        return $arrFirstIDs;
    }

    private function getRedirectPage(array $arrPage): array
    {
        static $jumpToQuery = null;
        static $jumpToFallbackQuery = null;

        if ($jumpToQuery === null) {
            $jumpToQuery = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('tl_page')
                ->where('id=:id')
                ->setMaxResults(1);

            $this->pageQueryBuilder
                ->addGuestsQueryParts($jumpToQuery)
                ->addPublishedCondition($jumpToQuery);
        }

        if ($jumpToFallbackQuery === null) {
            $jumpToFallbackQuery = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('tl_page')
                ->where('type=:type')
                ->andWhere('pid=:pid')
                ->setParameter('type', 'regular')
                ->orderBy('sorting')
                ->setMaxResults(1);

            $this->pageQueryBuilder
                ->addGuestsQueryParts($jumpToFallbackQuery);
        }

        if ($arrPage['jumpTo']) {
            $intFallbackSearchID = $arrPage['id'];
            $intJumpToID         = $arrPage['jumpTo'];
            do {
                $query = clone $jumpToQuery;
                $query->setParameter('id', $intJumpToID);
                $result = $query->execute();
                $next   = $result->fetchAssociative();

                if (! $result->rowCount()) {
                    $query = clone $jumpToFallbackQuery;
                    $query->setParameter('pid', $intFallbackSearchID);
                    $next = $query->execute()->fetchAssociative();
                    break;
                }

                $intFallbackSearchID = $intJumpToID;
                $intJumpToID         = $next['jumpTo'] ?? 0;
            } while ($next['type'] === 'forward');
        } else {
            $query = clone $jumpToFallbackQuery;
            $query->setParameter('pid', $arrPage['id']);
            $next = $query->execute()->fetchAssociative();
        }

        return $next;
    }
}
