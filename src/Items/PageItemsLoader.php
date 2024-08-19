<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Items;

use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Hofff\Contao\Navigation\QueryBuilder\PageQueryBuilder;
use Hofff\Contao\Navigation\Security\PagePermissionGuard;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface AS Security;

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
    /** @psalm-suppress PropertyNotSetInConstructor - Will be initialized in the only public method load() */
    private PageQueryBuilder $pageQueryBuilder;

    /** @psalm-suppress PropertyNotSetInConstructor - Will be initialized in the only public method load() */
    private PageItems $items;

    /** @psalm-suppress PropertyNotSetInConstructor - Will be initialized in the only public method load() */
    private ModuleModel $moduleModel;

    /** @var list<int> */
    private array $stopLevels = [PHP_INT_MAX];

    private int $hardLevel = PHP_INT_MAX;

    private int|null $activeId = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly Security $security,
        private readonly PagePermissionGuard $guard,
    ) {
    }

    /** @param list<int> $stopLevels */
    public function load(
        ModuleModel $moduleModel,
        PageModel $currentPage,
        array $stopLevels = [PHP_INT_MAX],
        int $hardLevel = PHP_INT_MAX,
        int|null $activeId = null,
    ): PageItems {
        $this->items            = new PageItems($currentPage);
        $this->pageQueryBuilder = new PageQueryBuilder($this->connection, $this->security, $moduleModel);
        $this->moduleModel      = $moduleModel;
        $this->stopLevels       = $stopLevels;
        $this->hardLevel        = $hardLevel;
        $this->activeId         = $activeId;

        $this->items->roots = array_fill_keys($this->calculateRootIDs(), true);
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
        $result = $this->pageQueryBuilder->createRootInformationQuery($rootIds)->executeQuery();
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
            // if $endParentIds == $parentIds the next $parentIds will be empty -> leave loop
            if ($level > $this->hardLevel) {
                $endParentIds = $parentIds;
            } elseif ($level > $stopLevels[0]) {
                count($stopLevels) > 1 && array_shift($stopLevels);
                $endParentIds = [];
                foreach ($parentIds as $parentId) {
                    if ($this->items->isInTrail((int) $parentId)) {
                        continue;
                    }

                    $endParentIds[$parentId] = true;
                }
            } else {
                $endParentIds = [];
            }

            $result = $this->pageQueryBuilder->createFetchItemsQuery($parentIds)->executeQuery();
            if ($result->rowCount() === 0) {
                break;
            }

            $parentIds = [];
            while ($page = $result->fetchAssociative()) {
                if (isset($this->items->items[$page['id']])) {
                    continue;
                }

                if (! $this->guard->isPermissionGranted($this->moduleModel, $page)) {
                    continue;
                }

                if (! isset($endParentIds[$page['pid']])) {
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

    /** @return list<int> */
    private function calculateRootIDs(): array
    {
        $rootIds = $this->getRootIds();
        if ($this->moduleModel->hofff_navigation_currentAsRoot) {
            /** @psalm-suppress RedundantCastGivenDocblockType */
            array_unshift($rootIds, (int) $this->items->currentPage->id);
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
            $path    = array_map('intval', $this->items->currentPage->trail);
            $path[]  = $this->activeId;
            $rootIds = array_values(array_intersect($rootIds, $path));
        }

        return $rootIds;
    }

    /** @return list<int> */
    private function getRootIds(): array
    {
        if (! $this->moduleModel->hofff_navigation_defineRoots) {
            /** @psalm-suppress RedundantCastGivenDocblockType */
            return [(int) $this->items->currentPage->rootId];
        }

        return array_map(
            'intval',
            array_values(
                array_unique(
                    array_merge(
                        StringUtil::deserialize($this->moduleModel->hofff_navigation_roots_order, true),
                        StringUtil::deserialize($this->moduleModel->hofff_navigation_roots, true),
                    ),
                ),
            ),
        );
    }

    /**
     * Filters the given array of page IDs in regard of publish state,
     * required permissions (protected and guests only) and hidden state, according to
     * this navigations settings.
     * Maintains relative order of the input array.
     *
     * For performance reason $pageIds is NOT "intval"ed. Make sure $pageIds
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
            ->setParameter('ids', $pageIds, ArrayParameterType::STRING);
        $result = $queryBuilder->executeQuery();

        if (! $this->guard->isPermissionCheckRequired($this->moduleModel)) {
            return array_values(
                array_intersect(
                    $pageIds,
                    array_map(static fn (array $row): int => (int) $row['id'], $result->fetchAllAssociative()),
                ),
            );
        } // restore order

        $parentIds = [];
        $valid     = [];
        while ($page = $result->fetchAssociative()) {
            if ($this->guard->isPermissionDenied($page)) {
                continue;
            }

            $valid[] = $page['id'];
            /*
             * do not remove the protected check! permission denied checks for
             * more, but we need to know, if we must recurse to parent pages,
             * for permission check, which must not be done, when this page
             * defines access rights.
             */
            if ($page['protected'] || $page['pid'] === 0) {
                continue;
            }

            $parentIds[$page['pid']][] = $page['id'];
        }

        // exclude pages which are in a protected path
        while (count($parentIds)) {
            $currentIds = $parentIds;
            $parentIds  = [];

            $query  = $this->pageQueryBuilder->createPageInformationQuery(array_keys($currentIds));
            $result = $query->executeQuery();

            while ($page = $result->fetchAssociative()) {
                if (! $page['protected']) { // do not remove, see above
                    if ($page['pid'] !== 0) {
                        $parentIds[$page['pid']] = isset($parentIds[$page['pid']])
                            ? array_merge($parentIds[$page['pid']], $currentIds[$page['id']])
                            : $currentIds[$page['id']];
                    }
                } elseif ($this->guard->isPermissionDenied($page)) {
                    $valid = array_diff($valid, $currentIds[$page['id']]);
                }
            }
        }

        return array_values(array_intersect($pageIds, $valid));
    }

    /**
     * Retrieves the subpages of the given array of page IDs in respect of the
     * given conditions, which are added to the WHERE clause of the query.
     * Maintains relative order of the input array.
     *
     * For performance reason $pageIds is NOT "intval"ed. Make sure $pageIds
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

        $queryBuilder->setParameter('ids', array_keys(array_flip($pageIds)), ArrayParameterType::STRING);
        $result = $queryBuilder->executeQuery();

        $next = [];
        if ($this->guard->isPermissionCheckRequired($this->moduleModel)) {
            while ($page = $result->fetchAssociative()) {
                if ($this->guard->isPermissionDenied($page)) {
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
        foreach ($pageIds as $pageId) {
            if (! isset($next[$pageId])) {
                continue;
            }

            $nextLevel = array_merge($nextLevel, $next[$pageId]);
        }

        return $nextLevel;
    }

    /**
     * Retrieves the parents of the given array of page IDs in respect of the
     * given conditions, which are added to the WHERE clause of the query.
     * Maintains relative order of the input array and merges subsequent parent
     * IDs.
     *
     * For performance reason $pageIds is NOT "intval"ed. Make sure $pageIds
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

        $result   = $this->pageQueryBuilder->createPreviousLevelQuery($pageIds)->executeQuery();
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
