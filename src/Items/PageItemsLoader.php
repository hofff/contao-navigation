<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Items;

use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Hofff\Contao\Navigation\QueryBuilder\PageQueryBuilder;
use Symfony\Component\Security\Core\Security;

use function array_diff;
use function array_fill_keys;
use function array_flip;
use function array_intersect;
use function array_keys;
use function array_map;
use function array_merge;
use function array_shift;
use function array_unique;
use function array_unshift;
use function array_values;
use function count;
use function in_array;
use function max;

use const PHP_INT_MAX;

/** @SuppressWarnings(PHPMD.ExcessiveClassComplexity) */
final class PageItemsLoader
{
    private Connection $connection;

    private Security $security;

    /** @psalm-suppress PropertyNotSetInConstructor - Will be initialized in the only public method load() */
    private PageQueryBuilder $pageQueryBuilder;

    /** @psalm-suppress PropertyNotSetInConstructor - Will be initialized in the only public method load() */
    private PageItems $items;

    /** @psalm-suppress PropertyNotSetInConstructor - Will be initialized in the only public method load() */
    private ModuleModel $moduleModel;

    /** @var list<int> */
    private array $stopLevels = [PHP_INT_MAX];

    private int $hardLevel = PHP_INT_MAX;

    private ?int $activeId = null;

    public function __construct(Connection $connection, Security $security)
    {
        $this->connection = $connection;
        $this->security   = $security;
    }

    /**
     * @param list<int> $stopLevels
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function load(
        ModuleModel $moduleModel,
        array $stopLevels = [PHP_INT_MAX],
        int $hardLevel = PHP_INT_MAX,
        ?int $activeId = null
    ): PageItems {
        $this->items        = new PageItems();
        $this->items->trail = array_flip(isset($GLOBALS['objPage']) ? $GLOBALS['objPage']->trail : []);

        $this->pageQueryBuilder = new PageQueryBuilder($this->connection, $this->security, $moduleModel);
        $this->moduleModel      = $moduleModel;
        $this->stopLevels       = $stopLevels;
        $this->hardLevel        = $hardLevel;
        $this->activeId         = $activeId;

        $this->items->roots = array_fill_keys(array_flip($this->calculateRootIDs()), true);
        if (! $this->items->roots) {
            return $this->items;
        }

        $rootIds = array_keys($this->items->roots);

        if (! $moduleModel->hofff_navigation_includeStart) {
            $this->fetchItems($rootIds);

            return $this->items;
        }

        $this->fetchItems($rootIds, 2);

        /** @psalm-var Result $result */
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
     * @param list<int|string> $parentIds
     * @param int              $level     (optional, defaults to 1) The level of the PIDs.
     *
     * @return array<int,bool>
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function fetchItems(array $parentIds, int $level = 1): array
    {
        $level = max(1, $level);

        // nothing todo
        // $level == $hardStop + 1 requires subitem detection for css class "submenu" calculation
        if (! $parentIds || $level - 1 > $this->hardLevel) {
            return [];
        }

        $fetched    = [];
        $stopLevels = $this->stopLevels;

        while ($parentIds) {
            // if $arrEndPIDs == $arrPIDs the next $arrPIDs will be empty -> leave loop
            if ($level > $this->hardLevel) {
                $arrEndPIDs = $parentIds;
            } elseif ($level > $stopLevels[0]) {
                count($stopLevels) > 1 && array_shift($stopLevels);
                $arrEndPIDs = [];
                foreach ($parentIds as $intPID) {
                    if ($this->items->isInTrail((int) $intPID)) {
                        continue;
                    }

                    $arrEndPIDs[$intPID] = true;
                }
            } else {
                $arrEndPIDs = [];
            }

            /** @psalm-var Result $result */
            $result = $this->pageQueryBuilder->createFetchItemsQuery($parentIds)->execute();
            if ($result->rowCount() === 0) {
                break;
            }

            $parentIds = [];
            /** @psalm-var Result $result */
            while ($page = $result->fetchAssociative()) {
                if (isset($this->items->items[$page['id']])) {
                    continue;
                }

                if (! $this->isPermissionGranted($this->moduleModel, $page)) {
                    continue;
                }

                if (! isset($arrEndPIDs[$page['pid']])) {
                    if (! in_array((int) $page['id'], $this->items->subItems[(int) $page['pid']] ?? [])) {
                        $this->items->subItems[(int) $page['pid']][] = (int) $page['id']; // for order of items
                    }

                    $this->items->items[(int) $page['id']] = $page; // item datasets
                    $parentIds[]                           = $page['id']; // ids of current layer (for next layer pids)
                    $fetched[$page['id']]                  = true; // fetched in this method
                } elseif (! isset($this->items->subItems[(int) $page['pid']])) {
                    $this->items->subItems[(int) $page['pid']] = [];
                }
            }

            $level++;
        }

        return $fetched;
    }

