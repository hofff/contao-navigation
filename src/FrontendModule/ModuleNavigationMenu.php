<?php

namespace Hofff\Contao\Navigation\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Environment;
use Contao\Input;
use Contao\ModuleModel;
use Contao\Template;
use Hofff\Contao\Navigation\Items\PageItemsLoader;
use Hofff\Contao\Navigation\Renderer\NavigationRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_keys;


/**
 * Navigation modules
 *
 * Navigation item array layout:
 * Before rendering:
 * id            => the ID of the current item (optional)
 * isInTrail     => whether this item is in the trail path
 * class         => CSS classes
 * title         => page name with Insert-Tags stripped and XML specialchars replaced by their entities
 * pageTitle     => page title with Insert-Tags stripped and XML specialchars replaced by their entities
 * link          => page name (with Insert-Tags and XML specialchars NOT replaced; as stored in the db)
 * href          => URL of target page
 * nofollow      => true, if nofollow should be set on rel attribute
 * target        => either ' onclick="window.open(this.href); return false;"' or empty string
 * description   => page description with line breaks (\r and \n) replaced by whitespaces
 *
 * Calculated while rendering:
 * subitems      => subnavigation as HTML string or empty string (rendered if subpages & items setup correctly)
 * isActive      => whether this item is the current active navigation item
 *
 * Following CSS classes are calculated while rendering: level_x, trail, sibling, submenu, first, last
 *
 * Additionally, all page dataset values from the database are available unter their field name,
 * if the field name does not collide with the listed keys.
 *
 * For the collisions of the Contao core page dataset fields the following keys are available:
 * _type
 * _title
 * _pageTitle
 * _target
 * _description
 *
 * @author Oliver Hoff <oliver@hofff.com>
 *
 * @FrontendModule("hofff_navigation_menu", category="navigation")
 */
final class ModuleNavigationMenu extends AbstractFrontendModuleController
{
    private PageItemsLoader $loader;

    private NavigationRenderer $renderer;

    public function __construct(PageItemsLoader $loader, NavigationRenderer $renderer)
    {
        $this->loader   = $loader;
        $this->renderer = $renderer;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $stopLevels = $this->getStopLevels($model);
        $hardLevel  = $this->getHardLevel($model);
        $activeId   = $model->hofff_navigation_isSitemap || Input::get('articles')
            ? null
            : (int) $GLOBALS['objPage']->id;

        $items = $this->loader->load($model, $stopLevels, $hardLevel, $activeId);

        $navigation = $this->renderer->render(
            $model,
            $items,
            array_keys($items->roots),
            $stopLevels,
            $hardLevel,
            $activeId
        );

        if ($navigation === '') {
            return new Response();
        }

        $template->items       = $navigation;
        $template->request     = Environment::get('indexFreeRequest');
        $template->skipId      = 'skipNavigation' . $model->id;
        $template->items       = $navigation;
        $template->legacyClass = $model->hofff_navigation_addLegacyCss ? ' mod_navigation' : '';

        return $template->getResponse();
    }

    public function getStopLevels(ModuleModel $model): array
    {
        if (! $model->hofff_navigation_defineStop) {
            return [PHP_INT_MAX];
        }

        $minLevel  = -1;
        $stopLevel = [];

        foreach (array_map('intval', explode(',', $model->hofff_navigation_stop)) as $level) {
            if ($level > $minLevel) {
                $stopLevel[] = $minLevel = $level;
            }
        }

        return $stopLevel ?: [PHP_INT_MAX];
    }

    public function getHardLevel(ModuleModel $model): int
    {
        return $model->hofff_navigation_defineHard ? (int) $model->hofff_navigation_hard : PHP_INT_MAX;
    }
}
