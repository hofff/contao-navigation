<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Event;

use Contao\ModuleModel;
use Symfony\Contracts\EventDispatcher\Event as BaseEvent;

abstract class Event extends BaseEvent
{
    private ModuleModel $moduleModel;

    public function __construct(ModuleModel $moduleModel)
    {
        $this->moduleModel = $moduleModel;
    }

    public function moduleModel(): ModuleModel
    {
        return $this->moduleModel;
    }
}
