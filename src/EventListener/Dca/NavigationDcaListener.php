<?php

namespace Hofff\Contao\Navigation\EventListener\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Hofff\Contao\Navigation\FrontendModule\ModuleNavigationMenu;
use Hofff\Contao\Navigation\QueryBuilder\PageQueryBuilder;

class NavigationDcaListener
{
    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @Callback(table="tl_module", target="fields.hofff_navigation_addFields.options")
     *
     * @return array<string,string>
     */
    public function getPageFields(): array
    {
        $controller = $this->framework->getAdapter(Controller::class);
        $controller->loadLanguageFile('tl_page');
        $controller->loadDataContainer('tl_page');

        $fields = [];

        foreach ($GLOBALS['TL_DCA']['tl_page']['fields'] as $strField => $arrConfig) {
            if (! isset(PageQueryBuilder::DEFAULT_FIELDS[$strField])) {
                $fields[$strField] = &$arrConfig['label'][0];
            }
        }

        return $fields;
    }

    /**
     * @param mixed $value
     *
     * @Callback(table="tl_module", target="fields.hofff_navigation_stop.save")
     */
    public function saveStop($value): string
    {
        $intMin = -1;
        $stop   = [];

        foreach (array_map('intval', explode(',', (string) $value)) as $intLevel) {
            if ($intLevel > $intMin) {
                $stop[] = $intMin = $intLevel;
            }
        }

        return implode(',', $stop);
    }
}
