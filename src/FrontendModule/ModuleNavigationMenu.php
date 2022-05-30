<?php

namespace Hofff\Contao\Navigation\FrontendModule;

use Contao\BackendTemplate;
use Contao\Module;
use Contao\System;
use Hofff\Contao\Navigation\Items\PageItemsLoader;
use Hofff\Contao\Navigation\Renderer\NavigationRenderer;

use function array_flip;
use function array_keys;
use function array_merge;
use function deserialize;
use function strlen;

use const TL_MODE;

/**
 * Navigation modules
 *
 * Navigation item array layout:
 * Before rendering:
 * id            => the ID of the current item (optional)
 * isInTrail        => whether this item is in the trail path
 * class        => CSS classes
 * title        => page name with Insert-Tags stripped and XML specialchars replaced by their entities
 * pageTitle    => page title with Insert-Tags stripped and XML specialchars replaced by their entities
 * link            => page name (with Insert-Tags and XML specialchars NOT replaced; as stored in the db)
 * href            => URL of target page
 * nofollow        => true, if nofollow should be set on rel attribute
 * target        => either ' onclick="window.open(this.href); return false;"' or empty string
 * description    => page description with line breaks (\r and \n) replaced by whitespaces
 *
 * Calculated while rendering:
 * subitems        => subnavigation as HTML string or empty string (rendered if subpages & items setup correctly)
 * isActive        => whether this item is the current active navigation item
 *
 * Following CSS classes are calculated while rendering: level_x, trail, sibling, submenu, first, last
 *
 * Additionally all page dataset values from the database are available unter their field name,
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
 */
final class ModuleNavigationMenu extends Module
{
    protected $strTemplate = 'mod_hofff_navigation_menu';

    protected string $strNavigation = '';

    protected $arrGroups; // set of groups of the current user

    public $varActiveID; // the id of the active page

    private PageItemsLoader $loader;

    private NavigationRenderer $renderer;

    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        $this->loader   = System::getContainer()->get(PageItemsLoader::class);
        $this->renderer = System::getContainer()->get(NavigationRenderer::class);

        if (TL_MODE === 'BE') {
            return;
        }

        $this->import('Database');

        $this->varActiveID = $this->hofff_navigation_isSitemap || $this->Input->get('articles')
            ? null
            : (int) $GLOBALS['objPage']->id;

        if (FE_USER_LOGGED_IN) {
            $this->import('FrontendUser', 'User');
            $this->User->groups && $this->arrGroups = $this->User->groups;
        }

        if (! strlen($this->navigationTpl)) {
            $this->navigationTpl = 'nav_default';
        }
    }

    public function generate(): string
    {
        if (TL_MODE === 'BE') {
            return $this->generateBE('NAVIGATION MENU');
        }

        $stopLevels = $this->getStopLevels();
        $hardLevel  = $this->getHardLevel();

        $items = $this->loader->load($this->objModel, $stopLevels, $hardLevel, $this->varActiveID);

        $this->strNavigation = $this->renderer->render(
            $this->objModel,
            $items,
            array_keys($items->roots),
            $stopLevels,
            $hardLevel,
            $this->varActiveID
        );

        return $this->strNavigation ? parent::generate() : '';
    }

    public function getStopLevels(): array
    {
        if (! $this->hofff_navigation_defineStop) {
            return [PHP_INT_MAX];
        }

        $minLevel  = -1;
        $stopLevel = [];

        foreach (array_map('intval', explode(',', $this->hofff_navigation_stop)) as $level) {
            if ($level > $minLevel) {
                $stopLevel[] = $minLevel = $level;
            }
        }

        return $stopLevel ?: [PHP_INT_MAX];
    }

    public function getHardLevel()
    {
        return $this->hofff_navigation_defineHard ? $this->hofff_navigation_hard : PHP_INT_MAX;
    }

    protected function compile(): void
    {
        $this->Template->request = $this->getIndexFreeRequest(true);
        $this->Template->skipId  = 'skipNavigation' . $this->id;
        $this->Template->items   = $this->strNavigation;
        $this->hofff_navigation_addLegacyCss && $this->Template->legacyClass = ' mod_navigation';
    }

    /**
     * A helper method to generate BE wildcard.
     *
     * @param string $strBEType (optional, defaults to "NAVIGATION") The type to be displayed in the wildcard
     *
     * @return string The wildcard HTML string
     */
    protected function generateBE($strBEType = 'NAVIGATION'): string
    {
        $objTemplate = new BackendTemplate('be_wildcard');

        $objTemplate->wildcard = '### ' . $strBEType . ' ###';
        $objTemplate->title    = $this->headline;
        $objTemplate->id       = $this->id;
        $objTemplate->link     = $this->name;
        $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

        return $objTemplate->parse();
    }
}
