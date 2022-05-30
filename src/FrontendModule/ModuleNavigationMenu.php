<?php

namespace Hofff\Contao\Navigation\FrontendModule;

use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\Navigation\Items\PageItemsLoader;
use Hofff\Contao\Navigation\Renderer\NavigationRenderer;

use function assert;

final class ModuleNavigationMenu extends AbstractModuleNavigation
{
    protected $strTemplate = 'mod_hofff_navigation_menu';

    protected $strNavigation;

    public function generate()
    {
        if (TL_MODE === 'BE') {
            return $this->generateBE('NAVIGATION MENU');
        }

        $stopLevels = $this->getStopLevels();
        $hardLevel  = $this->getHardLevel();
        $arrRootIDs = $this->calculateRootIDs($stopLevels);

        $loader = System::getContainer()->get(PageItemsLoader::class);
        assert($loader instanceof PageItemsLoader);

        $renderer = System::getContainer()->get(NavigationRenderer::class);
        assert($renderer instanceof NavigationRenderer);

        $items = $loader->load($this->objModel, $arrRootIDs, $this->arrFields, $stopLevels, $hardLevel);

        $this->strNavigation = $renderer->render($this->objModel, $items, $arrRootIDs, $stopLevels, $hardLevel, $this->varActiveID);

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

    protected function compile()
    {
        $this->Template->request = $this->getIndexFreeRequest(true);
        $this->Template->skipId  = 'skipNavigation' . $this->id;
        $this->Template->items   = $this->strNavigation;
        $this->hofff_navigation_addLegacyCss && $this->Template->legacyClass = ' mod_navigation';
    }

    protected function calculateRootIDs($arrStop = PHP_INT_MAX)
    {
        $arrRootIDs = $this->hofff_navigation_defineRoots
            ? $this->getRootIds()
            : [$GLOBALS['objPage']->rootId];

        $this->hofff_navigation_currentAsRoot && array_unshift($arrRootIDs, $GLOBALS['objPage']->id);

        $arrConditions = [
            $this->getQueryPartHidden(
                ! $this->hofff_navigation_respectHidden,
                $this->hofff_navigation_isSitemap
            ),
        ];
        $this->hofff_navigation_respectGuests && $arrConditions[] = $this->getQueryPartGuests();
        $this->hofff_navigation_respectPublish && $arrConditions[] = $this->getQueryPartPublish();
        $strConditions = implode(' AND ', array_filter($arrConditions, 'strlen'));

        if ($this->hofff_navigation_includeStart) {
            $arrStartConditions = [
                $this->getQueryPartHidden(
                    $this->hofff_navigation_showHiddenStart,
                    $this->hofff_navigation_isSitemap
                ),
                $this->getQueryPartPublish(),
                $this->getQueryPartErrorPages($this->hofff_navigation_showErrorPages),
            ];
            ! $this->hofff_navigation_showGuests && $arrStartConditions[] = $this->getQueryPartGuests();
            $strStartConditions = implode(' AND ', array_filter($arrStartConditions, 'strlen'));
        } else {
            $strStartConditions = $strConditions;
        }

        if ($this->hofff_navigation_start > 0) {
            $arrRootIDs = $this->filterPages($arrRootIDs, $strConditions);
            for ($i = 1, $n = $this->hofff_navigation_start; $i < $n; $i++) {
                $arrRootIDs = $this->getNextLevel($arrRootIDs, $strConditions);
            }
            $arrRootIDs = $this->getNextLevel($arrRootIDs, $strStartConditions);
        } elseif ($this->hofff_navigation_start < 0) {
            for ($i = 0, $n = -$this->hofff_navigation_start; $i < $n; $i++) {
                $arrRootIDs = $this->getPrevLevel($arrRootIDs);
            }
            $arrRootIDs = $this->filterPages($arrRootIDs, $strStartConditions);
        } else {
            $arrRootIDs = $this->filterPages($arrRootIDs, $strStartConditions);
        }

        $arrStop = (array) $arrStop;
        if ($arrStop[0] == 0) { // special case, keep only roots within the current path
            $arrPath    = $GLOBALS['objPage']->trail;
            $arrPath[]  = $this->varActiveID;
            $arrRootIDs = array_intersect($arrRootIDs, $arrPath);
        }

        return $arrRootIDs;
    }

    protected function getRootIds(): array
    {
        return array_map(
            'intval',
            array_values(
                array_unique(
                    array_merge(
                        StringUtil::deserialize($this->hofff_navigation_roots_order, true),
                        StringUtil::deserialize($this->hofff_navigation_roots, true)
                    )
                )
            )
        );
    }
}
