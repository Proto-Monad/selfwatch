<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Request;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        // selfwatch: segments are an analytics concept; the Logs UI has its own filtering,
        // so the "Manage Segments" page is not shown in the sidebar.
    }

    private function getDefaultIdSiteForUser(): int
    {
        $sites = SitesManagerAPI::getInstance()->getSitesWithAtLeastViewAccess(1);
        $site = reset($sites);
        if (!empty($site['idsite'])) {
            return (int) $site['idsite'];
        }

        return -1;
    }
}
