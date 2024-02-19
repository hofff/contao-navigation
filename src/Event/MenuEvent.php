<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Event;

use Contao\ModuleModel;

final class MenuEvent extends Event
{
    /** @param list<int> $rootIds */
    public function __construct(ModuleModel $moduleModel, private array $rootIds)
    {
        parent::__construct($moduleModel);
    }

    /** @param list<int> $rootIds */
    public function changeRootIds(array $rootIds): void
    {
        $this->rootIds = $rootIds;
    }

    /** @return list<int> */
    public function rootIds(): array
    {
        return $this->rootIds;
    }
}
