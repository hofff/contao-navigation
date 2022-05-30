<?php

namespace Hofff\Contao\Navigation\EventListener\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
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

        foreach ($GLOBALS['TL_DCA']['tl_page']['fields'] as $fieldName => &$config) {
            if (! isset(PageQueryBuilder::DEFAULT_FIELDS[$fieldName])) {
                $fields[$fieldName] = sprintf('%s <span class="tl_gray">[%s]</span>', $config['label'][0], $fieldName);
            }
        }

        return $fields;
    }

    /**
     * @Callback(table="tl_module", target="fields.hofff_navigation_stop.save")
     */
    public function saveStop(string $value): string
    {
        $minimum = -1;
        $stop    = [];

        foreach (array_map('intval', explode(',', (string) $value)) as $intLevel) {
            if ($intLevel > $minimum) {
                $stop[] = $minimum = $intLevel;
            }
        }

        return implode(',', $stop);
    }
}
