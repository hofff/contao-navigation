<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\EventListener\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Hofff\Contao\Navigation\QueryBuilder\PageQueryBuilder;

use function array_map;
use function explode;
use function implode;
use function sprintf;

/** @psalm-suppress PropertyNotSetInConstructor */
final class NavigationDcaListener
{
    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @return array<string,string>
     *
     * @Callback(table="tl_module", target="fields.hofff_navigation_addFields.options")
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getPageFields(): array
    {
        $controller = $this->framework->getAdapter(Controller::class);
        $controller->loadLanguageFile('tl_page');
        $controller->loadDataContainer('tl_page');

        $fields = [];

        foreach ($GLOBALS['TL_DCA']['tl_page']['fields'] as $fieldName => &$config) {
            if (isset(PageQueryBuilder::DEFAULT_FIELDS[$fieldName])) {
                continue;
            }

            $fields[$fieldName] = sprintf('%s <span class="tl_gray">[%s]</span>', $config['label'][0], $fieldName);
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

        foreach (array_map('intval', explode(',', $value)) as $intLevel) {
            if ($intLevel <= $minimum) {
                continue;
            }

            $stop[] = $minimum = $intLevel;
        }

        return implode(',', $stop);
    }
}
