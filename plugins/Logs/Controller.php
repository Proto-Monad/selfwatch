<?php

/**
 * selfwatch - self-hosted log tracking platform
 *
 * @link    https://github.com/Proto-Monad/selfwatch
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Logs;

use Piwik\Common;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
use Piwik\Session\SessionNamespace;
use Piwik\Url;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    private const PAGE_SIZE = 100;
    private const REGENERATE_NONCE = 'Logs.regenerateToken';

    /**
     * The log viewer - selfwatch's main page.
     */
    public function index()
    {
        Piwik::checkUserHasViewAccess($this->idSite);

        $model   = new Model();
        $filters = $this->getFiltersFromRequest();

        // Keyset pagination (cursor-based) - scales to millions of rows, unlike OFFSET.
        $before = Common::getRequestVar('before', 0, 'int');
        $after  = Common::getRequestVar('after', 0, 'int');

        if ($after > 0) {
            // "Newer" page: entries just after the cursor (oldest-first), reversed for display.
            $logs = array_reverse($model->getLogs($this->idSite, $filters + ['after_id' => $after], self::PAGE_SIZE));
        } else {
            $logs = $model->getLogs($this->idSite, $before > 0 ? $filters + ['before_id' => $before] : $filters, self::PAGE_SIZE);
        }
        $total = $model->countLogs($this->idSite, $filters);

        $view = new View('@Logs/index');
        $this->setGeneralVariablesView($view);

        $newestId = !empty($logs) ? (int) $logs[0]['idlogentry'] : 0;
        $oldestId = !empty($logs) ? (int) $logs[count($logs) - 1]['idlogentry'] : 0;

        $view->logs        = $logs;
        $view->total       = $total;
        $view->pageSize    = self::PAGE_SIZE;
        $view->newestId    = $newestId;
        $view->oldestId    = $oldestId;
        $view->hasCursor   = ($before > 0 || $after > 0);
        $view->hasOlder    = count($logs) === self::PAGE_SIZE && $oldestId > 1;
        $view->levelCounts = $model->getLevelCounts($this->idSite, $filters);
        $view->sources     = $model->getSources($this->idSite);
        $view->environments = $model->getEnvironments($this->idSite);
        $view->platforms    = $model->getPlatforms($this->idSite);
        $view->levels      = Model::LEVELS;
        $view->filters     = $filters;
        $view->timeRange   = $model->getTimeRange($this->idSite);
        $view->lastId      = $newestId;

        // The projects/alerts screens (and the ingest token they expose) are admin-only.
        // View-only users can read logs but can't obtain the token to send (forge) logs.
        $this->setNavVars($view);

        return $view->render();
    }

    /**
     * Single-entry detail page: full message plus the structured event - exception, stack
     * trace, tags, request/user, breadcrumbs and the raw context payload.
     */
    public function entry()
    {
        Piwik::checkUserHasViewAccess($this->idSite);

        $id    = Common::getRequestVar('id', 0, 'int');
        $entry = (new Model())->getEntry($this->idSite, $id);
        if (!$entry) {
            throw new \Exception(Piwik::translate('General_ExceptionInvalidRequest') ?: 'Log entry not found.');
        }

        $context = [];
        if (!empty($entry['context'])) {
            $decoded = json_decode((string) $entry['context'], true);
            if (is_array($decoded)) {
                $context = $decoded;
            }
        }

        // Normalise the Sentry-ish shapes ({values:[...]} or a bare list) into predictable
        // arrays with known keys, so the template needs no defensive attribute probing.
        $rawExceptions  = $context['exception']['values'] ?? ($context['exception'] ?? []);
        $rawBreadcrumbs = $context['breadcrumbs']['values'] ?? ($context['breadcrumbs'] ?? []);

        $exceptions = [];
        foreach (is_array($rawExceptions) ? $rawExceptions : [] as $exc) {
            if (!is_array($exc)) {
                continue;
            }
            $rawFrames = $exc['stacktrace']['frames'] ?? ($exc['stacktrace'] ?? []);
            $frames = [];
            foreach (is_array($rawFrames) ? $rawFrames : [] as $f) {
                if (!is_array($f)) {
                    continue;
                }
                $frames[] = [
                    'file'     => (string) ($f['filename'] ?? $f['file'] ?? ''),
                    'line'     => $f['lineno'] ?? $f['line'] ?? '',
                    'col'      => $f['colno'] ?? '',
                    'function' => (string) ($f['function'] ?? $f['func'] ?? ''),
                    'in_app'   => !empty($f['in_app']),
                ];
            }
            $exceptions[] = [
                'type'   => (string) ($exc['type'] ?? 'Error'),
                'value'  => (string) ($exc['value'] ?? ''),
                'frames' => $frames,
            ];
        }

        $breadcrumbs = [];
        foreach (is_array($rawBreadcrumbs) ? $rawBreadcrumbs : [] as $c) {
            if (!is_array($c)) {
                continue;
            }
            $msg = $c['message'] ?? (isset($c['data']) ? (is_scalar($c['data']) ? $c['data'] : json_encode($c['data'])) : '');
            $breadcrumbs[] = [
                'timestamp' => (string) ($c['timestamp'] ?? ''),
                'category'  => (string) ($c['category'] ?? $c['type'] ?? 'default'),
                'message'   => (string) $msg,
            ];
        }

        $view = new View('@Logs/entry');
        $this->setGeneralVariablesView($view);
        $this->setNavVars($view);

        $view->entry       = $entry;
        $view->exceptions  = $exceptions;
        $view->breadcrumbs = $breadcrumbs;
        $pretty = static function ($v): string {
            return empty($v) ? '' : (string) json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        };

        $view->tags        = is_array($context['tags'] ?? null) ? $context['tags'] : [];
        $view->logUser     = is_array($context['user'] ?? null) ? $context['user'] : [];
        $view->request     = is_array($context['request'] ?? null) ? $context['request'] : [];
        $view->extraJson    = $pretty($context['extra'] ?? null);
        $view->contextsJson = $pretty($context['contexts'] ?? null);
        $view->sdkJson      = $pretty($context['sdk'] ?? null);
        $view->rawContext  = $pretty($context);
        $view->levels      = Model::LEVELS;

        return $view->render();
    }

    /**
     * Project & ingest-token management.
     */
    public function projects()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $model    = new Model();
        $revealed = $this->takeRevealedTokens();
        $sites    = SitesManagerApi::getInstance()->getSitesWithAdminAccess();
        $projects = [];
        foreach ($sites as $site) {
            $idSite     = (int) $site['idsite'];
            $projects[] = [
                'idsite'    => $idSite,
                'name'      => $site['name'],
                'has_token' => $model->tokenExists($idSite),
                'reveal'    => $revealed[$idSite] ?? null, // plaintext, shown once after (re)generation
                'count'     => $model->countLogs($idSite),
            ];
        }

        $view = new View('@Logs/projects');
        $this->setGeneralVariablesView($view);
        $this->setNavVars($view);
        $view->projects     = $projects;
        $view->ingestUrl    = Url::getCurrentUrlWithoutFileName() . 'ingest.php';
        $view->regenerateNonce = Nonce::getNonce(self::REGENERATE_NONCE);

        return $view->render();
    }

    /**
     * (Re)generate a project's ingest token. The new plaintext is stashed in the session
     * so the projects page can reveal it exactly once; the previous token stops working.
     */
    public function regenerateToken()
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        Piwik::checkUserHasAdminAccess($idSite);

        $nonce = Common::getRequestVar('nonce', '', 'string');
        if (!Nonce::verifyNonce(self::REGENERATE_NONCE, $nonce)) {
            throw new \Exception(Piwik::translate('General_ExceptionNonceMismatch'));
        }
        Nonce::discardNonce(self::REGENERATE_NONCE);

        $plaintext = (new Model())->regenerateToken($idSite);
        $this->stashRevealedToken($idSite, $plaintext);

        Url::redirectToUrl(Url::getCurrentUrlWithoutQueryString() . '?' . http_build_query([
            'module' => 'Logs',
            'action' => 'projects',
            'idSite' => $idSite,
            'period' => Common::getRequestVar('period', 'day', 'string'),
            'date'   => Common::getRequestVar('date', 'today', 'string'),
        ]));
    }

    /**
     * JSON endpoint used for live tail: returns entries newer than `after_id`, oldest-first.
     */
    public function tail()
    {
        Piwik::checkUserHasViewAccess($this->idSite);

        $model   = new Model();
        $filters = $this->getFiltersFromRequest();
        $filters['after_id'] = Common::getRequestVar('after_id', 0, 'int');

        $logs = $model->getLogs($this->idSite, $filters, self::PAGE_SIZE);

        Common::sendHeader('Content-Type: application/json; charset=utf-8');
        return json_encode([
            'logs'   => array_map([$this, 'formatLogForJson'], $logs),
            'lastId' => !empty($logs) ? (int) $logs[count($logs) - 1]['idlogentry'] : (int) $filters['after_id'],
        ]);
    }

    // -- Alerts -------------------------------------------------------------------------

    private const ALERTS_NONCE = 'Logs.alerts';

    /**
     * Alert rules management + recent fired-alert activity.
     */
    public function alerts()
    {
        Piwik::checkUserHasAdminAccess($this->idSite);

        $alertModel = new AlertModel();

        $view = new View('@Logs/alerts');
        $this->setGeneralVariablesView($view);
        $this->setNavVars($view);
        $editId         = Common::getRequestVar('edit', 0, 'int');
        $view->rules    = $alertModel->getRules($this->idSite);
        $view->events   = $alertModel->getRecentEvents($this->idSite, 50);
        $view->levels   = Model::LEVELS;
        $view->channels = AlertModel::CHANNELS;
        $view->editRule = $editId ? $alertModel->getRule($editId, $this->idSite) : null;
        $view->nonce    = Nonce::getNonce(self::ALERTS_NONCE);

        return $view->render();
    }

    public function saveAlert()
    {
        $this->checkAlertRequest();

        $alertModel = new AlertModel();
        $idRule     = Common::getRequestVar('idalertrule', 0, 'int');
        $data       = [
            'name'             => Common::getRequestVar('name', 'Alert', 'string'),
            'enabled'          => Common::getRequestVar('enabled', 0, 'int'),
            'min_level'        => Common::getRequestVar('min_level', 'error', 'string'),
            'source'           => Common::getRequestVar('source', '', 'string'),
            'message_contains' => Common::getRequestVar('message_contains', '', 'string'),
            'channel'          => Common::getRequestVar('channel', 'webhook', 'string'),
            'channel_target'   => Common::getRequestVar('channel_target', '', 'string'),
            'cooldown_seconds' => Common::getRequestVar('cooldown_seconds', 0, 'int'),
        ];

        // updateRule is scoped to $this->idSite, so passing another project's rule id is a no-op.
        if ($idRule > 0 && $alertModel->getRule($idRule, $this->idSite)) {
            $alertModel->updateRule($idRule, $this->idSite, $data);
        } else {
            $alertModel->createRule($this->idSite, $data);
        }

        $this->redirectToAlerts();
    }

    public function deleteAlert()
    {
        $this->checkAlertRequest();
        (new AlertModel())->deleteRule(Common::getRequestVar('idalertrule', 0, 'int'), $this->idSite);
        $this->redirectToAlerts();
    }

    public function toggleAlert()
    {
        $this->checkAlertRequest();
        $alertModel = new AlertModel();
        $idRule     = Common::getRequestVar('idalertrule', 0, 'int');
        $rule       = $alertModel->getRule($idRule, $this->idSite);
        if ($rule) {
            $alertModel->setEnabled($idRule, $this->idSite, empty($rule['enabled']));
        }
        $this->redirectToAlerts();
    }

    private function checkAlertRequest(): void
    {
        Piwik::checkUserHasAdminAccess($this->idSite);
        if (!Nonce::verifyNonce(self::ALERTS_NONCE, Common::getRequestVar('nonce', '', 'string'))) {
            throw new \Exception(Piwik::translate('General_ExceptionNonceMismatch'));
        }
        Nonce::discardNonce(self::ALERTS_NONCE);
    }

    private function redirectToAlerts(): void
    {
        Url::redirectToUrl(Url::getCurrentUrlWithoutQueryString() . '?' . http_build_query([
            'module' => 'Logs',
            'action' => 'alerts',
            'idSite' => $this->idSite,
            'period' => Common::getRequestVar('period', 'day', 'string'),
            'date'   => Common::getRequestVar('date', 'today', 'string'),
        ]));
    }

    /**
     * Render selfwatch pages inside Matomo's standard admin shell (the same layout/menu the
     * Users/Settings pages use) so there is a single, consistent UI. The pages are added to
     * that menu in {@see Menu}.
     */
    private function setNavVars(View $view): void
    {
        \Piwik\Plugin\ControllerAdmin::setBasicVariablesAdminView($view);
        $view->canManage = Piwik::isUserHasAdminAccess($this->idSite);
    }

    // -- One-time token reveal (kept in the session, never in the URL) -----------------

    private const REVEAL_NS = 'Logs_token_reveal';

    private function stashRevealedToken(int $idSite, string $plaintext): void
    {
        $ns = new SessionNamespace(self::REVEAL_NS);
        $reveal = $ns->reveal ?? [];
        $reveal[$idSite] = $plaintext;
        $ns->reveal = $reveal;
    }

    /**
     * @return array<int,string> idSite => plaintext token, cleared after reading.
     */
    private function takeRevealedTokens(): array
    {
        $ns = new SessionNamespace(self::REVEAL_NS);
        $reveal = $ns->reveal ?? [];
        $ns->reveal = [];
        return is_array($reveal) ? $reveal : [];
    }

    private function formatLogForJson(array $row): array
    {
        return [
            'id'          => (int) $row['idlogentry'],
            'level'       => $row['level'],
            'source'      => $row['source'],
            'environment' => $row['environment'] ?? '',
            'message'     => $row['message'],
            'trace_id'    => $row['trace_id'],
            'logged_at'   => $row['logged_at'],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function getFiltersFromRequest(): array
    {
        // The <input type=datetime-local> sends "YYYY-MM-DDTHH:MM"; logged_at is stored as
        // "YYYY-MM-DD HH:MM:SS", so normalise the 'T' to a space and pad the seconds (so the
        // upper bound is inclusive of the whole selected minute).
        $from = str_replace('T', ' ', Common::getRequestVar('from', '', 'string'));
        $to   = str_replace('T', ' ', Common::getRequestVar('to', '', 'string'));
        if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $from)) {
            $from .= ':00';
        }
        if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $to)) {
            $to .= ':59';
        }

        return [
            'level'       => Common::getRequestVar('level', '', 'string'),
            'source'      => Common::getRequestVar('source', '', 'string'),
            'environment' => Common::getRequestVar('environment', '', 'string'),
            'platform'    => Common::getRequestVar('platform', '', 'string'),
            'q'           => Common::getRequestVar('q', '', 'string'),
            'trace_id'    => Common::getRequestVar('trace_id', '', 'string'),
            'from'        => $from,
            'to'          => $to,
        ];
    }
}
