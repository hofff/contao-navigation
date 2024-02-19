<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\QueryBuilder;

use Doctrine\DBAL\Query\QueryBuilder;

final class RedirectPageQueryBuilder extends BaseQueryBuilder
{
    public function createJumpToQuery(int $jumpToId): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select('*')
                    ->where('id=:id')
                    ->setMaxResults(1);

                $this
                    ->addGuestsQueryParts($queryBuilder)
                    ->addPublishedCondition($queryBuilder);
            },
        );

        $query->setParameter('id', $jumpToId);

        return $query;
    }

    public function createFallbackQuery(int $fallbackSearchId): QueryBuilder
    {
        $query = $this->query(
            __FUNCTION__,
            function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select('*')
                    ->where('type=:type')
                    ->andWhere('pid=:pid')
                    ->setParameter('type', 'regular')
                    ->orderBy('sorting')
                    ->setMaxResults(1);

                $this
                    ->addGuestsQueryParts($queryBuilder);
            },
        );

        $query->setParameter('pid', $fallbackSearchId);

        return $query;
    }
}
