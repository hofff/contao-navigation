<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Items;

use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Hofff\Contao\Navigation\QueryBuilder\PageQueryBuilder;
use Symfony\Component\Security\Core\Security;

use function array_diff;
use function array_flip;
use function array_intersect;
use function array_keys;
use function array_map;
use function array_merge;
use function array_shift;
use function array_unique;
use function array_unshift;
use function array_values;
use function in_array;
use function max;

use const PHP_INT_MAX;

final class PageItemsLoader
{
    private Connection $connection;

    private Security $security;

    private ?PageQueryBuilder $pageQueryBuilder;

    private PageItems $items;

    private ModuleModel $moduleModel;

    private array $stopLevels = [PHP_INT_MAX];

    private int $hardLevel = PHP_INT_MAX;

    private ?int $activeId = null;

    public function __construct(Connection $connection, Security $security)
    {
        $this->connection       = $connection;
        $this->security         = $security;
    }

    public function load(
        ModuleModel $moduleModel,
        array $stopLevels = [PHP_INT_MAX],
        int $hardLevel = PHP_INT_MAX,
        ?int $activeId = null
    ): PageItems {
        $this->items = new PageItems();
        $this->items->trail = array_flip(isset($GLOBALS['objPage']) ? $GLOBALS['objPage']->trail : []);

        $this->pageQueryBuilder = new PageQueryBuilder($this->connection, $moduleModel);
        $this->moduleModel      = $moduleModel;
        $this->stopLevels       = $stopLevels;
        $this->hardLevel        = $hardLevel;
        $this->activeId         = $activeId;

        $this->items->roots = array_flip($this->calculateRootIDs());
        if (! $this->items->roots) {
            return $this->items;
        }

        $rootIds = array_keys($this->items->roots);

        if (! $moduleModel->hofff_navigation_includeStart) {
            $this->fetchItems($rootIds);

            return $this->items;
        }

        $this->fetchItems($rootIds, 2);

        $result = $this->pageQueryBuilder->createRootInformationQuery($rootIds)->execute();
        while ($row = $result->fetchAssociative()) {
            $this->items->items[$row['id']] = $row;
            $this->items->roots[$row['id']] = true;
        }

        return $this->items;
    }

    /**
     * Fetches page data for all navigation items below the given roots.
     *
     * @param integer $level (optional, defaults to 1) The level of the PIDs.
     *
     * @return array<int,bool>
     */
    protected function fetchItems(array $parentIds, int $level = 1): array
    {
        $level = max(1, $level);

        // nothing todo
        // $level == $hardStop + 1 requires subitem detection for css class "submenu" calculation
        if (! $parentIds || $level - 1 > $this->hardLevel) {
            return [];
        }

        $fetched = [];
        $stopLevels = $this->stopLevels;

        while ($parentIds) {
            // if $arrEndPIDs == $arrPIDs the next $arrPIDs will be empty -> leave loop
            if ($level > $this->hardLevel) {
                $arrEndPIDs = $parentIds;
            } elseif ($level > $stopLevels[0]) {
                count($stopLevels) > 1 && array_shift($stopLevels);
                $arrEndPIDs = [];
                foreach ($parentIds as $intPID) {
                    if (!$this->items->isInTrail((int) $intPID)) {
                        $arrEndPIDs[$intPID] = true;
                    }
                }
            } else {
                $arrEndPIDs = [];
            }

            $result = $this->pageQueryBuilder->createFetchItemsQuery($parentIds)->execute();
            if ($result->rowCount() === 0) {
                break;
            }

            $parentIds = [];
            while ($page = $result->fetchAssociative()) {
                if (isset($items->items[$page['id']])) {
                    continue;
                }

                if (! $this->isPermissionGranted($this->moduleModel, $page)) {
                    continue;
                }

                if (! isset($arrEndPIDs[$page['pid']])) {
                    if (! in_array((int) $page['id'], $this->items->subItems[(int) $page['pid']] ?? [])) {
                        $this->items->subItems[(int) $page['pid']][] = (int) $page['id']; // for order of items
                    }

                    $this->items->items[(int) $page['id']]       = $page; // item datasets
                    $parentIds[]                                 = $page['id']; // ids of current layer (for next layer pids)
                    $fetched[$page['id']]                        = true; // fetched in this method
                } elseif (! isset($items->subItems[(int) $page['pid']])) {
                    $this->items->subItems[(int) $page['pid']] = [];
                }
            }

            $level++;
        }

        return $fetched;
    }

