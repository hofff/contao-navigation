<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Renderer;

use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\Result;
use Hofff\Contao\Navigation\Event\ItemEvent;
use Hofff\Contao\Navigation\Event\MenuEvent;
use Hofff\Contao\Navigation\Event\TreeEvent;
use Hofff\Contao\Navigation\Items\PageItems;
use Hofff\Contao\Navigation\QueryBuilder\RedirectPageQueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    /** @psalm-suppress PropertyNotSetInConstructor - Will be initialized in the only public method load() */
    private PageItems $items;

    /** @psalm-suppress PropertyNotSetInConstructor - Will be initialized in the only public method load() */
    private ModuleModel $moduleModel;

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
     * @param list<int> $itemIds      The navigation items arrays
     * @param list<int> $stopLimit    (optional, defaults to PHP_INT_MAX) The soft limit of depth.
     * @param int       $hardLevel    (optional, defaults to PHP_INT_MAX) The hard limit of depth.
     * @param int       $currentLevel (optional, defaults to 1) The current level of this navigation layer
     *
     * @return string The parsed navigation template, could be empty string.
     */
    public function render(
        ModuleModel $moduleModel,
        PageItems $items,
        array $itemIds,
        array $stopLimit = [PHP_INT_MAX],
        int $hardLevel = PHP_INT_MAX,
        ?int $activeId = null,
        int $currentLevel = 1
    ): string {
        $this->moduleModel = $moduleModel;
        $this->items       = $items;

        if ($stopLimit === []) {
            $stopLimit = [PHP_INT_MAX];
        }

        $this->compileTree();
        $this->dispatchEvent(new TreeEvent($moduleModel, $items));

        $event = new MenuEvent($moduleModel, $itemIds);
        $this->dispatchEvent($event);
        $itemIds  = $event->rootIds();
        $firstIds = $this->moduleModel->hofff_navigation_includeStart
            ? $itemIds
            : $this->items->getFirstNavigationLevel($itemIds);

        if ($moduleModel->hofff_navigation_hideSingleLevel) {
            $hasMultipleLevels = false;

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

        return trim(
            $this->renderTree($firstIds, $stopLimit, $hardLevel, $currentLevel, $activeId)
        );
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
        array $itemIds,
        array $stopLimit = [PHP_INT_MAX],
        int $hardLevel = PHP_INT_MAX,
        int $currentLevel = 1,
        ?int $activeId = null
    ): string {
        if (! $itemIds) {
            return '';
        }

        $stopLevel      = $currentLevel >= $stopLimit[0] ? array_shift($stopLimit) : $stopLimit[0];
        $renderedItems  = [];
        $containsActive = false;

        foreach ($itemIds as $itemId) {
            if (! isset($this->items->items[$itemId])) {
                continue;
            }

            $item = $this->items->items[$itemId];

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

            if (! isset($this->items->subItems[$itemId])) {
                $item['class'] .= ' leaf';
            } elseif ($currentLevel >= $hardLevel) {
                // we are at hard level, never draw submenu
                $item['class'] .= ' submenu leaf';
            } elseif (
                $currentLevel >= $stopLevel
                && ! $item['isInTrail'] && $itemId !== $activeId
                && $item['tid'] !== $activeId
            ) {
                // we are at stop level and not trail and not active, never draw submenu
                $item['class'] .= ' submenu leaf';
            } elseif ($this->items->subItems[$itemId]) {
                $item['class']   .= ' submenu inner';
                $item['subitems'] = $this->renderTree(
                    $this->items->subItems[$itemId] ?? [],
                    $stopLimit,
                    $hardLevel,
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

        $objTemplate = new FrontendTemplate($this->moduleModel->navigationTpl ?: 'nav_default');
        $objTemplate->setData([
            'module' => $this->moduleModel->row(),
            'level'  => 'level_' . $currentLevel,
            'items'  => $renderedItems,
            'type'   => self::class,
        ]);

        return $objTemplate->parse();
    }

    private function compileTree(): void
    {
        $forwardResolution = ! $this->moduleModel->hofff_navigation_noForwardResolution;
        foreach ($this->items->items as $itemId => $item) {
            if ($item === []) {
                continue;
            }

            $this->items->items[$itemId] = $this->compileNavigationItem(
                $this->items->items[$itemId],
                $forwardResolution
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
    private function compileNavigationItem(array $page, bool $forwardResolution = true): array
    {
        // fallback for dataset field collisions
        $page['_title']       = $page['title'];
        $page['_pageTitle']   = $page['pageTitle'];
        $page['_target']      = $page['target'];
        $page['_description'] = $page['description'] ?? '';

        $page['link']        = $page['_title'];
        $page['class']       = $page['cssClass'] . ' ' . $page['type'];
        $page['title']       = StringUtil::specialchars($page['_title'], true);
        $page['pageTitle']   = StringUtil::specialchars($page['_pageTitle'], true);
        $page['target']      = ''; // overwrite DB value
        $page['nofollow']    = strncmp($page['robots'], 'noindex', 7) === 0;
        $page['description'] = str_replace(["\n", "\r"], [' ', ''], $page['_description']);
        $page['isInTrail']   = $this->items->isInTrail((int) $page['id']);

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

        $event = new ItemEvent($this->moduleModel, $page);
        $this->dispatchEvent($event);

        return $event->item();
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
    private function getRedirectPage(array $page): array
    {
        if (! $page['jumpTo']) {
            $query = $this->redirectQueryBuilder->createFallbackQuery((int) $page['id']);
            /** @psalm-var Result $result */
            $result =  $query->execute();

            return $result->fetchAssociative() ?: [];
        }

        $fallbackSearchId = $page['id'];
        $jumpToId         = $page['jumpTo'];
        do {
            $query = $this->redirectQueryBuilder->createJumpToQuery((int) $jumpToId);
            $query->setParameter('id', $jumpToId);
            /** @psalm-var Result $result */
            $result = $query->execute();
            $next   = $result->fetchAssociative() ?: [];

            if (! $result->rowCount()) {
                $query = $this->redirectQueryBuilder->createFallbackQuery((int) $fallbackSearchId);
                /** @psalm-var Result $result */
                $result = $query->execute();
                $next   = $result->fetchAssociative() ?: [];
                break;
            }

            $fallbackSearchId = $jumpToId;
            $jumpToId         = $next['jumpTo'] ?? 0;
        } while ($next['type'] === 'forward');

        return $next;
    }

    private function dispatchEvent(Event $event): void
    {
        if (! $this->moduleModel->hofff_navigation_disableHooks) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
