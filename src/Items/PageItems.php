<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Items;

use function array_key_exists;

final class PageItems
{
    /** @var array<int,bool> */
    public array $roots = [];

    /** @var array<int,array<string,mixed>> */
    public array $items = [];

    /** @var array<int,list<int>> */
    public array $subItems = [];

    /** @var list<int> */
    public array $trail = [];

    public function isInTrail(int $pageId): bool
    {
        return array_key_exists($pageId, $this->trail);
    }
}