    protected function calculateRootIDs(): array
    {
        $rootIds = $this->getRootIds();
        if ($this->moduleModel->hofff_navigation_currentAsRoot) {
            array_unshift($rootIds, $GLOBALS['objPage']->id);
        }

        if ($this->moduleModel->hofff_navigation_start > 0) {
            $rootIds = $this->filterPages($rootIds, $this->pageQueryBuilder->createRootIdsQuery());
            for ($i = 1, $n = $this->moduleModel->hofff_navigation_start; $i < $n; $i++) {
                $rootIds = $this->getNextLevel($rootIds, $this->pageQueryBuilder->createRootIdsQuery());
            }
            $rootIds = $this->getNextLevel($rootIds, $this->pageQueryBuilder->createStartRootIdsQuery());
        } elseif ($this->moduleModel->hofff_navigation_start < 0) {
            for ($i = 0, $n = -$this->moduleModel->hofff_navigation_start; $i < $n; $i++) {
                $rootIds = $this->getPrevLevel($rootIds);
            }
            $rootIds = $this->filterPages($rootIds, $this->pageQueryBuilder->createStartRootIdsQuery());
        } else {
            $rootIds = $this->filterPages($rootIds, $this->pageQueryBuilder->createStartRootIdsQuery());
        }

        $stopLevels = $this->stopLevels;
        if ($stopLevels[0] == 0) { // special case, keep only roots within the current path
            $path    = $GLOBALS['objPage']->trail;
            $path[]  = $this->activeId;
            $rootIds = array_intersect($rootIds, $path);
        }

        return $rootIds;
    }

    public function isPermissionCheckRequired(): bool
    {
        return ! BE_USER_LOGGED_IN && ! $this->moduleModel->hofff_navigation_showProtected;
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
     * Context property: hofff_navigation_showProtected
     *
     * @param array $arrPage The page dataset of the current page, with at least
     *                       groups and protected attributes set.
     *
     * @return boolean If the permission is granted true, otherwise false.
     */
    public function isPermissionGranted(ModuleModel $model, array $arrPage): bool
    {
        // TODO: Replace with non deprecated check
        // be users have access everywhere
        if (BE_USER_LOGGED_IN) {
            return true;
        }

        // protection is ignored
        if ($model->hofff_navigation_showProtected) {
            return true;
        }

        return ! $this->isPermissionDenied($arrPage);
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
    public function isPermissionDenied(array $arrPage): bool
    {
        // this page is not protected
        if (! $arrPage['protected']) {
            return false;
        }

        $user = $this->security->getUser();
        if (! $user instanceof FrontendUser) {
            return true;
        }

        // the current user is not in any group
        if (! $user->groups) {
            return true;
        }

        // check if the current user is not in any group, which is allowed to access the current page
        return ! array_intersect((array) $user->groups, StringUtil::deserialize($arrPage['groups'], true));
    }

    protected function getRootIds(): array
    {
        if (! $this->moduleModel->hofff_navigation_defineRoots) {
            return [(int) $GLOBALS['objPage']->rootId];
        }

        return array_map(
            'intval',
            array_values(
                array_unique(
                    array_merge(
                        StringUtil::deserialize($this->moduleModel->hofff_navigation_roots_order, true),
                        StringUtil::deserialize($this->moduleModel->hofff_navigation_roots, true)
                    )
                )
            )
        );
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
    public function filterPages(array $arrPages, QueryBuilder $queryBuilder)
    {
        if (! $arrPages) {
            return $arrPages;
        }

        $queryBuilder
            ->andWhere('id IN (:ids)')
            ->setParameter('ids', array_keys(array_flip($arrPages)), Connection::PARAM_STR_ARRAY);
        $result = $queryBuilder->execute();

        if (! $this->isPermissionCheckRequired()) {
            return array_intersect(
                $arrPages,
                array_map(static fn(array $row): int => (int) $row['id'], $result->fetchAllAssociative())
            );
        } // restore order

        $arrPIDs  = [];
        $arrValid = [];
        while ($arrPage = $result->fetchAssociative()) {
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

            $query  = $this->pageQueryBuilder->createPageInformationQuery(array_keys($arrIDs));
            $result = $query->execute();

            while ($arrPage = $result->fetchAssociative()) {
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
    public function getNextLevel(array $arrPages, QueryBuilder $queryBuilder): array
    {
        if (! $arrPages) {
            return $arrPages;
        }

        $queryBuilder->setParameter('ids', array_keys(array_flip($arrPages)), Connection::PARAM_STR_ARRAY);
        $result = $queryBuilder->execute();

        $arrNext = [];
        if ($this->isPermissionCheckRequired()) {
            while ($arrPage = $result->fetchAssociative()) {
                if (! $this->isPermissionDenied($arrPage)) {
                    $arrNext[$arrPage['pid']][] = $arrPage['id'];
                }
            }
        } else {
            while ($arrPage = $result->fetchAssociative()) {
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
    public function getPrevLevel(array $arrPages): array
    {
        if (! $arrPages) {
            return $arrPages;
        }

        $result = $this->pageQueryBuilder->createPreviousLevelQuery(array_keys(array_flip($arrPages)))->execute();
        $arrPrev = [];
        while ($row = $result->fetchAssociative()) {
            $arrPrev[$row['id']] = $row['pid'];
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
}
