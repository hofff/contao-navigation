<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\EventListener\Dca;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Hofff\Contao\Navigation\QueryBuilder\PageQueryBuilder;

use function array_map;
use function explode;
use function implode;
use function sprintf;

/** @psalm-suppress PropertyNotSetInConstructor */
final class NavigationDcaListener
{
    public function __construct(private readonly ContaoFramework $framework)
    {
    }

    /**
     * @return array<string,string>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    #[AsCallback('tl_module', 'fields.hofff_navigation_addFields.options')]
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

            $fields[$fieldName] = sprintf(
                '%s <span class="tl_gray">[%s]</span>',
                $config['label'][0] ?? '',
                $fieldName,
            );
        }

        return $fields;
    }

    #[AsCallback('tl_module', 'fields.hofff_navigation_stop.save')]
    public function saveStop(string $value): string
    {
        $minimum = -1;
        $stop    = [];

        foreach (array_map('intval', explode(',', $value)) as $level) {
            if ($level <= $minimum) {
                continue;
            }

            $stop[] = $minimum = $level;
        }

        return implode(',', $stop);
    }
}
