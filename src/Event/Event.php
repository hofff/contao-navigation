<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Event;

use Contao\ModuleModel;
use Symfony\Contracts\EventDispatcher\Event as BaseEvent;

abstract class Event extends BaseEvent
{
    public function __construct(private readonly ModuleModel $moduleModel)
    {
    }

    public function moduleModel(): ModuleModel
    {
        return $this->moduleModel;
    }
}
