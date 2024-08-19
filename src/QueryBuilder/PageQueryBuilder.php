<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\QueryBuilder;

use Contao\ModuleModel;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\Security\Core\Security;

use function array_flip;
use function array_keys;
use function array_map;
use function array_merge;
use function count;

final class PageQueryBuilder extends BaseQueryBuilder
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
    ];

    private const ERROR_PAGE_TYPES = [
        'error_401',
        'error_403',
        'error_404',
    ];

    /** @var list<string> */
    private array $fields = [];

    public function __construct(Connection $connection, Security $security, private readonly ModuleModel $moduleModel)
    {
        parent::__construct($connection, $security);

        $this->determineFields();
    }

    /** @param list<int|string> $parentIds */
    public function createFetchItemsQuery(array $parentIds): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select(
                        ...array_map(
                            fn (string $field) => $field !== '*' ? $this->connection->quoteIdentifier($field) : $field,
                            $this->fields,
                        ),
                    )
                    ->andWhere('type != :rootType')
                    ->setParameter('rootType', 'root')
                    ->andWhere('pid IN (:pids)')
                    ->orderBy('sorting');

                $this
                    ->addHiddenCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_showHidden,
                        (bool) $this->moduleModel->hofff_navigation_isSitemap,
                    )
                    ->addPublishedCondition($queryBuilder)
                    ->addErrorPagesCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_showErrorPages,
                    )
                    ->addGuestsQueryParts(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_showGuests,
                    );
            },
        );

        $query->setParameter('pids', $parentIds, ArrayParameterType::INTEGER);

        return $query;
    }

    public function createRootIdsQuery(): QueryBuilder
    {
        return $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select(
                        ...array_map(
                            fn (string $field) => $this->connection->quoteIdentifier($field),
                            ['id', 'pid', 'protected', 'groups'],
                        ),
                    );

                $this
                    ->addHiddenCondition(
                        $queryBuilder,
                        ! $this->moduleModel->hofff_navigation_respectHidden,
                        (bool) $this->moduleModel->hofff_navigation_isSitemap,
                    )
                    ->addGuestsQueryParts(
                        $queryBuilder,
                        ! $this->moduleModel->hofff_navigation_respectGuests,
                    )
                    ->addPublishedCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_respectPublish,
                    );
            },
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
                $queryBuilder->select(
                    ...array_map(
                        fn (string $field) => $this->connection->quoteIdentifier($field),
                        ['id', 'pid', 'protected', 'groups'],
                    ),
                );

                $this
                    ->addHiddenCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_showHiddenStart,
                        (bool) $this->moduleModel->hofff_navigation_isSitemap,
                    )
                    ->addPublishedCondition($queryBuilder)
                    ->addErrorPagesCondition(
                        $queryBuilder,
                        (bool) $this->moduleModel->hofff_navigation_showErrorPages,
                    )
                    ->addGuestsQueryParts($queryBuilder, (bool) $this->moduleModel->hofff_navigation_showGuests);
            },
        );
    }

    /** @param list<int|string> $pageIds */
    public function createPageInformationQuery(array $pageIds): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select(
                        ...array_map(
                            fn (string $field) => $this->connection->quoteIdentifier($field),
                            ['id', 'pid', 'protected', 'groups'],
                        ),
                    )
                    ->where('id IN (:ids)');
            },
        );

        $query->setParameter('ids', $pageIds, ArrayParameterType::STRING);

        return $query;
    }

    /** @param list<int|string> $rootIds */
    public function createRootInformationQuery(array $rootIds): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                ->select(
                    ...array_map(
                        fn (string $field) => $this->connection->quoteIdentifier($field),
                        $this->fields,
                    ),
                )
                ->where('id IN (:rootIds)');
            },
        );

        $query->setParameter('rootIds', $rootIds, ArrayParameterType::STRING);

        return $query;
    }

    /** @param list<int|string> $pageIds */
    public function createPreviousLevelQuery(array $pageIds): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            static function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                ->select('id', 'pid')
                ->where('id IN (:ids)');
            },
        );

        $query->setParameter('ids', $pageIds, ArrayParameterType::STRING);

        return $query;
    }

    /**
     * Adds the or hidden state of a page.
     */
    private function addHiddenCondition(
        QueryBuilder $queryBuilder,
        bool $showHidden = false,
        bool $sitemap = false,
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

    private function addErrorPagesCondition(QueryBuilder $queryBuilder, bool $showErrorPages): self
    {
        if (! $showErrorPages) {
            $queryBuilder
                ->andWhere('type NOT IN (:errorPages)')
                ->setParameter('errorPages', self::ERROR_PAGE_TYPES, ArrayParameterType::STRING);
        }

        return $this;
    }

    private function determineFields(): void
    {
        $customFields = StringUtil::deserialize($this->moduleModel->hofff_navigation_addFields, true);

        if (count($customFields) > 10) {
            $this->fields[] = '*';

            return;
        }

        $schemaManager = $this->connection->createSchemaManager();
        $table         = $schemaManager->introspectTable('tl_page');

        if ($customFields === []) {
            $this->fields = array_keys(self::DEFAULT_FIELDS);

            if ($table->hasColumn('tabindex')) {
                $this->fields[] = 'tabindex';
            }

            return;
        }

        $customFields = array_flip($customFields);
        $fields       = [];

        foreach ($table->getColumns() as $column) {
            if (! isset($customFields[$column->getName()])) {
                continue;
            }

            $fields[$column->getName()] = true;
        }

        $this->fields = array_keys(array_merge($fields, self::DEFAULT_FIELDS));
    }
}
