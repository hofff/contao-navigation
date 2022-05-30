<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Items;

use Contao\PageModel;

use function array_flip;
use function array_key_exists;
use function array_map;
use function array_merge;

final class PageItems
{
    /** @psalm-readonly */
    public PageModel $currentPage;

    /** @var array<int,bool> */
    public array $roots = [];

    /** @var array<int,array<string,mixed>> */
    public array $items = [];

    /** @var array<int,list<int>> */
    public array $subItems = [];

    /**
     * Page trail as flipped array
     *
     * @var array<int,int>
     */
    private array $trail = [];

    public function __construct(PageModel $currentPage)
    {
        $this->currentPage = $currentPage;
        $this->trail       = array_flip(array_map('intval', $currentPage->trail));
    }

    public function isInTrail(int $pageId): bool
    {
        return array_key_exists($pageId, $this->trail);
    }

    /**
     * @param list<int> $rootIds
     *
     * @return list<int>
     */
    public function getFirstNavigationLevel(array $rootIds): array
    {
        // if we do not want to show the root level
        $firstIds = [];
        foreach ($rootIds as $rootId) {
            if (! isset($this->subItems[$rootId])) {
                continue;
            }

            $firstIds[] = $this->subItems[$rootId];
        }

        return array_merge(...$firstIds);
    }
}
