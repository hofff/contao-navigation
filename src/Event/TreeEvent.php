<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Event;

use Contao\ModuleModel;
use Hofff\Contao\Navigation\Items\PageItems;

final class TreeEvent extends Event
{
    private PageItems $items;

    public function __construct(ModuleModel $moduleModel, PageItems $items)
    {
        parent::__construct($moduleModel);

        $this->items = $items;
    }

    public function items(): PageItems
    {
        return $this->items;
    }
}
