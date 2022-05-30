<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\QueryBuilder;

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

    public function addPublishedCondition(QueryBuilder $queryBuilder): self
    {
        if (BE_USER_LOGGED_IN) {
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

    public function addGuestsQueryParts(QueryBuilder $queryBuilder, bool $showGuests): self
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
