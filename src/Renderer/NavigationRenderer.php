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
use function get_class;
use function ltrim;
use function preg_replace;
use function str_replace;
use function strncasecmp;
use function strncmp;
use function trim;

use const LINK_NEW_WINDOW;
use const PHP_INT_MAX;

final class NavigationRenderer
{
    private RedirectPageQueryBuilder $redirectPageQueryBuilder;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RedirectPageQueryBuilder $redirectPageQueryBuilder
    ) {
        $this->redirectPageQueryBuilder = $redirectPageQueryBuilder;
        $this->eventDispatcher          = $eventDispatcher;
    }

    /**
     * Renders the navigation of the given IDs into the navigation template.
     * Adds CSS classes "first" and "last" to the appropriate navigation item arrays.
     * If the given array is empty, the empty string is returned.
     *
     * @param array   $arrIDs       The navigation items arrays
     * @param array   $stopLimit    (optional, defaults to PHP_INT_MAX) The soft limit of depth.
     * @param integer $intHard      (optional, defaults to PHP_INT_MAX) The hard limit of depth.
     * @param integer $currentLevel (optional, defaults to 1) The current level of this navigation layer
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

        $itemIds = $this->dispatchMenuEvent($moduleModel, $itemIds);
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

        if ($stopLimit[0] == 0) {
            array_shift($stopLimit); // special case renderNavigationTree cannot handle
        }

        return trim($this->renderTree($moduleModel, $items, $firstIds, $stopLimit, $intHard, $currentLevel, $activeId));
    }

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

            if ($itemId == $activeId) {
                $containsActive = true;

                if ($item['href'] === Environment::get('request')) {
                    $item['isActive'] = true; // nothing else (active class is set in template)
                    $item['isInTrail']  = false;
                } else {
                    $item['isActive'] = false; // nothing else (active class is set in template)
                    $item['isInTrail']  = true;
                }
            } else { // do not flatten if/else
                if ($item['tid'] == $activeId) {
                    if ($item['href'] === Environment::get('request')) {
                        $item['isActive'] = true; // nothing else (active class is set in template)
                        $item['isInTrail']  = false;
                    } else {
                        $item['isActive'] = false; // nothing else (active class is set in template)
                        $item['isInTrail']  = true;
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
            } elseif ($currentLevel >= $intStop && ! $item['isInTrail'] && $itemId !== $activeId && $item['tid'] != $activeId) {
                // we are at stop level and not trail and not active, never draw submenu
                $item['class'] .= ' submenu leaf';
            } elseif ($items->subItems[$itemId]) {
                $item['class']    .= ' submenu inner';
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
                if (! $item['isActive']) {
                    $item['class'] .= ' sibling';
                }
            }
            unset($item);
        }

        $renderedItems[0]['class']                    .= ' first';
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
            'type'   => get_class($this),
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
     * @param array $page The page dataset as an array
     *
     * @return array The compiled navigation item array
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
                        $page['target'] = $redirectPage['target'] ? LINK_NEW_WINDOW : '';
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
                $page['target'] = $page['_target'] ? LINK_NEW_WINDOW : '';
                break;

            case 'root':
                if (! $page['dns']
                    || preg_replace('/^www\./', '', $page['dns']) == preg_replace(
                        '/^www\./',
                        '',
                        Environment::get('httpHost')
                    )) {
                    $page['href'] = Environment::get('base');
                    break; // we only break on root pages; pages in different roots should be handled by DomainLink extension
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

    private function generatePageUrl(array $arrPage): ?string
    {
        $pageModel = PageModel::findByPk($arrPage['id']);
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
     *
     * @return void
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
     * @param array $arrRootIDs The root pages before hook execution
     *
     * @return array $arrRootIDs The root pages after hook execution
     */
    private  function dispatchMenuEvent(ModuleModel $moduleModel, array $arrRootIDs): array
    {
        if ($moduleModel->hofff_navigation_disableHooks) {
            return $arrRootIDs;
        }

        $event = new MenuEvent($moduleModel, $arrRootIDs);
        $this->eventDispatcher->dispatch($event);

        return $event->rootIds();
    }

    private function getFirstNavigationLevel(ModuleModel $moduleModel, PageItems $items, array $arrRootIDs): array
    {
        if ($moduleModel->hofff_navigation_includeStart) {
            return $arrRootIDs;
        }

        // if we do not want to show the root level
        $arrFirstIDs = [];
        foreach ($arrRootIDs as $varRootID) {
            if (isset($items->subItems[$varRootID])) {
                $arrFirstIDs[] = $items->subItems[$varRootID];
            }
        }

        return array_merge(... $arrFirstIDs);
    }

    private function getRedirectPage(array $arrPage): array
    {
        if (! $arrPage['jumpTo']) {
            $query = $this->redirectPageQueryBuilder->createFallbackQuery((int) $arrPage['id']);

            return $query->execute()->fetchAssociative();
        }

        $intFallbackSearchID = $arrPage['id'];
        $intJumpToID         = $arrPage['jumpTo'];
        do {
            $query = $this->redirectPageQueryBuilder->createJumpToQuery((int) $intJumpToID);
            $query->setParameter('id', $intJumpToID);
            $result = $query->execute();
            $next   = $result->fetchAssociative();

            if (! $result->rowCount()) {
                $query = $this->redirectPageQueryBuilder->createFallbackQuery((int) $intFallbackSearchID);
                $next  = $query->execute()->fetchAssociative();
                break;
            }

            $intFallbackSearchID = $intJumpToID;
            $intJumpToID         = $next['jumpTo'] ?? 0;
        } while ($next['type'] === 'forward');

        return $next;
    }
}
