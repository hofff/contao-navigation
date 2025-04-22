<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Hofff\Contao\Navigation\HofffContaoNavigationBundle;
use Override;

final class Plugin implements BundlePluginInterface
{
    /** {@inheritDoc} */
    #[Override]
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(HofffContaoNavigationBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['backboneit_navigation']),
        ];
    }
}
