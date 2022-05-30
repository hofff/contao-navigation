<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

use function is_int;
use function sprintf;

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
        // phpcs:ignore Squiz.Arrays.ArrayDeclaration.KeySpecified
        'disableHooks' => 'disableEvents',
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
        if ($this->hasBackboneItNavigationModules()) {
            return true;
        }

        return $this->determineAffectedFields() !== [];
    }

    public function run(): MigrationResult
    {
        $this->renameNavigationModules();

        $affectedFields = $this->determineAffectedFields();
        foreach ($affectedFields as $oldName => $newName) {
            $this->renameField($oldName, $newName);
        }

        return $this->createResult(true);
    }

    private function hasBackboneItNavigationModules(): bool
    {
        $result = $this->connection->executeQuery(
            'SELECT count(id) FROM tl_module WHERE type=:type',
            ['type' => 'backboneit_navigation_menu']
        );

        return $result->fetchOne() > 0;
    }

    private function renameNavigationModules(): void
    {
        $this->connection->update(
            'tl_module',
            ['type' => 'hofff_navigation_menu'],
            ['type' => 'backboneit_navigation_menu']
        );
    }

    /** @return array<string,string> */
    private function determineAffectedFields(): array
    {
        $table  = $this->connection->getSchemaManager()->listTableDetails('tl_module');
        $fields = [];

        foreach (self::FIELDS as $oldName => $newName) {
            if (is_int($oldName)) {
                $oldName = $newName;
            }

            if (! $table->hasColumn(self::OLD_PREFIX . $oldName) || $table->hasColumn(self::NEW_PREFIX . $newName)) {
                continue;
            }

            $fields[$oldName] = $newName;
        }

        return $fields;
    }

    private function renameField(string $oldName, string $newName): void
    {
        $this->connection->executeStatement(
            sprintf(
                'ALTER TABLE tl_module RENAME COLUMN %s%s TO %s%s',
                self::OLD_PREFIX,
                $oldName,
                self::NEW_PREFIX,
                $newName,
            )
        );
    }
}
