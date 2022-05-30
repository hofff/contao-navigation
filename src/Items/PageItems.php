<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Items;

use function array_key_exists;

final class PageItems
{
    /** @var array<int,bool> */
    public array $roots = [];

    /** @var array<int,string> */
    public array $items = [];

    /** @var array<int,list<int>> */
    public array $subItems = [];

    /** @var array<int,string> */
    public array $trail = [];

    public function isInTrail(int $pageId): bool
    {
        return array_key_exists($pageId, $this->trail);
    }
}
