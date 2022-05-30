<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Event;

use Contao\ModuleModel;

final class ItemEvent extends Event
{
    private array $item;

    public function __construct(ModuleModel $moduleModel, array $item)
    {
        parent::__construct($moduleModel);

        $this->item = $item;
    }

    public function changeItem(array $item): void
    {
        $this->item = $item;
    }

    public function item(): array
    {
        return $this->item;
    }
}
