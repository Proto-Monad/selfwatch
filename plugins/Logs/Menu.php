<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs;

use Piwik\Common;
use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;

/**
 * Adds the selfwatch pages (Logs, Alerts, Projects) to Matomo's standard admin menu, so the
 * whole app shares one consistent navigation/layout instead of a separate custom sidebar.
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu): void
    {
        $idSite = $this->defaultIdSite();
        if (!$idSite) {
            return;
        }

        $params = ['idSite' => $idSite, 'period' => 'day', 'date' => 'today'];

        // "Logs" group, shown first.
        $menu->addItem('Logs', 'Logs', $this->urlForAction('index', $params), 1);

        if (Piwik::isUserHasAdminAccess($idSite)) {
            $menu->addItem('Logs', 'Alerts', $this->urlForAction('alerts', $params), 2);
            $menu->addItem('Logs', 'Projects & tokens', $this->urlForAction('projects', $params), 3);
        }
    }

    /**
     * The project the menu links should target: the one in the request if accessible,
     * otherwise the first project the user can view.
     */
    private function defaultIdSite(): int
    {
        $requested  = Common::getRequestVar('idSite', 0, 'int');
        $accessible = SitesManagerApi::getInstance()->getSitesIdWithAtLeastViewAccess();

        if ($requested && in_array($requested, $accessible)) {
            return $requested;
        }

        return (int) (reset($accessible) ?: 0);
    }
}
