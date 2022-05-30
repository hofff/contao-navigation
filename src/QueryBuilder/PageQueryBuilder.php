<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\QueryBuilder;

use Contao\ModuleModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

use function array_flip;
use function array_keys;
use function array_merge;
use function time;

final class PageQueryBuilder
{
    public const DEFAULT_FIELDS = [
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

    private const ERROR_PAGE_TYPES = [
        'error_401',
        'error_403',
        'error_404',
        'error_410'
    ];

    private Connection $connection;

    private ModuleModel $moduleModel;
    
    private array $fields = [];
    
    /** @var array<string,QueryBuilder> */
    private $queries = [];
    
    public function __construct(Connection $connection, ModuleModel $moduleModel)
    {
        $this->connection  = $connection;
        $this->moduleModel = $moduleModel;

        $this->determineFields();
    }

    public function createFetchItemsQuery(array $parentIds): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select(... $this->fields)
                    ->andWhere('type != :rootType')
                    ->setParameter('rootType', 'root')
                    ->andWhere('pid IN (:pids)')
                    ->orderBy('sorting');

                $this
                    ->addHiddenCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_showHidden,
                        (bool) $this->moduleModel->hofff_navigation_isSitemap
                    )
                    ->addPublishedCondition($queryBuilder)
                    ->addErrorPagesCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_showErrorPages
                    )
                    ->addGuestsQueryParts(
                        $queryBuilder,
                        (bool) $this->moduleModel->backboneit_navigation_showGuests
                    );
            }
        );

        $query->setParameter('pids', $parentIds, Connection::PARAM_INT_ARRAY);

        return $query;
    }

    public function createRootIdsQuery(): QueryBuilder
    {
        return $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select('id', 'pid', 'protected', 'groups');

                $this
                    ->addHiddenCondition(
                        $queryBuilder,
                        ! $this->moduleModel->hofff_navigation_respectHidden,
                        (bool) $this->moduleModel->hofff_navigation_isSitemap
                    )
                    ->addGuestsQueryParts(
                        $queryBuilder,
                        ! $this->moduleModel->hofff_navigation_respectGuests
                    )
                    ->addPublishedCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_respectPublish
                    );
            }
        );
    }

    public function createStartRootIdsQuery(): QueryBuilder
    {
        if (! $this->moduleModel->hofff_navigation_includeStart) {
            return $this->createRootIdsQuery();
        }

        return $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select('id', 'pid', 'protected', 'groups');

                $this
                    ->addHiddenCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_showHiddenStart,
                        (bool) $this->moduleModel->hofff_navigation_isSitemap
                    )
                    ->addPublishedCondition($queryBuilder)
                    ->addErrorPagesCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_showErrorPages
                    )
                    ->addGuestsQueryParts($queryBuilder, (bool) $this->moduleModel->hofff_navigation_showGuests);
            }
        );
    }

    public function createPageInformationQuery(array $pageIds): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select('id', 'pid', 'protected', 'groups')
                    ->where('id IN (:ids)');
            }
        );

        $query->setParameter('ids', $pageIds, Connection::PARAM_STR_ARRAY);

        return $query;
    }

    public function createRootInformationQuery(array $rootIds): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                ->select(... $this->fields)
                ->where('id IN (:rootIds)');
            }
        );

        $query->setParameter('rootIds', $rootIds, Connection::PARAM_STR_ARRAY);

        return $query;
    }

    public function createPreviousLevelQuery(array $pageIds): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                ->select('id', 'pid')
                ->where('id IN (:ids)');
            }
        );

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

    private function query(string $name, callable $builder): QueryBuilder
    {
        if (! isset ($this->queries[$name])) {
            $this->queries[$name] = $this->connection->createQueryBuilder()->from('tl_page');
            $builder($this->queries[$name]);
        }

        return clone $this->queries[$name];
    }

    private function determineFields(): void
    {
        $customFields = StringUtil::deserialize($this->moduleModel->hofff_navigation_addFields, true);

        if (count($customFields) > 10) {
            $this->fields[] = '*';

            return;
        }

        if ($customFields === []) {
            $this->fields = array_keys(self::DEFAULT_FIELDS);

            return;
        }

        $customFields = array_flip($customFields);
        $table        = $this->connection->getSchemaManager()->listTableDetails('tl_page');
        $fields       = [];

        foreach ($table->getColumns() as $column) {
            if (isset($customFields[$column->getName()])) {
                $fields[$column->getName()] = true;
            }
        }

        $this->fields = array_keys(array_merge($fields, self::DEFAULT_FIELDS));
    }
}
