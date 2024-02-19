<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Event;

use Contao\ModuleModel;
use Hofff\Contao\Navigation\Items\PageItems;

final class TreeEvent extends Event
{
    public function __construct(ModuleModel $moduleModel, private readonly PageItems $items)
    {
        parent::__construct($moduleModel);
    }

    public function items(): PageItems
    {
        return $this->items;
    }
}
