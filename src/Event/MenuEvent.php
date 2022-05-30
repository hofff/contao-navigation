<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Event;

use Contao\ModuleModel;

final class MenuEvent extends Event
{
    private array $rootIds;

    public function __construct(ModuleModel $moduleModel, array $rootIds)
    {
        parent::__construct($moduleModel);

        $this->rootIds = $rootIds;
    }

    public function changeRootIds(array $rootIds): void
    {
        $this->rootIds = $rootIds;
    }

    public function rootIds(): array
    {
        return $this->rootIds;
    }
}
