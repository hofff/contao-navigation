<?php

declare(strict_types=1);

namespace Hofff\Contao\Navigation\Security;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\ModuleModel;
use Contao\StringUtil;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as Security;

final class PagePermissionGuard
{
    public function __construct(private readonly Security $security)
    {
    }

    public function isPermissionCheckRequired(ModuleModel $model): bool
    {
        return ! $this->security->isGranted('ROLE_USER') && ! $model->hofff_navigation_showProtected;
    }

    /**
     * Utility method.
     *
     * THIS IS NOT THE OPPOSITE OF ::isPermissionDenied()!
     *
     * Checks if the current user has permission to view the page of the given
     * page dataset, in regard to the current navigation settings and the
     * permission requirements of the page.
     *
     * Context property: hofff_navigation_showProtected
     *
     * @param array<string,mixed> $page The page dataset of the current page, with at least
     *                                  groups and protected attributes set.
     *
     * @return bool If the permission is granted true, otherwise false.
     */
    public function isPermissionGranted(ModuleModel $model, array $page): bool
    {
        // be users have access everywhere
        if ($this->security->isGranted('ROLE_USER')) {
            return true;
        }

        // protection is ignored
        if ($model->hofff_navigation_showProtected) {
            return true;
        }

        return ! $this->isPermissionDenied($page);
    }

    /**
     * Utility method.
     *
     * THIS IS NOT THE OPPOSITE OF ::isPermissionGranted()!
     *
     * Checks if the current user has no permission to view the page of the
     * given page dataset, in regard to the permission requirements of the
     * page.
     *
     * @param array<string,mixed> $page The page dataset of the current page, with at least
     *                                  groups and protected attributes set.
     *
     * @return bool If the permission is denied true, otherwise false.
     */
    public function isPermissionDenied(array $page): bool
    {
        // this page is not protected
        if (! $page['protected']) {
            return false;
        }

        return ! $this->security->isGranted(
            ContaoCorePermissions::MEMBER_IN_GROUPS,
            StringUtil::deserialize($page['groups'], true),
        );
    }
}
