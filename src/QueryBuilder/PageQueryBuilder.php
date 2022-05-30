<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\QueryBuilder;

use Contao\ModuleModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

use function time;

final class PageQueryBuilder
{
    private const ERROR_PAGE_TYPES = [
        'error_401',
        'error_403',
        'error_404',
        'error_410'
    ];

    private Connection $connection;

    private ModuleModel $moduleModel;
    
    private array $fields;
    
    /** @var array<string,QueryBuilder> */
    private $queries = [];
    
    public function __construct(Connection $connection, ModuleModel $moduleModel, array $fields)
    {
        $this->connection  = $connection;
        $this->moduleModel = $moduleModel;
        $this->fields      = $fields;
    }

    public function createFetchItemsQuery(array $parentIds): QueryBuilder
    {
        if (! isset($this->queries[__FUNCTION__])) {
            $this->queries[__FUNCTION__] = $this->connection->createQueryBuilder()
                ->from('tl_page')
                ->select(... $this->fields)
                ->andWhere('type != :rootType')
                ->setParameter('rootType', 'root')
                ->andWhere('pid IN (:pids)')
                ->orderBy('sorting');

            $this
                ->addHiddenCondition(
                    $this->queries[__FUNCTION__],
                    (bool) $this->moduleModel->hofff_navigation_showHidden,
                    (bool) $this->moduleModel->hofff_navigation_isSitemap
                )
                ->addPublishedCondition($this->queries[__FUNCTION__])
                ->addErrorPagesCondition(
                    $this->queries[__FUNCTION__],
                    (bool) $this->moduleModel->hofff_navigation_showErrorPages
                )
                ->addGuestsQueryParts(
                    $this->queries[__FUNCTION__],
                    (bool) $this->moduleModel->backboneit_navigation_showGuests
                );
        }

        $query = clone $this->queries[__FUNCTION__];
        $query->setParameter('pids', $parentIds, Connection::PARAM_INT_ARRAY);

        return $query;
    }

    public function createRootIdsQuery(): QueryBuilder
    {
        if (! isset($this->queries[__FUNCTION__])) {
            $this->queries[__FUNCTION__] = $this->connection->createQueryBuilder();
            $this->queries[__FUNCTION__]
                ->select('id', 'pid', 'protected', 'groups')
                ->from('tl_page');

            $this
                ->addHiddenCondition(
                    $this->queries[__FUNCTION__],
                    ! $this->moduleModel->hofff_navigation_respectHidden,
                    (bool) $this->moduleModel->hofff_navigation_isSitemap
                )
                ->addGuestsQueryParts(
                    $this->queries[__FUNCTION__],
                    ! $this->moduleModel->hofff_navigation_respectGuests
                )
                ->addPublishedCondition(
                    $this->queries[__FUNCTION__],
                    (bool) $this->moduleModel->hofff_navigation_respectPublish
                );
        }

        return clone $this->queries[__FUNCTION__];
    }

    public function createStartRootIdsQuery(): QueryBuilder
    {
        if (! $this->moduleModel->hofff_navigation_includeStart) {
            return $this->createRootIdsQuery();
        }

        if (! isset($this->queries[__FUNCTION__])) {
            $this->queries[__FUNCTION__] = $this->connection->createQueryBuilder();

            $this->queries[__FUNCTION__]
                ->select('id', 'pid', 'protected', 'groups')
                ->from('tl_page');

            $this
                ->addHiddenCondition(
                    $this->queries[__FUNCTION__],
                    (bool) $this->moduleModel->hofff_navigation_showHiddenStart,
                    (bool) $this->moduleModel->hofff_navigation_isSitemap
                )
                ->addPublishedCondition($this->queries[__FUNCTION__])
                ->addErrorPagesCondition(
                    $this->queries[__FUNCTION__],
                    (bool) $this->moduleModel->hofff_navigation_showErrorPages
                )
                ->addGuestsQueryParts($this->queries[__FUNCTION__], (bool) $this->moduleModel->hofff_navigation_showGuests);
        }

        return clone $this->queries[__FUNCTION__];
    }

    public function createPageInformationQuery(array $pageIds): QueryBuilder
    {
        if (! isset($this->queries[__FUNCTION__])) {
            $this->queries[__FUNCTION__] = $this->connection->createQueryBuilder();
            $this->queries[__FUNCTION__]
                ->select('id', 'pid', 'protected', 'groups')
                ->from('tl_page')
                ->where('id IN (:ids)');
        }

        $query = clone $this->queries[__FUNCTION__];
        $query->setParameter('ids', $pageIds, Connection::PARAM_STR_ARRAY);

        return $query;
    }

    public function createRootInformationQuery(array $rootIds): QueryBuilder
    {
        if (! isset($this->queries[__FUNCTION__])) {
            $this->queries[__FUNCTION__] = $this->connection->createQueryBuilder();
            $this->queries[__FUNCTION__]
                ->select(... $this->fields)
                ->from('tl_page')
                ->where('id IN (:rootIds)');
        }

        $query = clone $this->queries[__FUNCTION__];
        $query->setParameter('rootIds', $rootIds, Connection::PARAM_STR_ARRAY);

        return $query;
    }

    public function createPreviousLevelQuery(array $pageIds): QueryBuilder
    {
        if (! isset($this->queries[__FUNCTION__])) {
            $this->queries[__FUNCTION__] = $this->connection->createQueryBuilder();
            $this->queries[__FUNCTION__]
                ->select('id', 'pid')
                ->from('tl_page')
                ->where('id IN (:ids)');
        }

        $query = clone $this->queries[__FUNCTION__];
        $query->setParameter('ids', $pageIds);

        return $query;
    }

    public function addPublishedCondition(QueryBuilder $queryBuilder, bool $respectPublished = true): self
    {
        if (BE_USER_LOGGED_IN || ! $respectPublished) {
            return $this;
        }

        static $time;
        if (! $time) {
            $time = time();
        }

        $queryBuilder
            ->andWhere('(start = \'\' OR start < :time) AND (stop = \'\' OR stop > :time) AND published = 1')
            ->setParameter('time', $time);

        return $this;
    }

    /**
     * Adds the or hidden state of a page.
     *
     * @return self
     */
    public function addHiddenCondition(
        QueryBuilder $queryBuilder,
        bool $showHidden = false,
        bool $sitemap = false
    ): self {
        if ($showHidden) {
            return $this;
        }

        if ($sitemap) {
            $queryBuilder->andWhere('(sitemap = \'map_always\' OR (hide != 1 AND sitemap != \'map_never\'))');

            return $this;
        }

        $queryBuilder->andWhere('hide != 1');

        return $this;
    }

    public function addErrorPagesCondition(QueryBuilder $queryBuilder, bool $showErrorPages): self
    {
        if (! $showErrorPages) {
            $queryBuilder
                ->andWhere('type NOT IN (:errorPages)')
                ->setParameter('errorPages', self::ERROR_PAGE_TYPES, Connection::PARAM_STR_ARRAY);
        }

        return $this;
    }

    public function addGuestsQueryParts(QueryBuilder $queryBuilder, bool $showGuests = false): self
    {
        if ($showGuests) {
            return $this;
        }

        if (FE_USER_LOGGED_IN && ! BE_USER_LOGGED_IN) {
            $queryBuilder->andWhere('guests != 1');
        }

        return $this;
    }
}
