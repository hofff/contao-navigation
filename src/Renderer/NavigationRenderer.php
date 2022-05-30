<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Renderer;

use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Hofff\Contao\Navigation\Event\ItemEvent;
use Hofff\Contao\Navigation\Event\MenuEvent;
use Hofff\Contao\Navigation\Event\TreeEvent;
use Hofff\Contao\Navigation\Items\PageItems;
use Hofff\Contao\Navigation\QueryBuilder\RedirectPageQueryBuilder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function array_merge;
use function array_shift;
use function count;
use function ltrim;
use function preg_replace;
use function str_replace;
use function strncasecmp;
use function strncmp;
use function trim;

use const PHP_INT_MAX;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
final class NavigationRenderer
{
    private RedirectPageQueryBuilder $redirectQueryBuilder;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RedirectPageQueryBuilder $redirectQueryBuilder
    ) {
        $this->redirectQueryBuilder = $redirectQueryBuilder;
        $this->eventDispatcher      = $eventDispatcher;
    }

    /**
     * Renders the navigation of the given IDs into the navigation template.
     * Adds CSS classes "first" and "last" to the appropriate navigation item arrays.
     * If the given array is empty, the empty string is returned.
     *
     * @param list<int|string> $itemIds      The navigation items arrays
     * @param list<int>        $stopLimit    (optional, defaults to PHP_INT_MAX) The soft limit of depth.
     * @param int              $intHard      (optional, defaults to PHP_INT_MAX) The hard limit of depth.
     * @param int              $currentLevel (optional, defaults to 1) The current level of this navigation layer
     *
     * @return string The parsed navigation template, could be empty string.
     */
    public function render(
        ModuleModel $moduleModel,
        PageItems $items,
        array $itemIds,
        array $stopLimit = [PHP_INT_MAX],
        int $intHard = PHP_INT_MAX,
        int $currentLevel = 1,
        ?int $activeId = null
    ): string {
        if ($stopLimit === []) {
            $stopLimit = [PHP_INT_MAX];
        }

        $this->compileTree($moduleModel, $items);
        $this->dispatchTreeEvent($moduleModel, $items);

        $itemIds  = $this->dispatchMenuEvent($moduleModel, $itemIds);
        $firstIds = $this->getFirstNavigationLevel($moduleModel, $items, $itemIds);

        if ($moduleModel->hofff_navigation_hideSingleLevel) {
            foreach ($firstIds as $id) {
                if ($items->subItems[$id]) {
                    $hasMultipleLevels = true;
                    break;
                }
            }

            if (! $hasMultipleLevels) {
                return '';
            }
        }

        if ($stopLimit[0] === 0) {
            array_shift($stopLimit); // special case renderNavigationTree cannot handle
        }

        return trim($this->renderTree($moduleModel, $items, $firstIds, $stopLimit, $intHard, $currentLevel, $activeId));
    }

    /**
     * @param list<string|int> $itemIds
     * @param list<int>        $stopLimit
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function renderTree(
        ModuleModel $moduleModel,
        PageItems $items,
        array $itemIds,
        array $stopLimit = [PHP_INT_MAX],
        int $intHard = PHP_INT_MAX,
        int $currentLevel = 1,
        ?int $activeId = null
    ): string {
        if (! $itemIds) {
            return '';
        }

        $intStop        = $currentLevel >= $stopLimit[0] ? array_shift($stopLimit) : $stopLimit[0];
        $renderedItems  = [];
        $containsActive = false;

        foreach ($itemIds as $itemId) {
            if (! isset($items->items[$itemId])) {
                continue;
            }

            $item = $items->items[$itemId];

            if ($itemId === $activeId) {
                $containsActive = true;

                if ($item['href'] === Environment::get('request')) {
                    $item['isActive']  = true; // nothing else (active class is set in template)
                    $item['isInTrail'] = false;
                } else {
                    $item['isActive']  = false; // nothing else (active class is set in template)
                    $item['isInTrail'] = true;
                }
            } else { // do not flatten if/else
                if ($item['tid'] === $activeId) {
                    if ($item['href'] === Environment::get('request')) {
                        $item['isActive']  = true; // nothing else (active class is set in template)
                        $item['isInTrail'] = false;
                    } else {
                        $item['isActive']  = false; // nothing else (active class is set in template)
                        $item['isInTrail'] = true;
                    }
                }
            }

            if ($item['isInTrail']) {
                $item['class'] .= ' trail';
            }

            if (! isset($items->subItems[$itemId])) {
                $item['class'] .= ' leaf';
            } elseif ($currentLevel >= $intHard) {
                // we are at hard level, never draw submenu
                $item['class'] .= ' submenu leaf';
            } elseif (
                $currentLevel >= $intStop
                && ! $item['isInTrail'] && $itemId !== $activeId
                && $item['tid'] !== $activeId
            ) {
                // we are at stop level and not trail and not active, never draw submenu
                $item['class'] .= ' submenu leaf';
            } elseif ($items->subItems[$itemId]) {
                $item['class']   .= ' submenu inner';
                $item['subitems'] = $this->renderTree(
                    $moduleModel,
                    $items,
                    $items->subItems[$itemId] ?? [],
                    $stopLimit,
                    $intHard,
                    $currentLevel + 1
                );
            } else { // should never be reached, if no hooks are used
                $item['class'] .= ' leaf';
            }

            $renderedItems[] = $item;
        }

        if ($containsActive) {
            foreach ($renderedItems as &$item) {
                if ($item['isActive']) {
                    continue;
                }

                $item['class'] .= ' sibling';
            }

            unset($item);
        }

        $renderedItems[0]['class']                         .= ' first';
        $renderedItems[count($renderedItems) - 1]['class'] .= ' last';

        foreach ($renderedItems as &$item) {
            $item['class'] = ltrim($item['class']);
        }

        unset($item);

        $objTemplate = new FrontendTemplate($moduleModel->navigationTpl ?: 'nav_default');
        $objTemplate->setData([
            'module' => $moduleModel->row(),
            'level'  => 'level_' . $currentLevel,
            'items'  => $renderedItems,
            'type'   => static::class,
        ]);

        return $objTemplate->parse();
    }

    private function compileTree(ModuleModel $moduleModel, PageItems $items): void
    {
        $blnForwardResolution = ! $moduleModel->hofff_navigation_noForwardResolution;
        foreach ($items->items as $itemId => $item) {
            if ($item === []) {
                continue;
            }

            $items->items[$itemId] = $this->compileNavigationItem(
                $moduleModel,
                $items,
                $items->items[$itemId],
                $blnForwardResolution
            );
        }
    }

    /**
     * Compiles a navigation item array from a page dataset with the given subnavi
     *
     * @param array<string,mixed> $page The page dataset as an array
     *
     * @return array<string,mixed> The compiled navigation item array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function compileNavigationItem(
        ModuleModel $moduleModel,
        PageItems $items,
        array $page,
        bool $forwardResolution = true
    ): array {
        // fallback for dataset field collisions
        $page['_title']       = $page['title'];
        $page['_pageTitle']   = $page['pageTitle'];
        $page['_target']      = $page['target'];
        $page['_description'] = $page['description'];

        $page['link']        = $page['_title'];
        $page['class']       = $page['cssClass'] . ' ' . $page['type'];
        $page['title']       = StringUtil::specialchars($page['_title'], true);
        $page['pageTitle']   = StringUtil::specialchars($page['_pageTitle'], true);
        $page['target']      = ''; // overwrite DB value
        $page['nofollow']    = strncmp($page['robots'], 'noindex', 7) === 0;
        $page['description'] = str_replace(["\n", "\r"], [' ', ''], $page['_description']);
        $page['isInTrail']   = $items->isInTrail((int) $page['id']);

        switch ($page['type']) {
            case 'forward':
                if ($forwardResolution) {
                    $redirectPage = $this->getRedirectPage($page);
                    if (! $redirectPage) {
                        $page['href'] = $this->generatePageUrl($page);
                    } elseif ($redirectPage['type'] === 'redirect') {
                        $page['href']   = $this->encodeEmailURL($redirectPage['url']);
                        $page['target'] = $redirectPage['target'] ? 'target="_blank"' : '';
                    } else {
                        $page['tid']  = $redirectPage['id'];
                        $page['href'] = $this->generatePageUrl($redirectPage);
                    }
                } else {
                    $page['tid']  = $page['jumpTo'];
                    $page['href'] = $this->generatePageUrl($page);
                }

                break;

            case 'redirect':
                $page['href']   = $this->encodeEmailURL($page['url']);
                $page['target'] = $page['_target'] ? 'target="_blank"' : '';
                break;

            case 'root':
                if (
                    ! $page['dns']
                    || preg_replace('/^www\./', '', $page['dns']) === preg_replace(
                        '/^www\./',
                        '',
                        Environment::get('httpHost')
                    )
                ) {
                    $page['href'] = Environment::get('base');
                    // we only break on root pages; pages in different roots should be handled by DomainLink extension
                    break;
                }
            // do not break
            default:
            case 'regular':
            case 'error_401':
            case 'error_403':
            case 'error_404':
                $page['href'] = $this->generatePageUrl($page);
                break;
        }

        return $this->dispatchItemEvent($moduleModel, $page);
    }

    /** @param array<string,mixed> $page */
    private function generatePageUrl(array $page): ?string
    {
        $pageModel = PageModel::findByPk($page['id']);
        if ($pageModel) {
            return $pageModel->getFrontendUrl();
        }

        return null;
    }

    /**
     * Utility method of compileNavigationItem.
     *
     * If the given URL starts with "mailto:", the E-Mail is encoded,
     * otherwise nothing is done.
     *
     * @param string $href The URL to check and possibly encode
     *
     * @return string The modified URL
     */
    private function encodeEmailURL(string $href): string
    {
        if (strncasecmp($href, 'mailto:', 7) !== 0) {
            return $href;
        }

        return StringUtil::encodeEmail($href);
    }

    /**
     * @param array<string,mixed> $page
     *
     * @return array<string,mixed>
     */
    private function dispatchItemEvent(ModuleModel $moduleModel, array $page): array
    {
        if (! $moduleModel->hofff_navigation_disableHooks) {
            return $page;
        }

        $event = new ItemEvent($moduleModel, $page);
        $this->eventDispatcher->dispatch($event);

        return $event->item();
    }

    /**
     * Executes the tree hook, to dynamically add navigations items to the tree
     * the navigation is rendered from.
     *
     * The callback receives the following parameters:
     * $this - This navigation module instance
     */
    private function dispatchTreeEvent(ModuleModel $moduleModel, PageItems $items): void
    {
        if (! $moduleModel->hofff_navigation_disableHooks) {
            return;
        }

        $this->eventDispatcher->dispatch(new TreeEvent($moduleModel, $items));
    }

    /**
     * Executes the navigation hook.
     * The callback receives the following parameters:
     * $this - This navigation module instance
     * $arrRootIDs - The IDs of the first navigation level
     *
     * And should return a new root array or null
     *
     * @param list<int|string> $rootIds The root pages before hook execution
     *
     * @return list<int|string> $arrRootIDs The root pages after hook execution
     */
    private function dispatchMenuEvent(ModuleModel $moduleModel, array $rootIds): array
    {
        if ($moduleModel->hofff_navigation_disableHooks) {
            return $rootIds;
        }

        $event = new MenuEvent($moduleModel, $rootIds);
        $this->eventDispatcher->dispatch($event);

        return $event->rootIds();
    }

    /**
     * @param list<int|string> $rootIds
     *
     * @return array<int|string>
     */
    private function getFirstNavigationLevel(ModuleModel $moduleModel, PageItems $items, array $rootIds): array
    {
        if ($moduleModel->hofff_navigation_includeStart) {
            return $rootIds;
        }

        // if we do not want to show the root level
        $arrFirstIDs = [];
        foreach ($rootIds as $varRootID) {
            if (! isset($items->subItems[$varRootID])) {
                continue;
            }

            $arrFirstIDs[] = $items->subItems[$varRootID];
        }

        return array_merge(...$arrFirstIDs);
    }

    /**
     * @param array<string,mixed> $arrPage
     *
     * @return array<string,mixed>
     */
    private function getRedirectPage(array $arrPage): array
    {
        if (! $arrPage['jumpTo']) {
            $query = $this->redirectQueryBuilder->createFallbackQuery((int) $arrPage['id']);

            return $query->execute()->fetchAssociative();
        }

        $intFallbackSearchID = $arrPage['id'];
        $intJumpToID         = $arrPage['jumpTo'];
        do {
            $query = $this->redirectQueryBuilder->createJumpToQuery((int) $intJumpToID);
            $query->setParameter('id', $intJumpToID);
            $result = $query->execute();
            $next   = $result->fetchAssociative();

            if (! $result->rowCount()) {
                $query = $this->redirectQueryBuilder->createFallbackQuery((int) $intFallbackSearchID);
                $next  = $query->execute()->fetchAssociative();
                break;
            }

            $intFallbackSearchID = $intJumpToID;
            $intJumpToID         = $next['jumpTo'] ?? 0;
        } while ($next['type'] === 'forward');

        return $next;
    }
}
