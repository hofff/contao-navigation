<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\QueryBuilder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface AS Security;

use function time;

abstract class BaseQueryBuilder
{
    /** @var array<string,QueryBuilder> */
    private array $queries = [];

    public function __construct(protected readonly Connection $connection, private readonly Security $security)
    {
    }

    protected function query(string $name, callable $builder): QueryBuilder
    {
        if (! isset($this->queries[$name])) {
            $this->queries[$name] = $this->connection->createQueryBuilder()->from('tl_page');
            $builder($this->queries[$name]);
        }

        return clone $this->queries[$name];
    }

    /** @return static */
    protected function addGuestsQueryParts(QueryBuilder $queryBuilder, bool $showGuests = false): self
    {
        if ($showGuests) {
            return $this;
        }

        if ($this->security->isGranted('ROLE_MEMBER') && ! $this->security->isGranted('ROLE_USER')) {
            $queryBuilder->andWhere('guests != 1');
        }

        return $this;
    }

    /** @return static */
    protected function addPublishedCondition(QueryBuilder $queryBuilder, bool $respectPublished = true): self
    {
        if (! $respectPublished || $this->security->isGranted('ROLE_USER')) {
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
}
