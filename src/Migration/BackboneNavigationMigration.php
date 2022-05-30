<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

final class BackboneNavigationMigration extends AbstractMigration
{
    private Connection $connection;

    private const OLD_PREFIX = 'backboneit_navigation_';

    private const NEW_PREFIX = 'hofff_navigation_';

    private const FIELDS = [
        'roots_order',
        'start',
        'respectHidden',
        'respectPublish',
        'respectGuests',
        'includeStart',
        'showHiddenStart',
        'showHidden',
        'showProtected',
        'showGuests',
        'addFields',
        'noForwardResolution',
        'showErrorPages',
        'disableHooks',
        'currentAsRoot',
        'defineRoots',
        'defineStop',
        'stop',
        'defineHard',
        'hard',
        'isSitemap',
        'hideSingleLevel',
        'addLegacyCss',
    ];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function shouldRun(): bool
    {
        $affectedFields = $this->determineAffectedFields();

        return $affectedFields !== [];
    }

    public function run(): MigrationResult
    {
        $affectedFields = $this->determineAffectedFields();

        foreach ($affectedFields as $field) {
            $this->renameField($field);
        }

        return $this->createResult(true, 'Renamed field prefix from backboneit_navigation_ to hofff_navigation_.');
    }

    /** @return list<string> */
    private function determineAffectedFields(): array
    {
        $table  = $this->connection->getSchemaManager()->listTableDetails('tl_module');
        $fields = [];

        foreach (self::FIELDS as $field) {
            if (! $table->hasColumn(self::OLD_PREFIX . $field) || $table->hasColumn(self::NEW_PREFIX . $field)) {
                continue;
            }

            $fields[] = $field;
        }

        return $fields;
    }

    private function renameField(string $field): void
    {
        $this->connection->executeStatement(
            sprintf(
                'ALTER TABLE tl_module RENAME COLUMN %s%s TO %s%s',
                self::OLD_PREFIX,
                $field,
                self::NEW_PREFIX,
                $field,
            )
        );
    }
}
