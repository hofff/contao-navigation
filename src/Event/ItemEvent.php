<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Event;

use Contao\ModuleModel;

final class ItemEvent extends Event
{
    /** @param array<string,mixed> $item */
    public function __construct(ModuleModel $moduleModel, private array $item)
    {
        parent::__construct($moduleModel);
    }

    /** @param array<string,mixed> $item */
    public function changeItem(array $item): void
    {
        $this->item = $item;
    }

    /** @return array<string,mixed> */
    public function item(): array
    {
        return $this->item;
    }
}