    /**
     * @return list<int>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function calculateRootIDs(): array
    {
        $rootIds = $this->getRootIds();
        if ($this->moduleModel->hofff_navigation_currentAsRoot) {
            array_unshift($rootIds, (int) $GLOBALS['objPage']->id);
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
        if ($stopLevels[0] === 0) { // special case, keep only roots within the current path
            $path    = array_map('intval', $GLOBALS['objPage']->trail);
            $path[]  = $this->activeId;
            $rootIds = array_values(array_intersect($rootIds, $path));
        }

        return $rootIds;
    }

    public function isPermissionCheckRequired(): bool
    {
        return ! $this->security->isGranted('ROLE_USER') && ! $this->moduleModel->hofff_navigation_showProtected;
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
     * @param array<string,mixed> $page The page dataset of the current page, with at least
     *                                  groups and protected attributes set.
     *
     * @return bool If the permission is granted true, otherwise false.
     */
    public function isPermissionGranted(ModuleModel $model, array $page): bool
    {
        // be users have access everywhere
        if ($this->security->isGranted('ROLE_USER')) {
            return true;
        }

        // protection is ignored
        if ($model->hofff_navigation_showProtected) {
            return true;
        }

        return ! $this->isPermissionDenied($page);
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
     * @param array<string,mixed> $page The page dataset of the current page, with at least
     *                                  groups and protected attributes set.
     *
     * @return bool If the permission is denied true, otherwise false.
     */
    public function isPermissionDenied(array $page): bool
    {
        // this page is not protected
        if (! $page['protected']) {
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
        return ! array_intersect((array) $user->groups, StringUtil::deserialize($page['groups'], true));
    }

    /**
     * @return list<int>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getRootIds(): array
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
     * @param list<int> $pageIds An array of page IDs to filter
     *
     * @return list<int> Filtered array of page IDs
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function filterPages(array $pageIds, QueryBuilder $queryBuilder): array
    {
        if (! $pageIds) {
            return $pageIds;
        }

        $queryBuilder
            ->andWhere('id IN (:ids)')
            ->setParameter('ids', $pageIds, Connection::PARAM_STR_ARRAY);
        /** @psalm-var Result $result */
        $result = $queryBuilder->execute();

        if (! $this->isPermissionCheckRequired()) {
            return array_values(
                array_intersect(
                    $pageIds,
                    array_map(static fn (array $row): int => (int) $row['id'], $result->fetchAllAssociative())
                )
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
            if ($arrPage['protected'] || $arrPage['pid'] === 0) {
                continue;
            }

            $arrPIDs[$arrPage['pid']][] = $arrPage['id'];
        }

        // exclude pages which are in a protected path
        while (count($arrPIDs)) {
            $arrIDs  = $arrPIDs;
            $arrPIDs = [];

            $query = $this->pageQueryBuilder->createPageInformationQuery(array_keys($arrIDs));
            /** @psalm-var Result $result */
            $result = $query->execute();

            while ($arrPage = $result->fetchAssociative()) {
                if (! $arrPage['protected']) { // do not remove, see above
                    if ($arrPage['pid'] !== 0) {
                        $arrPIDs[$arrPage['pid']] = isset($arrPIDs[$arrPage['pid']])
                            ? array_merge($arrPIDs[$arrPage['pid']], $arrIDs[$arrPage['id']])
                            : $arrIDs[$arrPage['id']];
                    }
                } elseif ($this->isPermissionDenied($arrPage)) {
                    $arrValid = array_diff($arrValid, $arrIDs[$arrPage['id']]);
                }
            }
        }

        return array_values(array_intersect($pageIds, $arrValid));
    }

    /**
     * Retrieves the subpages of the given array of page IDs in respect of the
     * given conditions, which are added to the WHERE clause of the query.
     * Maintains relative order of the input array.
     *
     * For performance reason $arrPages is NOT "intval"ed. Make sure $arrPages
     * contains no hazardous code.
     *
     * @param list<int> $pageIds An array of parent IDs
     *
     * @return list<int> The child IDs
     */
    public function getNextLevel(array $pageIds, QueryBuilder $queryBuilder): array
    {
        if (! $pageIds) {
            return $pageIds;
        }

        $queryBuilder->setParameter('ids', array_keys(array_flip($pageIds)), Connection::PARAM_STR_ARRAY);
        /** @psalm-var Result */
        $result = $queryBuilder->execute();

        $next = [];
        if ($this->isPermissionCheckRequired()) {
            while ($page = $result->fetchAssociative()) {
                if ($this->isPermissionDenied($page)) {
                    continue;
                }

                $next[(int) $page['pid']][] = (int) $page['id'];
            }
        } else {
            while ($page = $result->fetchAssociative()) {
                $next[(int) $page['pid']][] = (int) $page['id'];
            }
        }

        $nextLevel = [];
        foreach ($pageIds as $intID) {
            if (! isset($next[$intID])) {
                continue;
            }

            $nextLevel = array_merge($nextLevel, $next[$intID]);
        }

        return $nextLevel;
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
     * @param list<int> $pageIds An array of child IDs
     *
     * @return list<int> The parent IDs
     */
    private function getPrevLevel(array $pageIds): array
    {
        if (! $pageIds) {
            return $pageIds;
        }

        /** @psalm-var Result $result */
        $result   = $this->pageQueryBuilder->createPreviousLevelQuery($pageIds)->execute();
        $previous = [];
        while ($row = $result->fetchAssociative()) {
            $previous[(int) $row['id']] = (int) $row['pid'];
        }

        $prevLevel = [];
        $parentId  = -1;
        foreach ($pageIds as $pageId) {
            if (! isset($previous[$pageId]) || $previous[$pageId] === $parentId) {
                continue;
            }

            $prevLevel[] = $parentId = $previous[$pageId];
        }

        return $prevLevel;
    }
}
